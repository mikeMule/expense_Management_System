<?php
require_once '../config/database.php';
require_once '../classes/Auth.php';
require_once '../classes/Transaction.php';

session_start();
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    $response['message'] = 'Authentication required. Please log in.';
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = trim($_POST['type'] ?? '');
    $category_id = trim($_POST['category_id'] ?? '');
    $amount = trim($_POST['amount'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $transaction_date = trim($_POST['transaction_date'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $attachment_path = null;

    // Handle file upload
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['attachment'];
        $upload_dir = '../uploads/';
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowed_types)) {
            $response['message'] = 'Invalid file type. Only JPG, PNG, GIF, and PDF are allowed.';
        } elseif ($file['size'] > $max_size) {
            $response['message'] = 'File is too large. Maximum size is 5MB.';
        } else {
            // Generate a unique filename to prevent overwriting
            $filename = uniqid('tx_', true) . '_' . basename($file['name']);
            $destination = $upload_dir . $filename;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $attachment_path = 'uploads/' . $filename;
            } else {
                $response['message'] = 'Failed to upload file.';
            }
        }
    }

    // If there was a file upload error, and it wasn't just "no file", stop
    if (isset($file) && $file['error'] !== UPLOAD_ERR_NO_FILE && $attachment_path === null) {
        echo json_encode($response);
        exit();
    }

    // Validation
    if (empty($type) || empty($amount) || empty($description) || empty($transaction_date)) {
        $response['message'] = 'Please fill in all required fields.';
    } elseif (!in_array($type, ['income', 'expense'])) {
        $response['message'] = 'Invalid transaction type.';
    } elseif (!is_numeric($amount) || $amount <= 0) {
        $response['message'] = 'Please enter a valid amount greater than 0.';
    } elseif (!strtotime($transaction_date)) {
        $response['message'] = 'Please enter a valid date.';
    } else {
        try {
            $transaction = new Transaction();
            $newTransactionId = $transaction->addTransaction($type, $category_id ?: null, $amount, $description, $transaction_date, $notes, $attachment_path);
            if ($newTransactionId) {
                $response['success'] = true;
                $response['message'] = 'Transaction added successfully!';
                $response['transaction_id'] = $newTransactionId;
            } else {
                $response['message'] = 'Failed to add transaction. Please try again.';
            }
        } catch (Exception $e) {
            error_log('Add Transaction Error: ' . $e->getMessage());
            $response['message'] = 'An error occurred while adding the transaction.';
        }
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
exit();
