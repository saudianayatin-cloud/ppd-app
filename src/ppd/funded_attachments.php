<?php
require_once 'db.php';
header('Content-Type: application/json');

function ensure_funded_attachments_table($conn)
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

    if (!mysqli_query($conn, $sql)) {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        exit;
    }

    $columnResult = mysqli_query($conn, "SHOW COLUMNS FROM funded_attachments LIKE 'folder_id'");
    if ($columnResult && mysqli_num_rows($columnResult) === 0) {
        if (!mysqli_query($conn, "ALTER TABLE funded_attachments ADD COLUMN folder_id INT DEFAULT NULL")) {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
            exit;
        }
    }

    $indexResult = mysqli_query($conn, "SHOW INDEX FROM funded_attachments WHERE Key_name = 'idx_funded_attachment_folder'");
    if ($indexResult && mysqli_num_rows($indexResult) === 0) {
        mysqli_query($conn, "ALTER TABLE funded_attachments ADD INDEX idx_funded_attachment_folder (folder_id)");
    }

    $folderSql = "CREATE TABLE IF NOT EXISTS funded_attachment_folders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        funded_id INT NOT NULL,
        parent_id INT DEFAULT NULL,
        name VARCHAR(120) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_funded_folder_parent (parent_id),
        INDEX (funded_id)
    )";

    if (!mysqli_query($conn, $folderSql)) {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        exit;
    }

    $folderParentResult = mysqli_query($conn, "SHOW COLUMNS FROM funded_attachment_folders LIKE 'parent_id'");
    if ($folderParentResult && mysqli_num_rows($folderParentResult) === 0) {
        if (!mysqli_query($conn, "ALTER TABLE funded_attachment_folders ADD COLUMN parent_id INT DEFAULT NULL AFTER funded_id")) {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
            exit;
        }
    }

    $oldUniqueResult = mysqli_query($conn, "SHOW INDEX FROM funded_attachment_folders WHERE Key_name = 'unique_project_folder'");
    if ($oldUniqueResult && mysqli_num_rows($oldUniqueResult) > 0) {
        mysqli_query($conn, "ALTER TABLE funded_attachment_folders DROP INDEX unique_project_folder");
    }

    $folderParentIndex = mysqli_query($conn, "SHOW INDEX FROM funded_attachment_folders WHERE Key_name = 'idx_funded_folder_parent'");
    if ($folderParentIndex && mysqli_num_rows($folderParentIndex) === 0) {
        mysqli_query($conn, "ALTER TABLE funded_attachment_folders ADD INDEX idx_funded_folder_parent (parent_id)");
    }
}

function clean_upload_name($name)
{
    return preg_replace('/[^A-Za-z0-9._ -]/', '_', basename($name));
}

function clean_folder_name($name)
{
    $name = trim(preg_replace('/[^A-Za-z0-9._&() -]/', '_', $name));
    return trim($name, ". \t\n\r\0\x0B");
}

function default_funded_project_folders()
{
    return [
        '1. Validation Report',
        '2. Validation Certification',
        '3. Validation Data Forms',
        '4. Shapefiles',
        '5. DED Plans & Cost Estimates',
        '6. Realignment_Modification Memo (If Necessary)',
        '7. Transmittals to DEO & Divisions',
    ];
}

function is_default_funded_project_folder($folder)
{
    $parentId = isset($folder['parent_id']) ? intval($folder['parent_id']) : 0;
    return $parentId <= 0 && in_array((string) ($folder['name'] ?? ''), default_funded_project_folders(), true);
}

function ensure_default_funded_project_folders($conn, $fundedId)
{
    foreach (default_funded_project_folders() as $folderName) {
        get_or_create_upload_folder($conn, $fundedId, 0, $folderName);
    }
}

