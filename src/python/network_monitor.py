import base64
import csv
from concurrent.futures import ThreadPoolExecutor, as_completed
from functools import partial
from http.server import SimpleHTTPRequestHandler, ThreadingHTTPServer
import ipaddress
import json
import os
import platform
import re
import secrets
import socket
import subprocess
import sys
import threading
import time
from datetime import datetime
from pathlib import Path

import speedtest

try:
    import gspread
except ImportError:
    gspread = None


PROJECT_ROOT = Path(__file__).resolve().parents[2]


def env_path(name, default):
    path = Path(os.getenv(name, default))
    return path if path.is_absolute() else PROJECT_ROOT / path


CHECK_INTERVAL_SECONDS = int(os.getenv("CHECK_INTERVAL_SECONDS", "60"))
DEVICE_PING_WORKERS = int(os.getenv("DEVICE_PING_WORKERS", "16"))
WEB_SERVER_HOST = os.getenv("NETWORK_DASHBOARD_HOST", "0.0.0.0")
WEB_SERVER_PORT = int(os.getenv("NETWORK_DASHBOARD_PORT", "8088"))
CSV_PATH = env_path("SPEED_HISTORY_CSV", "speed_history.csv")
DEVICE_CSV_PATH = env_path("DEVICE_CSV", "src/python/devices.csv")
DEVICE_STATUS_CSV_PATH = env_path("DEVICE_STATUS_CSV", "device_status.csv")
STATUS_JSON_PATH = env_path("NETWORK_STATUS_JSON", "network_status.json")
LAST_SEEN_JSON_PATH = env_path("DEVICE_LAST_SEEN_JSON", "device_last_seen.json")
DEVICE_HEADERS = ["Device Name", "IP Address", "MAC Address", "Device Type", "Notes"]
WEB_AUTH_USERNAME = os.getenv("NETWORK_DASHBOARD_USER", "admin")
WEB_AUTH_PASSWORD = os.getenv("NETWORK_DASHBOARD_PASSWORD", "123")
TCP_CONNECT_TIMEOUT_SECONDS = float(os.getenv("TCP_CONNECT_TIMEOUT_SECONDS", "0.75"))
DEFAULT_TCP_PORTS = [80, 443, 22, 445, 3389, 9100, 554]
TCP_PORTS_BY_TYPE = {
    "access point": [80, 443, 22, 53],
    "camera": [80, 443, 554],
    "laptop": [445, 3389, 80, 443],
    "pc": [445, 3389, 80, 443],
    "phone": [80, 443, 62078],
    "printer": [80, 443, 9100, 515, 631],
    "router": [80, 443, 22, 53],
    "server": [80, 443, 22, 445, 3389],
}

GOOGLE_CREDENTIALS_FILE = os.getenv("GOOGLE_SHEETS_CREDENTIALS")
SPREADSHEET_ID = os.getenv("GOOGLE_SHEETS_SPREADSHEET_ID")
DEVICE_WORKSHEET_NAME = os.getenv("GOOGLE_SHEETS_DEVICE_WORKSHEET", "")
SPEED_WORKSHEET_NAME = os.getenv("GOOGLE_SHEETS_SPEED_WORKSHEET", "Speed History")
REFRESH_LOCK = threading.Lock()


def now_text():
    return datetime.now().strftime("%b %d, %Y • %I:%M %p")


def get_google_client():
    if not GOOGLE_CREDENTIALS_FILE or not SPREADSHEET_ID:
        return None

    if gspread is None:
        print("Google Sheets skipped: install gspread first.")
        return None

    return gspread.service_account(filename=GOOGLE_CREDENTIALS_FILE)


def get_device_worksheet(client):
    spreadsheet = client.open_by_key(SPREADSHEET_ID)
    if DEVICE_WORKSHEET_NAME:
        return spreadsheet.worksheet(DEVICE_WORKSHEET_NAME)
    return spreadsheet.sheet1


def ping_ip(ip_address):
    system = platform.system().lower()
    command = ["ping", "-n", "1", "-w", "1000", ip_address]

    if system != "windows":
        command = ["ping", "-c", "1", "-W", "1", ip_address]

    try:
        result = subprocess.run(
            command,
            capture_output=True,
            text=True,
            timeout=5,
            check=False,
        )
    except (OSError, subprocess.TimeoutExpired):
        return "Offline", None

    output = result.stdout + result.stderr
    match = re.search(r"time\s*([=<])\s*(\d+(?:\.\d+)?)\s*ms", output, re.IGNORECASE)
    ping_ms = None
    if match:
        ping_ms = f"<{match.group(2)}" if match.group(1) == "<" else float(match.group(2))

    if result.returncode == 0:
        return "Online", ping_ms

    return "Offline", None


