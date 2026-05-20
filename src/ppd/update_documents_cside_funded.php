

<?php
require_once 'db.php';
header('Content-Type: application/json');

if (!isset($_POST['stud_id2'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
    exit;
}

$id = mysqli_real_escape_string($conn, $_POST['stud_id2']);
$deo = mysqli_real_escape_string($conn, $_POST['deo']);
$stud_no = mysqli_real_escape_string($conn, $_POST['stud_no']);
$proj_name = mysqli_real_escape_string($conn, $_POST['proj_name']);
$municipality = mysqli_real_escape_string($conn, $_POST['municipality']);
$barangay = mysqli_real_escape_string($conn, $_POST['barangay']);
$sitio = mysqli_real_escape_string($conn, $_POST['sitio']);
$cy = mysqli_real_escape_string($conn, $_POST['cy']);
$fund_source_raw = trim($_POST['fund_source'] ?? '');
if (strtoupper($fund_source_raw) === 'CONTINGENCY FUND') {
    $fund_source_raw = 'Contingency Fund';
}
$fund_source = mysqli_real_escape_string($conn, $fund_source_raw);
$moi = mysqli_real_escape_string($conn, $_POST['moi']);
$proj_target = mysqli_real_escape_string($conn, $_POST['proj_target']);
$proj_plan = mysqli_real_escape_string($conn, $_POST['proj_plan']);
$cost = mysqli_real_escape_string($conn, $_POST['cost']);
$proponent = mysqli_real_escape_string($conn, $_POST['proponent']);
// $status = mysqli_real_escape_string($conn, $_POST['status']);
// $ro_remarks = mysqli_real_escape_string($conn, $_POST['ro_remarks']);
// $deo_remarks = mysqli_real_escape_string($conn, $_POST['deo_remarks']);



/* ===============================
   DATE VALIDATION
=============================== */
// if (!empty($dat)) {
//     if (!strtotime($dat)) {
//         echo json_encode(['status' => 'error', 'message' => 'Invalid date format']);
//         exit;
//     }
//     $dat = date('Y-m-d', strtotime($dat));
// }

$update_file = "";

/* ===============================
   FILE UPLOAD HANDLING
=============================== */
if (!empty($_FILES['file']['name'])) {

    // ✅ CANCEL / FAILED UPLOAD CHECK
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['status' => 'error', 'message' => 'Upload cancelled or failed']);
        exit;
    }

    // include "common_directory.php";
    $uploadFileDir = "funded_uploads/"; // relative to your project

    // Get old file
    $query = mysqli_query($conn, "SELECT file FROM funded WHERE stud_id2 = '$id'");
    $row = mysqli_fetch_assoc($query);
    $oldFile = $row['file'] ?? '';

    $fileTmpPath = $_FILES['file']['tmp_name'];
    $fileName = $_FILES['file']['name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $allowed = ['jpg', 'jpeg', 'png', 'pdf', 'docx', 'xlsx', 'txt', 'mp4'];

    if (!in_array($fileExtension, $allowed)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid file type. Allowed: ' . implode(", ", $allowed)
        ]);
        exit;
    }

    if (!is_dir($uploadFileDir)) {
        mkdir($uploadFileDir, 0755, true);
    }

    $newFileName = uniqid("doc_", true) . "." . $fileExtension;
    $dest_path = $uploadFileDir . $newFileName;

    if (!move_uploaded_file($fileTmpPath, $dest_path)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'There was an error moving the uploaded file.'
        ]);
        exit;
    }

    // Delete old file
    if (!empty($oldFile) && file_exists($uploadFileDir . $oldFile)) {
        unlink($uploadFileDir . $oldFile);
    }

    $update_file = ", file = '$newFileName'";
}

/* ===============================
   UPDATE QUERY
=============================== */
$sql = "UPDATE funded SET
                deo = '$deo',
                stud_no = '$stud_no',
                proj_name = '$proj_name',
                municipality = '$municipality',
                barangay = '$barangay',
                sitio = '$sitio',
                cy = '$cy',
                fund_source = '$fund_source',
                moi = '$moi',
                proj_target = '$proj_target',
                proj_plan = '$proj_plan',
                cost = '$cost',
                proponent = '$proponent'
                $update_file
        WHERE stud_id2 = '$id'";

if (mysqli_query($conn, $sql)) {

    // AJAX vs normal submit
    if (
        !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
    ) {

        echo json_encode([
            'status' => 'success',
            'message' => 'Record updated successfully.'
        ]);
    } else {
        header('Location: indexdocs_cside_funded.php');
    }
    exit;
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error updating data: ' . mysqli_error($conn)
    ]);
    exit;
}