function clean_relative_upload_path($path)
{
    $path = str_replace('\\', '/', (string) $path);
    $parts = [];

    foreach (explode('/', $path) as $part) {
        $part = trim($part);

        if ($part === '' || $part === '.' || $part === '..') {
            continue;
        }

        $parts[] = $part;
    }

    return implode('/', $parts);
}

function get_or_create_upload_folder($conn, $fundedId, $parentId, $folderName)
{
    $folderName = clean_folder_name($folderName);

    if ($folderName === '') {
        return null;
    }

    if ($parentId > 0) {
        $stmt = mysqli_prepare($conn, "SELECT id FROM funded_attachment_folders WHERE funded_id = ? AND parent_id = ? AND name = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'iis', $fundedId, $parentId, $folderName);
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id FROM funded_attachment_folders WHERE funded_id = ? AND parent_id IS NULL AND name = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'is', $fundedId, $folderName);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $existing = mysqli_fetch_assoc($result);

    if ($existing) {
        return intval($existing['id']);
    }

    if ($parentId > 0) {
        $insertStmt = mysqli_prepare($conn, "INSERT INTO funded_attachment_folders (funded_id, parent_id, name) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($insertStmt, 'iis', $fundedId, $parentId, $folderName);
    } else {
        $insertStmt = mysqli_prepare($conn, "INSERT INTO funded_attachment_folders (funded_id, name) VALUES (?, ?)");
        mysqli_stmt_bind_param($insertStmt, 'is', $fundedId, $folderName);
    }

    if (!mysqli_stmt_execute($insertStmt)) {
        return null;
    }

    return mysqli_insert_id($conn);
}

function get_or_create_upload_folder_path($conn, $fundedId, $baseFolderId, $relativePath)
{
    $relativePath = clean_relative_upload_path($relativePath);

    if ($relativePath === '') {
        return $baseFolderId;
    }

    $parts = explode('/', $relativePath);
    array_pop($parts);

    if (!count($parts)) {
        return $baseFolderId;
    }

    $parentId = $baseFolderId;

    foreach ($parts as $part) {
        $nextId = get_or_create_upload_folder($conn, $fundedId, $parentId, $part);

        if (!$nextId) {
            return false;
        }

        $parentId = $nextId;
    }

    return $parentId;
}

function collect_folder_descendant_ids($conn, $fundedId, $folderId)
{
    $folderIds = [$folderId];
    $queue = [$folderId];

    while (count($queue)) {
        $currentId = array_shift($queue);
        $stmt = mysqli_prepare($conn, "SELECT id FROM funded_attachment_folders WHERE funded_id = ? AND parent_id = ?");
        mysqli_stmt_bind_param($stmt, 'ii', $fundedId, $currentId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            $childId = intval($row['id']);
            $folderIds[] = $childId;
            $queue[] = $childId;
        }
    }

    return array_values(array_unique($folderIds));
}

function format_file_size($bytes)
{
    if ($bytes === null || $bytes === false) {
        return '0 B';
    }

    $units = ['B', 'KB', 'MB', 'GB'];
    $size = (float) $bytes;
    $unitIndex = 0;

    while ($size >= 1024 && $unitIndex < count($units) - 1) {
        $size /= 1024;
        $unitIndex++;
    }

    return ($unitIndex === 0 ? number_format($size, 0) : number_format($size, 2)) . ' ' . $units[$unitIndex];
}

function upload_error_message($code)
{
    switch ($code) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return 'File is too large for the current PHP upload limit.';
        case UPLOAD_ERR_PARTIAL:
            return 'File was only partially uploaded. Please try again.';
        case UPLOAD_ERR_NO_FILE:
            return 'No file was selected.';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Server upload temp folder is missing.';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Server could not write the uploaded file.';
        case UPLOAD_ERR_EXTENSION:
            return 'Upload was stopped by a PHP extension.';
        default:
            return 'Upload failed.';
    }
}

ensure_funded_attachments_table($conn);

$method = $_SERVER['REQUEST_METHOD'];
$action = $_POST['action'] ?? $_GET['action'] ?? 'list';
$uploadDir = __DIR__ . '/funded_uploads/';
$uploadUrl = 'funded_uploads/';
$allowed = [
    'pdf',
    'jpg',
    'jpeg',
    'png',
    'gif',
    'webp',
    'doc',
    'docx',
    'xls',
    'xlsx',
    'csv',
    'txt',
    'mp4',
    'kml',
    'kmz',
    'cpg',
    'dbf',
    'prj',
    'qmd',
    'shp',
    'shx',
    'zip',
    'rar',
    '7z'
];

if ($action === 'list' && $method === 'GET') {
    $fundedId = intval($_GET['funded_id'] ?? 0);

    if ($fundedId <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid project id.']);
        exit;
    }

    ensure_default_funded_project_folders($conn, $fundedId);

    $foldersStmt = mysqli_prepare($conn, "SELECT id, parent_id, name, created_at FROM funded_attachment_folders WHERE funded_id = ? ORDER BY name ASC");
    mysqli_stmt_bind_param($foldersStmt, 'i', $fundedId);
    mysqli_stmt_execute($foldersStmt);
    $foldersResult = mysqli_stmt_get_result($foldersStmt);

    $folders = [];
    while ($folder = mysqli_fetch_assoc($foldersResult)) {
        $folder['is_protected'] = is_default_funded_project_folder($folder);
        $folders[] = $folder;
    }

    $stmt = mysqli_prepare($conn, "SELECT id, funded_id, file_name, original_name, file_type, folder_id, uploaded_at FROM funded_attachments WHERE funded_id = ? ORDER BY uploaded_at DESC, id DESC");
    mysqli_stmt_bind_param($stmt, 'i', $fundedId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $files = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $filePath = $uploadDir . $row['file_name'];
        $fileSize = file_exists($filePath) ? filesize($filePath) : 0;

        $row['url'] = $uploadUrl . rawurlencode($row['file_name']);
        $row['size_bytes'] = $fileSize;
        $row['size_label'] = format_file_size($fileSize);
        $files[] = $row;
    }

    echo json_encode(['status' => 'success', 'folders' => $folders, 'files' => $files]);
    exit;
}

if ($action === 'create_folder' && $method === 'POST') {
    $fundedId = intval($_POST['funded_id'] ?? 0);
    $parentId = intval($_POST['parent_id'] ?? 0);
    $folderName = clean_folder_name($_POST['folder_name'] ?? '');

    if ($fundedId <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid project id.']);
        exit;
    }

    if ($folderName === '') {
        echo json_encode(['status' => 'error', 'message' => 'Please enter a folder name.']);
        exit;
    }

    if ($parentId > 0) {
        $parentStmt = mysqli_prepare($conn, "SELECT id FROM funded_attachment_folders WHERE id = ? AND funded_id = ? LIMIT 1");
        mysqli_stmt_bind_param($parentStmt, 'ii', $parentId, $fundedId);
        mysqli_stmt_execute($parentStmt);
        $parentResult = mysqli_stmt_get_result($parentStmt);

        if (!mysqli_fetch_assoc($parentResult)) {
            echo json_encode(['status' => 'error', 'message' => 'Parent folder was not found.']);
            exit;
        }
    }

    if ($parentId > 0) {
        $duplicateStmt = mysqli_prepare($conn, "SELECT id FROM funded_attachment_folders WHERE funded_id = ? AND parent_id = ? AND name = ? LIMIT 1");
        mysqli_stmt_bind_param($duplicateStmt, 'iis', $fundedId, $parentId, $folderName);
    } else {
        $duplicateStmt = mysqli_prepare($conn, "SELECT id FROM funded_attachment_folders WHERE funded_id = ? AND parent_id IS NULL AND name = ? LIMIT 1");
        mysqli_stmt_bind_param($duplicateStmt, 'is', $fundedId, $folderName);
    }

    mysqli_stmt_execute($duplicateStmt);
    $duplicateResult = mysqli_stmt_get_result($duplicateStmt);

    if (mysqli_fetch_assoc($duplicateResult)) {
        echo json_encode(['status' => 'error', 'message' => 'Folder already exists in this location.']);
        exit;
    }

    if ($parentId > 0) {
        $stmt = mysqli_prepare($conn, "INSERT INTO funded_attachment_folders (funded_id, parent_id, name) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'iis', $fundedId, $parentId, $folderName);
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO funded_attachment_folders (funded_id, name) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, 'is', $fundedId, $folderName);
    }

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['status' => 'success', 'message' => 'Folder created.']);
        exit;
    }

    if (mysqli_errno($conn) == 1062) {
        echo json_encode(['status' => 'error', 'message' => 'Folder already exists in this location.']);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
    exit;
}