def get_tcp_ports_for_device(device_type):
    configured_ports = os.getenv("NETWORK_DEVICE_TCP_PORTS", "").strip()
    if configured_ports:
        return [
            int(port.strip())
            for port in configured_ports.split(",")
            if port.strip().isdigit()
        ]

    return TCP_PORTS_BY_TYPE.get(device_type.lower(), DEFAULT_TCP_PORTS)


def check_tcp_ports(ip_address, device_type):
    for port in get_tcp_ports_for_device(device_type):
        try:
            with socket.create_connection(
                (ip_address, port),
                timeout=TCP_CONNECT_TIMEOUT_SECONDS,
            ):
                return True, port
        except OSError:
            continue

    return False, None


def check_device_reachability(row):
    status, ping_ms = ping_ip(row["IP Address"])

    if status == "Online":
        return status, ping_ms, "Ping"

    tcp_online, port = check_tcp_ports(row["IP Address"], row["Device Type"])
    if tcp_online:
        return "Online", None, f"TCP:{port}"

    return "Offline", None, "No response"


def read_last_seen_rows():
    if not LAST_SEEN_JSON_PATH.exists():
        return {}

    try:
        with LAST_SEEN_JSON_PATH.open() as file:
            data = json.load(file)
    except (OSError, json.JSONDecodeError):
        return {}

    return data if isinstance(data, dict) else {}


def write_last_seen_rows(rows):
    with LAST_SEEN_JSON_PATH.open("w") as file:
        json.dump(rows, file, indent=2)


def clean_device_row(row):
    return {
        "Device Name": row.get("Device Name", "").strip(),
        "IP Address": row.get("IP Address", "").strip(),
        "MAC Address": row.get("MAC Address", "").strip(),
        "Device Type": row.get("Device Type", "").strip(),
        "Notes": row.get("Notes", "").strip(),
    }


def read_device_rows():
    if not DEVICE_CSV_PATH.exists():
        return []

    with DEVICE_CSV_PATH.open(newline="") as file:
        return [clean_device_row(row) for row in csv.DictReader(file)]


def write_device_rows(rows):
    DEVICE_CSV_PATH.parent.mkdir(parents=True, exist_ok=True)
    with DEVICE_CSV_PATH.open("w", newline="") as file:
        writer = csv.DictWriter(file, fieldnames=DEVICE_HEADERS)
        writer.writeheader()
        writer.writerows(rows)


def add_or_update_device(row):
    cleaned = clean_device_row(row)
    device_name = cleaned["Device Name"]
    ip_address = cleaned["IP Address"]
    original_ip_address = row.get("Original IP Address", "").strip()

    if not device_name:
        raise ValueError("Device name is required.")

    try:
        ipaddress.ip_address(ip_address)
    except ValueError as exc:
        raise ValueError("Valid IP address is required.") from exc

    if original_ip_address:
        try:
            ipaddress.ip_address(original_ip_address)
        except ValueError as exc:
            raise ValueError("Valid original IP address is required.") from exc

    rows = read_device_rows()
    original_index = None
    duplicate_index = None

    for index, existing in enumerate(rows):
        if existing["IP Address"] == original_ip_address:
            original_index = index
        if existing["IP Address"] == ip_address:
            duplicate_index = index

    if duplicate_index is not None and duplicate_index != original_index:
        raise ValueError("IP address already exists.")

    replaced = original_index is not None or duplicate_index is not None

    if original_ip_address and original_index is None:
        raise ValueError("Device not found.")

    if replaced:
        rows[original_index if original_index is not None else duplicate_index] = cleaned
    else:
        rows.append(cleaned)

    write_device_rows(rows)
    return cleaned, replaced


def delete_device(ip_address):
    try:
        ipaddress.ip_address(ip_address)
    except ValueError as exc:
        raise ValueError("Valid IP address is required.") from exc

    rows = read_device_rows()
    kept_rows = [row for row in rows if row["IP Address"] != ip_address]

    if len(kept_rows) == len(rows):
        raise ValueError("Device not found.")

    write_device_rows(kept_rows)
    return True


