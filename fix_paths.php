<?php
/**
 * Direct fix for attachment paths - update database with correct file paths
 */

require_once 'config/database.php';
require_once 'classes/Database.php';

// Connect to database
$db = new Database;

// Get all transactions with attachments
$db->query("SELECT id, attachment_path FROM transactions WHERE attachment_path IS NOT NULL AND attachment_path != ''");
$transactions = $db->resultset();

$upload_dir = __DIR__ . '/uploads/';
$fixed_count = 0;
$errors = [];

echo "Checking " . count($transactions) . " transactions with attachments...\n\n";

foreach ($transactions as $t) {
    $old_path = $t['attachment_path'];
    $filename = basename($old_path);

    // Check if file exists
    if (!file_exists($upload_dir . $filename)) {
        // Try to find file with similar name (tx_ prefix)
        $files = glob($upload_dir . 'tx_*');
        $found = false;

        foreach ($files as $file) {
            $basename = basename($file);
            // Match files starting with tx_
            if (strpos($basename, 'tx_') === 0) {
                $new_path = 'uploads/' . $basename;
                $update_query = "UPDATE transactions SET attachment_path = '$new_path' WHERE id = {$t['id']}";
                $db->query($update_query);
                $fixed_count++;
                echo "FIXED: Transaction {$t['id']}: {$filename} -> {$basename}\n";
                $found = true;
                break;
            }
        }

        if (!$found) {
            $errors[] = "Transaction {$t['id']}: File '$filename' not found";
            echo "ERROR: Transaction {$t['id']} - File '$filename' not found\n";
        }
    } else {
        // File exists, check if path needs normalization
        $expected_path = 'uploads/' . $filename;
        if ($old_path != $expected_path) {
            $update_query = "UPDATE transactions SET attachment_path = '$expected_path' WHERE id = {$t['id']}";
            $db->query($update_query);
            $fixed_count++;
            echo "FIXED: Transaction {$t['id']}: Normalized path\n";
        }
    }
}

echo "\n=== Results ===\n";
echo "Fixed: $fixed_count transactions\n";
echo "Errors: " . count($errors) . " transactions\n";

if (!empty($errors)) {
    echo "\nError details:\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
}

echo "\nDone!\n";