if ($action === 'rename_folder' && $method === 'POST') {
    $fundedId = intval($_POST['funded_id'] ?? 0);
    $folderId = intval($_POST['folder_id'] ?? 0);
    $folderName = clean_folder_name($_POST['folder_name'] ?? '');

    if ($fundedId <= 0 || $folderId <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid folder id.']);
        exit;
    }

    if ($folderName === '') {
        echo json_encode(['status' => 'error', 'message' => 'Please enter a folder name.']);
        exit;
    }

    $currentStmt = mysqli_prepare($conn, "SELECT parent_id, name FROM funded_attachment_folders WHERE id = ? AND funded_id = ? LIMIT 1");
    mysqli_stmt_bind_param($currentStmt, 'ii', $folderId, $fundedId);
    mysqli_stmt_execute($currentStmt);
    $currentResult = mysqli_stmt_get_result($currentStmt);
    $currentFolder = mysqli_fetch_assoc($currentResult);

    if (!$currentFolder) {
        echo json_encode(['status' => 'error', 'message' => 'Folder was not found.']);
        exit;
    }

    $parentId = intval($currentFolder['parent_id'] ?? 0);

    if (is_default_funded_project_folder($currentFolder)) {
        echo json_encode(['status' => 'error', 'message' => 'Default project folders cannot be renamed.']);
        exit;
    }

    if ($parentId > 0) {
        $duplicateStmt = mysqli_prepare($conn, "SELECT id FROM funded_attachment_folders WHERE funded_id = ? AND parent_id = ? AND name = ? AND id <> ? LIMIT 1");
        mysqli_stmt_bind_param($duplicateStmt, 'iisi', $fundedId, $parentId, $folderName, $folderId);
    } else {
        $duplicateStmt = mysqli_prepare($conn, "SELECT id FROM funded_attachment_folders WHERE funded_id = ? AND parent_id IS NULL AND name = ? AND id <> ? LIMIT 1");
        mysqli_stmt_bind_param($duplicateStmt, 'isi', $fundedId, $folderName, $folderId);
    }

    mysqli_stmt_execute($duplicateStmt);
    $duplicateResult = mysqli_stmt_get_result($duplicateStmt);

    if (mysqli_fetch_assoc($duplicateResult)) {
        echo json_encode(['status' => 'error', 'message' => 'Folder already exists in this location.']);
        exit;
    }

    $stmt = mysqli_prepare($conn, "UPDATE funded_attachment_folders SET name = ? WHERE id = ? AND funded_id = ?");
    mysqli_stmt_bind_param($stmt, 'sii', $folderName, $folderId, $fundedId);

    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_stmt_affected_rows($stmt) === 0) {
            $checkStmt = mysqli_prepare($conn, "SELECT id FROM funded_attachment_folders WHERE id = ? AND funded_id = ? LIMIT 1");
            mysqli_stmt_bind_param($checkStmt, 'ii', $folderId, $fundedId);
            mysqli_stmt_execute($checkStmt);
            $checkResult = mysqli_stmt_get_result($checkStmt);

            if (!mysqli_fetch_assoc($checkResult)) {
                echo json_encode(['status' => 'error', 'message' => 'Folder was not found.']);
                exit;
            }

            echo json_encode(['status' => 'success', 'message' => 'Folder name is already updated.']);
            exit;
        }

        echo json_encode(['status' => 'success', 'message' => 'Folder renamed.']);
        exit;
    }

    if (mysqli_errno($conn) == 1062) {
        echo json_encode(['status' => 'error', 'message' => 'Folder already exists.']);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
    exit;
}

