<?php
session_start();
if (!isset($_GET['file'])) {
    $_SESSION['error'] = 'No file specified.';
    header('Location: settings.php');
    exit;
}
$file = basename($_GET['file']);
$backupDir = __DIR__ . '/BackupDB/';
$filepath = $backupDir . $file;
if (is_file($filepath)) {
    if (unlink($filepath)) {
        $_SESSION['success'] = 'Backup deleted: ' . htmlspecialchars($file);
    } else {
        $_SESSION['error'] = 'Failed to delete backup.';
    }
} else {
    $_SESSION['error'] = 'File not found.';
}
header('Location: settings.php');
exit;
