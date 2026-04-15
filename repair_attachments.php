<?php
/**
 * Repair all attachment paths to match actual files
 */

require_once 'config/database.php';
require_once 'classes/Database.php';

$db = new Database;
$db->query('SELECT id, attachment_path FROM transactions WHERE attachment_path IS NOT NULL');
$transactions = $db->resultset();

$upload_dir = __DIR__ . '/uploads/';
$fixed = 0;

foreach ($transactions as $t) {
    $old_path = $t['attachment_path'];
    $actual_file = $upload_dir . basename($old_path);

    if (!file_exists($actual_file)) {
        // Find the correct file
        $files = glob($upload_dir . 'tx_*');
        foreach ($files as $file) {
            if (file_exists($file)) {
                $new_name = basename($file);
                $db->query("UPDATE transactions SET attachment_path = '$new_name' WHERE id = {$t['id']}");
                if ($db->rowCount() > 0) {
                    $fixed++;
                    echo "Fixed #{$t['id']}: $old_path -> $new_name\n";
                }
                break;
            }
        }
    }
}

echo "\nTotal fixed: $fixed\n";