def check_device_rows(rows):
    checked_at = now_text()
    valid_rows = [clean_device_row(row) for row in rows if row.get("IP Address", "").strip()]
    last_seen_rows = read_last_seen_rows()

    def check_one(row):
        status, ping_ms, detection_method = check_device_reachability(row)
        ping_value = round(ping_ms, 2) if isinstance(ping_ms, float) else ping_ms
        ping_text = f"{ping_value} ms" if ping_value else "no ping"
        last_seen = checked_at if status == "Online" else last_seen_rows.get(row["IP Address"], "")
        print(
            f"{row['Device Name']} ({row['IP Address']}): "
            f"{status} {ping_text} via {detection_method}"
        )

        return {
            "name": row["Device Name"],
            "ip": row["IP Address"],
            "type": row["Device Type"],
            "status": status,
            "ping": ping_value,
            "detection_method": detection_method,
            "last_seen": last_seen,
            "Device Name": row["Device Name"],
            "IP Address": row["IP Address"],
            "MAC Address": row["MAC Address"],
            "Device Type": row["Device Type"],
            "Status": status,
            "Ping (ms)": ping_value if ping_value is not None else "",
            "Detection Method": detection_method,
            "Last Check": checked_at,
            "Last Seen": last_seen,
            "Notes": row["Notes"],
        }

    if not valid_rows:
        return []

    checked_devices = []
    workers = min(DEVICE_PING_WORKERS, len(valid_rows))

    with ThreadPoolExecutor(max_workers=workers) as executor:
        futures = {executor.submit(check_one, row): index for index, row in enumerate(valid_rows)}
        results = {}

        for future in as_completed(futures):
            results[futures[future]] = future.result()

    for index in range(len(valid_rows)):
        checked_devices.append(results[index])

    for device in checked_devices:
        if device["Status"] == "Online":
            last_seen_rows[device["IP Address"]] = device["Last Seen"]

    write_last_seen_rows(last_seen_rows)
    return checked_devices


def update_local_device_statuses():
    if not DEVICE_CSV_PATH.exists():
        print(f"Local device check skipped: {DEVICE_CSV_PATH} not found.")
        return []

    checked_devices = check_device_rows(read_device_rows())

    if not checked_devices:
        print("Local device list has no valid IP addresses.")
        return []

    headers = [
        "Device Name",
        "IP Address",
        "MAC Address",
        "Device Type",
        "Status",
        "Ping (ms)",
        "Detection Method",
        "Last Check",
        "Last Seen",
        "Notes",
    ]

    with DEVICE_STATUS_CSV_PATH.open("w", newline="") as file:
        writer = csv.DictWriter(file, fieldnames=headers, extrasaction="ignore")
        writer.writeheader()
        writer.writerows(checked_devices)

    print(f"Device statuses saved to {DEVICE_STATUS_CSV_PATH}.")
    return checked_devices


def update_google_device_statuses(client):
    if client is None:
        return

    worksheet = get_device_worksheet(client)
    sheet_rows = worksheet.get_all_records()

    if not sheet_rows:
        print("Device sheet has no device rows.")
        return

    checked_devices = check_device_rows(sheet_rows)
    updates = [
        [device["Status"], device["Ping (ms)"], device["Last Check"]]
        for device in checked_devices
    ]

    if not updates:
        print("Device sheet has no valid IP addresses.")
        return

    end_row = len(updates) + 1
    worksheet.update(values=updates, range_name=f"E2:G{end_row}")
    print("Device statuses updated in Google Sheets.")


def run_speed_test():
    print("\nTesting internet speed...")

    st = speedtest.Speedtest()
    st.get_best_server()

    download = st.download() / 1_000_000
    upload = st.upload() / 1_000_000
    ping = st.results.ping

    result = {
        "time": now_text(),
        "download": round(download, 2),
        "upload": round(upload, 2),
        "ping": round(ping, 2),
    }

    print(f"Time: {result['time']}")
    print(f"Download: {result['download']:.2f} Mbps")
    print(f"Upload: {result['upload']:.2f} Mbps")
    print(f"Ping: {result['ping']:.2f} ms")

    return result


def save_speed_to_csv(result):
    with CSV_PATH.open("a", newline="") as file:
        writer = csv.writer(file)

        if file.tell() == 0:
            writer.writerow(["Time", "Download Mbps", "Upload Mbps", "Ping ms"])

        writer.writerow([
            result["time"],
            result["download"],
            result["upload"],
            result["ping"],
        ])

    print(f"Speed test saved to {CSV_PATH}.")


