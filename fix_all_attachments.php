<?php
/**
 * Comprehensive fix for all attachment paths
 */

require_once 'config/database.php';
require_once 'classes/Database.php';

$db = new Database;

// Get all transactions
$db->query('SELECT id, attachment_path FROM transactions WHERE attachment_path IS NOT NULL');
$transactions = $db->resultset();

$upload_dir = __DIR__ . '/uploads/';
$fixed = 0;
$errors = 0;

echo "Processing " . count($transactions) . " transactions...\n\n";

foreach ($transactions as $t) {
    $old_path = $t['attachment_path'];
    $filename = basename($old_path);
    $actual_file = $upload_dir . $filename;

    // Check if file exists
    if (!file_exists($actual_file)) {
        // Try to find the correct file
        $files = glob($upload_dir . 'tx_*');
        $found = false;

        foreach ($files as $file) {
            if (file_exists($file)) {
                $new_name = basename($file);
                $new_path = 'uploads/' . $new_name;

                // Update database
                $db->query("UPDATE transactions SET attachment_path = '$new_path' WHERE id = {$t['id']}");
                if ($db->rowCount() > 0) {
                    $fixed++;
                    echo "FIXED #{$t['id']}: $old_path -> $new_path\n";
                }
                $found = true;
                break;
            }
        }

        if (!$found) {
            $errors++;
            echo "ERROR #{$t['id']}: File '$filename' not found\n";
        }
    } else {
        // File exists, check if path is correct
        if ($old_path != 'uploads/' . $filename) {
            $db->query("UPDATE transactions SET attachment_path = 'uploads/$filename' WHERE id = {$t['id']}");
            $fixed++;
            echo "FIXED #{$t['id']}: Normalized path\n";
        }
    }
}

echo "\n=== Summary ===\n";
echo "Fixed: $fixed\n";
echo "Errors: $errors\n";