if ($action === 'upload' && $method === 'POST') {
    $fundedId = intval($_POST['funded_id'] ?? 0);
    $folderId = intval($_POST['folder_id'] ?? 0);

    if ($fundedId <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid project id.']);
        exit;
    }

    if (empty($_FILES['files']['name'][0])) {
        echo json_encode(['status' => 'error', 'message' => 'Please choose at least one file.']);
        exit;
    }

    if ($folderId > 0) {
        $folderStmt = mysqli_prepare($conn, "SELECT id FROM funded_attachment_folders WHERE id = ? AND funded_id = ? LIMIT 1");
        mysqli_stmt_bind_param($folderStmt, 'ii', $folderId, $fundedId);
        mysqli_stmt_execute($folderStmt);
        $folderResult = mysqli_stmt_get_result($folderStmt);

        if (!mysqli_fetch_assoc($folderResult)) {
            echo json_encode(['status' => 'error', 'message' => 'Selected folder was not found.']);
            exit;
        }
    }

    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        echo json_encode(['status' => 'error', 'message' => 'Upload folder cannot be created.']);
        exit;
    }

    $uploaded = [];
    $errors = [];

    foreach ($_FILES['files']['name'] as $index => $name) {
        if ($_FILES['files']['error'][$index] !== UPLOAD_ERR_OK) {
            $errors[] = $name . ' - ' . upload_error_message($_FILES['files']['error'][$index]);
            continue;
        }

        $relativePath = $_POST['relative_paths'][$index] ?? ($_FILES['files']['full_path'][$index] ?? '');
        $targetFolderId = get_or_create_upload_folder_path($conn, $fundedId, $folderId, $relativePath);

        if ($targetFolderId === false) {
            $errors[] = clean_upload_name($name) . ' could not create its folder path.';
            continue;
        }

        $originalName = clean_upload_name($name);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($extension, $allowed)) {
            $errors[] = $originalName . ' has an invalid file type.';
            continue;
        }

        $newFileName = uniqid('doc_', true) . '.' . $extension;
        $target = $uploadDir . $newFileName;

        if (!move_uploaded_file($_FILES['files']['tmp_name'][$index], $target)) {
            $errors[] = $originalName . ' could not be saved.';
            continue;
        }

        if ($targetFolderId > 0) {
            $stmt = mysqli_prepare($conn, "INSERT INTO funded_attachments (funded_id, file_name, original_name, file_type, folder_id) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'isssi', $fundedId, $newFileName, $originalName, $extension, $targetFolderId);
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO funded_attachments (funded_id, file_name, original_name, file_type) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'isss', $fundedId, $newFileName, $originalName, $extension);
        }

        if (!mysqli_stmt_execute($stmt)) {
            if (file_exists($target)) {
                unlink($target);
            }
            $errors[] = $originalName . ' could not be saved to database.';
            continue;
        }

        $uploaded[] = $originalName;
    }

    if (count($uploaded) === 0) {
        echo json_encode(['status' => 'error', 'message' => implode(' ', $errors) ?: 'No files uploaded.']);
        exit;
    }

    echo json_encode([
        'status' => 'success',
        'message' => count($uploaded) . ' file(s) uploaded.' . (count($errors) ? ' Some files were skipped.' : ''),
        'errors' => $errors
    ]);
    exit;
}