def read_speed_history(limit=20):
    if not CSV_PATH.exists():
        return []

    with CSV_PATH.open(newline="") as file:
        rows = list(csv.DictReader(file))

    history = []
    for row in rows[-limit:]:
        history.append({
            "time": row.get("Time", ""),
            "download": float(row.get("Download Mbps", 0) or 0),
            "upload": float(row.get("Upload Mbps", 0) or 0),
            "ping": float(row.get("Ping ms", 0) or 0),
        })

    return history


def get_last_speed_result(error_message=""):
    history = read_speed_history(limit=1)

    if history:
        last_result = history[-1]
        return {
            "time": now_text(),
            "download": last_result["download"],
            "upload": last_result["upload"],
            "ping": last_result["ping"],
            "error": error_message,
        }

    return {
        "time": now_text(),
        "download": 0,
        "upload": 0,
        "ping": 0,
        "error": error_message,
    }


def safe_run_speed_test():
    try:
        return run_speed_test(), True
    except Exception as exc:
        print(f"Speed test skipped: {exc}")
        print("Dashboard will use the last saved speed result.")
        return get_last_speed_result(str(exc)), False


def write_dashboard_json(speed_result, devices):
    online_count = sum(1 for device in devices if device.get("Status") == "Online")
    offline_count = sum(1 for device in devices if device.get("Status") == "Offline")

    payload = {
        "updated_at": now_text(),
        "speed": speed_result,
        "devices": devices,
        "summary": {
            "online": online_count,
            "offline": offline_count,
            "total": len(devices),
        },
        "history": read_speed_history(),
    }

    with STATUS_JSON_PATH.open("w") as file:
        json.dump(payload, file, indent=2)

    print(f"Dashboard data saved to {STATUS_JSON_PATH}.")


def get_lan_ip():
    try:
        with socket.socket(socket.AF_INET, socket.SOCK_DGRAM) as sock:
            sock.connect(("8.8.8.8", 80))
            return sock.getsockname()[0]
    except OSError:
        return socket.gethostbyname(socket.gethostname())


def append_speed_to_google_sheet(client, result):
    if client is None:
        return

    spreadsheet = client.open_by_key(SPREADSHEET_ID)

    try:
        worksheet = spreadsheet.worksheet(SPEED_WORKSHEET_NAME)
    except gspread.WorksheetNotFound:
        worksheet = spreadsheet.add_worksheet(title=SPEED_WORKSHEET_NAME, rows=1000, cols=4)
        worksheet.append_row(["Time", "Download Mbps", "Upload Mbps", "Ping ms"])

    worksheet.append_row(
        [result["time"], result["download"], result["upload"], result["ping"]],
        value_input_option="USER_ENTERED",
    )
    print("Speed test appended to Google Sheets.")


def refresh_dashboard_data(include_speed_test=True):
    if not REFRESH_LOCK.acquire(blocking=False):
        raise RuntimeError("Refresh already running.")

    try:
        client = get_google_client()

        devices = update_local_device_statuses()
        update_google_device_statuses(client)

        speed_test_ok = False
        if include_speed_test:
            speed_result, speed_test_ok = safe_run_speed_test()
            if speed_test_ok:
                save_speed_to_csv(speed_result)
        else:
            speed_result = get_last_speed_result()

        write_dashboard_json(speed_result, devices)
        if include_speed_test and speed_test_ok:
            append_speed_to_google_sheet(client, speed_result)

        return {
            "refreshed": True,
            "updated_at": now_text(),
            "devices": len(devices),
            "speed_test": include_speed_test and speed_test_ok,
        }
    finally:
        REFRESH_LOCK.release()


