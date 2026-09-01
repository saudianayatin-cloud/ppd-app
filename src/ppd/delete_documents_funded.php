
<?php
require_once 'db.php';
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Unknown error occurred'];

function ensure_funded_attachments_table_for_delete($conn)
{
    $sql = "CREATE TABLE IF NOT EXISTS funded_attachments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        funded_id INT NOT NULL,
        file_name VARCHAR(255) NOT NULL,
        original_name VARCHAR(255) DEFAULT NULL,
        file_type VARCHAR(20) DEFAULT NULL,
        folder_id INT DEFAULT NULL,
        uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX (funded_id)
    )";

    mysqli_query($conn, $sql);

    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS funded_attachment_folders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        funded_id INT NOT NULL,
        parent_id INT DEFAULT NULL,
        name VARCHAR(120) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_funded_folder_parent (parent_id),
        INDEX (funded_id)
    )");
}

if (isset($_POST['stud_id2'])) {
    $stud_id = mysqli_real_escape_string($conn, $_POST['stud_id2']);

    // Fetch file names first
    $query = mysqli_query($conn, "SELECT file FROM `funded` WHERE `stud_id2` = '$stud_id'");
    
    if ($query && mysqli_num_rows($query) > 0) {
        $fetch = mysqli_fetch_assoc($query);
        $uploadPath = __DIR__ . '/funded_uploads/';

        // Delete files if they exist
        foreach (['file'] as $col) {
            if (!empty($fetch[$col])) {
                $filePath = $uploadPath . $fetch[$col];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        }

        ensure_funded_attachments_table_for_delete($conn);
        $attachments = mysqli_query($conn, "SELECT file_name FROM funded_attachments WHERE funded_id = '$stud_id'");
        if ($attachments) {
            while ($attachment = mysqli_fetch_assoc($attachments)) {
                if (!empty($attachment['file_name'])) {
                    $attachmentPath = $uploadPath . $attachment['file_name'];
                    if (file_exists($attachmentPath)) {
                        unlink($attachmentPath);
                    }
                }
            }
            mysqli_query($conn, "DELETE FROM funded_attachments WHERE funded_id = '$stud_id'");
            mysqli_query($conn, "DELETE FROM funded_attachment_folders WHERE funded_id = '$stud_id'");
        }

        // Delete the database record
        if (mysqli_query($conn, "DELETE FROM `funded` WHERE `stud_id2` = '$stud_id'")) {
            $response = ['success' => true, 'message' => 'Record and files deleted successfully'];
        } else {
            $response['message'] = 'Failed to delete database record: ' . mysqli_error($conn);
        }
    } else {
        $response['message'] = 'Record not found';
    }
} else {
    $response['message'] = 'No ID provided';
}

echo json_encode($response);
$conn->close();
?>
