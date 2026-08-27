import csv
import ipaddress
import platform
import re
import socket
import subprocess
from concurrent.futures import ThreadPoolExecutor, as_completed
from pathlib import Path


OUTPUT_CSV = Path("src/python/devices.csv")
DEFAULT_NETWORK = "192.168.1.0/24"


def run_command(command, timeout=8):
    result = subprocess.run(
        command,
        capture_output=True,
        text=True,
        timeout=timeout,
        check=False,
    )
    return result.stdout + result.stderr


def get_local_network():
    output = run_command(["ipconfig"])
    match = re.search(r"IPv4 Address[.\s]*:\s*(192\.168\.\d+\.\d+)", output)

    if not match:
        return ipaddress.ip_network(DEFAULT_NETWORK, strict=False)

    local_ip = ipaddress.ip_address(match.group(1))
    return ipaddress.ip_network(f"{local_ip}/24", strict=False)


def ping_ip(ip_address):
    system = platform.system().lower()
    command = ["ping", "-n", "1", "-w", "500", str(ip_address)]

    if system != "windows":
        command = ["ping", "-c", "1", "-W", "1", str(ip_address)]

    result = subprocess.run(
        command,
        capture_output=True,
        text=True,
        timeout=3,
        check=False,
    )
    return result.returncode == 0


def get_arp_table():
    output = run_command(["arp", "-a"])
    entries = {}

    for line in output.splitlines():
        match = re.search(
            r"(\d+\.\d+\.\d+\.\d+)\s+([0-9a-fA-F-]{17})\s+(\w+)",
            line,
        )
        if match:
            entries[match.group(1)] = match.group(2)

    return entries


def get_hostname(ip_address):
    try:
        return socket.gethostbyaddr(str(ip_address))[0]
    except OSError:
        return f"Device {ip_address}"


def discover_devices():
    network = get_local_network()
    print(f"Scanning network: {network}")

    reachable = []
    with ThreadPoolExecutor(max_workers=64) as executor:
        futures = {executor.submit(ping_ip, ip): ip for ip in network.hosts()}

        for future in as_completed(futures):
            ip = futures[future]
            if future.result():
                reachable.append(ip)
                print(f"Found: {ip}")

    arp_entries = get_arp_table()
    rows = []

    for ip in sorted(reachable, key=lambda value: int(value)):
        rows.append({
            "Device Name": get_hostname(ip),
            "IP Address": str(ip),
            "MAC Address": arp_entries.get(str(ip), ""),
            "Device Type": "Network Device",
            "Notes": "Auto discovered",
        })

    return rows


def save_devices(rows):
    headers = ["Device Name", "IP Address", "MAC Address", "Device Type", "Notes"]
    with OUTPUT_CSV.open("w", newline="") as file:
        writer = csv.DictWriter(file, fieldnames=headers)
        writer.writeheader()
        writer.writerows(rows)

    print(f"Saved {len(rows)} devices to {OUTPUT_CSV}.")


def main():
    rows = discover_devices()
    save_devices(rows)


if __name__ == "__main__":
    main()