class NetworkDashboardHandler(SimpleHTTPRequestHandler):
    def is_authenticated(self):
        header = self.headers.get("Authorization", "")
        if not header.startswith("Basic "):
            return False

        try:
            decoded = base64.b64decode(header.split(" ", 1)[1]).decode("utf-8")
        except (ValueError, UnicodeDecodeError):
            return False

        username, separator, password = decoded.partition(":")
        return (
            separator == ":"
            and secrets.compare_digest(username, WEB_AUTH_USERNAME)
            and secrets.compare_digest(password, WEB_AUTH_PASSWORD)
        )

    def require_authentication(self):
        self.send_response(401)
        self.send_header("WWW-Authenticate", 'Basic realm="PPD Network Monitor"')
        self.send_header("Content-Type", "text/plain")
        self.end_headers()
        self.wfile.write(b"Authentication required.")

    def send_json(self, status_code, payload):
        body = json.dumps(payload).encode("utf-8")
        self.send_response(status_code)
        self.send_header("Content-Type", "application/json")
        self.send_header("Content-Length", str(len(body)))
        self.end_headers()
        self.wfile.write(body)

    def do_GET(self):
        if not self.is_authenticated():
            self.require_authentication()
            return

        if self.path.split("?", 1)[0] == "/api/devices":
            self.send_json(200, {"devices": read_device_rows()})
            return

        super().do_GET()

    def do_POST(self):
        if not self.is_authenticated():
            self.require_authentication()
            return

        path = self.path.split("?", 1)[0]
        if path == "/api/refresh":
            try:
                self.send_json(200, refresh_dashboard_data(include_speed_test=False))
            except RuntimeError as exc:
                self.send_json(409, {"error": str(exc)})
            except Exception as exc:
                self.send_json(500, {"error": str(exc)})
            return

        if path != "/api/devices":
            self.send_error(404)
            return

        length = int(self.headers.get("Content-Length", "0") or 0)
        try:
            payload = json.loads(self.rfile.read(length).decode("utf-8"))
            action = str(payload.get("Action", "")).strip().lower()

            if action == "delete":
                delete_device(payload.get("IP Address", "").strip())
                self.send_json(200, {"deleted": True})
                return

            device, replaced = add_or_update_device(payload)
            self.send_json(200, {"device": device, "updated": replaced})
        except ValueError as exc:
            self.send_json(400, {"error": str(exc)})
        except Exception as exc:
            self.send_json(500, {"error": str(exc)})

    def do_PUT(self):
        if not self.is_authenticated():
            self.require_authentication()
            return

        if self.path.split("?", 1)[0] != "/api/devices":
            self.send_error(404)
            return

        length = int(self.headers.get("Content-Length", "0") or 0)
        try:
            payload = json.loads(self.rfile.read(length).decode("utf-8"))
            device, replaced = add_or_update_device(payload)
            self.send_json(200, {"device": device, "updated": replaced})
        except ValueError as exc:
            self.send_json(400, {"error": str(exc)})
        except Exception as exc:
            self.send_json(500, {"error": str(exc)})

    def do_DELETE(self):
        if not self.is_authenticated():
            self.require_authentication()
            return

        path, _, query = self.path.partition("?")
        if path != "/api/devices":
            self.send_error(404)
            return

        params = dict(
            item.split("=", 1)
            for item in query.split("&")
            if "=" in item
        )
        try:
            delete_device(params.get("ip", "").strip())
            self.send_json(200, {"deleted": True})
        except ValueError as exc:
            self.send_json(400, {"error": str(exc)})
        except Exception as exc:
            self.send_json(500, {"error": str(exc)})


def start_dashboard_server():
    handler = partial(NetworkDashboardHandler, directory=str(PROJECT_ROOT))
    server = ThreadingHTTPServer((WEB_SERVER_HOST, WEB_SERVER_PORT), handler)
    thread = ThreadPoolExecutor(max_workers=1)
    thread.submit(server.serve_forever)
    local_url = f"http://127.0.0.1:{WEB_SERVER_PORT}/network_dashboard.html"
    lan_url = f"http://{get_lan_ip()}:{WEB_SERVER_PORT}/network_dashboard.html"
    print(f"Dashboard server running locally at {local_url}")
    print(f"Other devices on the same Wi-Fi/LAN can open {lan_url}")
    return server, thread


def main():
    server = None
    server_thread = None

    if "--no-server" not in sys.argv:
        try:
            server, server_thread = start_dashboard_server()
        except OSError as exc:
            print(f"Dashboard server skipped: {exc}")

    while True:
        try:
            refresh_dashboard_data(include_speed_test=True)
            print("Saved successfully!")
        except Exception as e:
            print("Error:", e)

        print(f"Next test after {CHECK_INTERVAL_SECONDS // 60} minutes...")
        if "--once" in sys.argv:
            break

        time.sleep(CHECK_INTERVAL_SECONDS)

    if server:
        server.shutdown()
    if server_thread:
        server_thread.shutdown(wait=False)


if __name__ == "__main__":
    main()
