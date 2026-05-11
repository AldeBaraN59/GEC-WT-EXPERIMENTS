<?php
/**
 * Handle file uploads securely.
 * Saves files to the 'uploads/' directory.
 */

function uploadFile($fileData, $allowedTypes = ['video/mp4', 'application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'image/webp']) {
    $targetDir = __DIR__ . '/../uploads/';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    if (!isset($fileData['error']) || is_array($fileData['error'])) {
        return ['error' => 'Invalid parameters.'];
    }

    switch ($fileData['error']) {
        case UPLOAD_ERR_OK: break;
        case UPLOAD_ERR_NO_FILE: return ['error' => 'No file sent.'];
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE: return ['error' => 'Exceeded filesize limit.'];
        default: return ['error' => 'Unknown errors.'];
    }

    // Check MIME Type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($fileData['tmp_name']);
    if (!in_array($mime, $allowedTypes)) {
        return ['error' => 'Invalid file format. Allowed: ' . implode(', ', $allowedTypes)];
    }

    // Generate safe filename
    $ext = pathinfo($fileData['name'], PATHINFO_EXTENSION);
    $filename = sprintf('%s.%s', sha1_file($fileData['tmp_name']) . '_' . time(), $ext);
    $targetFilePath = $targetDir . $filename;

    if (!move_uploaded_file($fileData['tmp_name'], $targetFilePath)) {
        return ['error' => 'Failed to move uploaded file.'];
    }

    return ['success' => true, 'path' => 'uploads/' . $filename];
}
?>