if ($action === 'move_file' && $method === 'POST') {
    $fundedId = intval($_POST['funded_id'] ?? 0);
    $attachmentId = intval($_POST['id'] ?? 0);
    $folderId = intval($_POST['folder_id'] ?? 0);

    if ($fundedId <= 0 || $attachmentId <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid file id.']);
        exit;
    }

    $fileStmt = mysqli_prepare($conn, "SELECT id, folder_id FROM funded_attachments WHERE id = ? AND funded_id = ? LIMIT 1");
    mysqli_stmt_bind_param($fileStmt, 'ii', $attachmentId, $fundedId);
    mysqli_stmt_execute($fileStmt);
    $fileResult = mysqli_stmt_get_result($fileStmt);
    $file = mysqli_fetch_assoc($fileResult);

    if (!$file) {
        echo json_encode(['status' => 'error', 'message' => 'Attachment not found.']);
        exit;
    }

    if ($folderId > 0) {
        $folderStmt = mysqli_prepare($conn, "SELECT id FROM funded_attachment_folders WHERE id = ? AND funded_id = ? LIMIT 1");
        mysqli_stmt_bind_param($folderStmt, 'ii', $folderId, $fundedId);
        mysqli_stmt_execute($folderStmt);
        $folderResult = mysqli_stmt_get_result($folderStmt);

        if (!mysqli_fetch_assoc($folderResult)) {
            echo json_encode(['status' => 'error', 'message' => 'Destination folder was not found.']);
            exit;
        }

        $stmt = mysqli_prepare($conn, "UPDATE funded_attachments SET folder_id = ? WHERE id = ? AND funded_id = ?");
        mysqli_stmt_bind_param($stmt, 'iii', $folderId, $attachmentId, $fundedId);
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE funded_attachments SET folder_id = NULL WHERE id = ? AND funded_id = ?");
        mysqli_stmt_bind_param($stmt, 'ii', $attachmentId, $fundedId);
    }

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['status' => 'success', 'message' => 'File moved.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
    }
    exit;
}

