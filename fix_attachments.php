#!/usr/bin/env php
<?php
/**
 * Script to fix mismatched attachment paths in the database
 * This script will:
 * 1. Scan the uploads directory for actual files
 * 2. Update the database to match actual file names
 */

require_once 'config/database.php';
require_once 'classes/Transaction.php';

// Configuration
$upload_dir = __DIR__ . '/uploads/';
$base_url = 'uploads/';

// Connect to database
$db = new Database;
$db->connect();

// Get all transactions
$transaction = new Transaction();
$transactions = $transaction->getAllTransactions();

// Scan uploads directory for actual files
$actual_files = [];
if (is_dir($upload_dir)) {
    if ($handle = opendir($upload_dir)) {
        while (false !== ($file = readdir($handle))) {
            if ($file != '.' && $file != '..' && is_file($upload_dir . $file)) {
                $actual_files[$file] = $file;
            }
        }
        closedir($handle);
    }
}

echo "Found " . count($actual_files) . " files in uploads directory\n";
echo "Found " . count($transactions) . " transactions in database\n\n";

$updated_count = 0;
$errors = [];

foreach ($transactions as $t) {
    if (!empty($t['attachment_path'])) {
        $filename = basename($t['attachment_path']);

        // Check if the file exists in uploads directory
        if (!file_exists($upload_dir . $filename)) {
            // File doesn't exist - need to fix
            if (isset($actual_files[$filename])) {
                // File exists with same name, no action needed
                continue;
            }

            // Try to find the file with different name
            $found = false;
            foreach ($actual_files as $actual_file) {
                // Check if this might be the same file (same content, different name)
                // For now, we'll just report the mismatch
                echo "MISMATCH: Transaction {$t['id']} has attachment '{$filename}' but actual file is '{$actual_file}'\n";

                // Ask user if they want to update
                echo "Would you like to update the path? (y/n): ";
                $handle = fopen("php://stdin", "r");
                $line = fgets($handle);
                if (trim($line) === 'y') {
                    // Update database with actual file
                    $new_path = $base_url . $actual_file;
                    $update_query = "UPDATE transactions SET attachment_path = '$new_path' WHERE id = {$t['id']}";
                    $db->query($update_query);
                    $updated_count++;
                    echo "Updated transaction {$t['id']}: {$filename} -> {$actual_file}\n";
                    $found = true;
                }
                fclose($handle);
                break;
            }

            if (!$found) {
                echo "ERROR: Transaction {$t['id']} references non-existent file '{$filename}'\n";
                $errors[] = $t['id'];
            }
        }
    }
}

echo "\n=== Summary ===\n";
echo "Updated: $updated_count transactions\n";
echo "Errors: " . count($errors) . " transactions\n";

if (!empty($errors)) {
    echo "\nTransactions with errors:\n";
    foreach ($errors as $id) {
        echo "  - Transaction ID: $id\n";
    }
}

echo "\nDone!\n";