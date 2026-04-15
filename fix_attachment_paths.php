<?php
/**
 * Direct fix for attachment paths - update database with correct file paths
 */

require_once 'config/database.php';

// Connect to database
$db = new Database;
$db->connect();

// Get all transactions with attachments
$query = "SELECT id, attachment_path FROM transactions WHERE attachment_path IS NOT NULL AND attachment_path != ''";
$db->query($query);
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
        // Try to find the file with similar name
        $files = glob($upload_dir . '*');
        $found = false;

        foreach ($files as $file) {
            $basename = basename($file);
            // Check if this might be the same file (similar name pattern)
            if (strpos($basename, 'tx_') === 0 && strpos($filename, 'tx_') === 0) {
                // Possible match - update the path
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
        // File exists, check if path matches
        if ($old_path != 'uploads/' . $filename) {
            $new_path = 'uploads/' . $filename;
            $update_query = "UPDATE transactions SET attachment_path = '$new_path' WHERE id = {$t['id']}";
            $db->query($update_query);
            $fixed_count++;
            echo "FIXED: Transaction {$t['id']}: Path normalized\n";
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