if ($action === 'move_folder' && $method === 'POST') {
    $fundedId = intval($_POST['funded_id'] ?? 0);
    $folderId = intval($_POST['folder_id'] ?? 0);
    $parentId = intval($_POST['parent_id'] ?? 0);

    if ($fundedId <= 0 || $folderId <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid folder id.']);
        exit;
    }

    $folderStmt = mysqli_prepare($conn, "SELECT id, parent_id FROM funded_attachment_folders WHERE id = ? AND funded_id = ? LIMIT 1");
    mysqli_stmt_bind_param($folderStmt, 'ii', $folderId, $fundedId);
    mysqli_stmt_execute($folderStmt);
    $folderResult = mysqli_stmt_get_result($folderStmt);
    $folder = mysqli_fetch_assoc($folderResult);

    if (!$folder) {
        echo json_encode(['status' => 'error', 'message' => 'Folder was not found.']);
        exit;
    }

    if ($parentId === $folderId) {
        echo json_encode(['status' => 'error', 'message' => 'A folder cannot be pasted into itself.']);
        exit;
    }

    $descendantIds = collect_folder_descendant_ids($conn, $fundedId, $folderId);
    if ($parentId > 0 && in_array($parentId, $descendantIds, true)) {
        echo json_encode(['status' => 'error', 'message' => 'A folder cannot be pasted inside its own subfolder.']);
        exit;
    }

    if ($parentId > 0) {
        $parentStmt = mysqli_prepare($conn, "SELECT id FROM funded_attachment_folders WHERE id = ? AND funded_id = ? LIMIT 1");
        mysqli_stmt_bind_param($parentStmt, 'ii', $parentId, $fundedId);
        mysqli_stmt_execute($parentStmt);
        $parentResult = mysqli_stmt_get_result($parentStmt);

        if (!mysqli_fetch_assoc($parentResult)) {
            echo json_encode(['status' => 'error', 'message' => 'Destination folder was not found.']);
            exit;
        }

        $stmt = mysqli_prepare($conn, "UPDATE funded_attachment_folders SET parent_id = ? WHERE id = ? AND funded_id = ?");
        mysqli_stmt_bind_param($stmt, 'iii', $parentId, $folderId, $fundedId);
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE funded_attachment_folders SET parent_id = NULL WHERE id = ? AND funded_id = ?");
        mysqli_stmt_bind_param($stmt, 'ii', $folderId, $fundedId);
    }

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['status' => 'success', 'message' => 'Folder moved.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
    }
    exit;
}

