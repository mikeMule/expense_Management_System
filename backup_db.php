<?php
require_once 'config/database.php';
session_start();

$backupDir = __DIR__ . '/BackupDB/';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0777, true);
}

$filename = 'backup_' . DB_NAME . '_' . date('Ymd_His') . '.sql';
$filepath = $backupDir . $filename;

// Windows: use mysqldump from XAMPP
$mysqldump = 'C:/xampp/mysql/bin/mysqldump.exe';
$command = sprintf(
    '"%s" --user=%s --password=%s --host=%s --port=%s %s > "%s"',
    $mysqldump,
    escapeshellarg(DB_USER),
    escapeshellarg(DB_PASS),
    escapeshellarg(DB_HOST),
    escapeshellarg(DB_PORT),
    escapeshellarg(DB_NAME),
    $filepath
);

$output = null;
$return_var = null;
@system($command, $return_var);

if ($return_var === 0 && file_exists($filepath)) {
    $_SESSION['success'] = 'Database backup created: ' . $filename;
} else {
    $_SESSION['error'] = 'Database backup failed.';
}
header('Location: settings.php');
exit;