if ($action === 'delete_folder' && $method === 'POST') {
    $fundedId = intval($_POST['funded_id'] ?? 0);
    $folderId = intval($_POST['folder_id'] ?? 0);

    if ($fundedId <= 0 || $folderId <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid folder id.']);
        exit;
    }

    $folderStmt = mysqli_prepare($conn, "SELECT id, parent_id, name FROM funded_attachment_folders WHERE id = ? AND funded_id = ? LIMIT 1");
    mysqli_stmt_bind_param($folderStmt, 'ii', $folderId, $fundedId);
    mysqli_stmt_execute($folderStmt);
    $folderResult = mysqli_stmt_get_result($folderStmt);
    $folder = mysqli_fetch_assoc($folderResult);

    if (!$folder) {
        echo json_encode(['status' => 'error', 'message' => 'Folder was not found.']);
        exit;
    }

    if (is_default_funded_project_folder($folder)) {
        echo json_encode(['status' => 'error', 'message' => 'Default project folders cannot be deleted.']);
        exit;
    }

    $folderIds = collect_folder_descendant_ids($conn, $fundedId, $folderId);
    $idList = implode(',', array_map('intval', $folderIds));

    $filesResult = mysqli_query($conn, "SELECT id, file_name FROM funded_attachments WHERE funded_id = " . intval($fundedId) . " AND folder_id IN ($idList)");
    $fileNames = [];
    while ($file = mysqli_fetch_assoc($filesResult)) {
        $fileNames[] = $file['file_name'];
    }

    mysqli_begin_transaction($conn);

    $deleteFilesOk = mysqli_query($conn, "DELETE FROM funded_attachments WHERE funded_id = " . intval($fundedId) . " AND folder_id IN ($idList)");
    $deleteFoldersOk = mysqli_query($conn, "DELETE FROM funded_attachment_folders WHERE funded_id = " . intval($fundedId) . " AND id IN ($idList)");

    if (!$deleteFilesOk || !$deleteFoldersOk) {
        mysqli_rollback($conn);
        echo json_encode(['status' => 'error', 'message' => mysqli_error($conn) ?: 'Folder could not be deleted.']);
        exit;
    }

    mysqli_commit($conn);

    foreach ($fileNames as $fileName) {
        $filePath = $uploadDir . $fileName;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    echo json_encode(['status' => 'success', 'message' => 'Folder deleted.']);
    exit;
}

if ($action === 'delete' && $method === 'POST') {
    $attachmentId = intval($_POST['id'] ?? 0);

    if ($attachmentId <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid attachment id.']);
        exit;
    }

    $stmt = mysqli_prepare($conn, "SELECT file_name FROM funded_attachments WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $attachmentId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $file = mysqli_fetch_assoc($result);

    if (!$file) {
        echo json_encode(['status' => 'error', 'message' => 'Attachment not found.']);
        exit;
    }

    $filePath = $uploadDir . $file['file_name'];
    if (file_exists($filePath)) {
        unlink($filePath);
    }

    $stmt = mysqli_prepare($conn, "DELETE FROM funded_attachments WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $attachmentId);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['status' => 'success', 'message' => 'File deleted.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
