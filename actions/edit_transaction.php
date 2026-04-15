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
    $transaction_id = isset($_POST['transaction_id']) ? intval($_POST['transaction_id']) : 0;
    $type = trim($_POST['type'] ?? '');
    $category_id = trim($_POST['category_id'] ?? '');
    $amount = trim($_POST['amount'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $transaction_date = trim($_POST['transaction_date'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $remove_attachment = isset($_POST['remove_attachment']) ? intval($_POST['remove_attachment']) : 0;

    // Validation
    if (empty($transaction_id) || empty($type) || empty($amount) || empty($description) || empty($transaction_date)) {
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
            
            // 1. Fetch current transaction details
            $current_tx = $transaction->getTransactionById($transaction_id);
            if (!$current_tx) {
                $response['message'] = 'Transaction not found.';
                echo json_encode($response);
                exit();
            }

            // 2. Initialize attachment path with existing value
            $attachment_path = $current_tx['attachment_path'];
            $upload_dir = '../uploads/';
            
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            // 3. Logic for replacement/removal
            if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                // User is uploading a NEW file
                $file = $_FILES['attachment'];
                $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                $allowed_extensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif'];

                $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                if (!in_array($file['type'], $allowed_types) || !in_array($file_extension, $allowed_extensions)) {
                    $response['message'] = 'Invalid file type. Please upload only PDF, DOC/DOCX, or Image files.';
                    echo json_encode($response);
                    exit();
                }

                if ($file['size'] > 5 * 1024 * 1024) {
                    $response['message'] = 'File size must be less than 5MB.';
                    echo json_encode($response);
                    exit();
                }

                $filename = 'tx_' . date('YmdHis') . '_' . uniqid() . '.' . $file_extension;
                $destination = $upload_dir . $filename;

                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    // Delete old physical file if it exists
                    if ($current_tx['attachment_path']) {
                        $old_file_path = '../' . $current_tx['attachment_path'];
                        if (file_exists($old_file_path)) {
                            unlink($old_file_path);
                        }
                    }
                    // Set new path for database
                    $attachment_path = 'uploads/' . $filename;
                } else {
                    $response['message'] = 'Failed to save the uploaded file.';
                    echo json_encode($response);
                    exit();
                }
            } elseif ($remove_attachment) {
                // User checked "remove attachment"
                if ($current_tx['attachment_path']) {
                    $old_file_path = '../' . $current_tx['attachment_path'];
                    if (file_exists($old_file_path)) {
                        unlink($old_file_path);
                    }
                }
                $attachment_path = null;
            }

            // 4. Update transaction with the final attachment path
            if ($transaction->updateTransactionWithAttachment($transaction_id, $type, $category_id ?: null, $amount, $description, $transaction_date, $notes, $attachment_path)) {
                $response['success'] = true;
                $response['message'] = 'Transaction updated successfully!';
                $response['transaction_id'] = $transaction_id;
            } else {
                $response['message'] = 'Failed to update transaction in database.';
            }
        } catch (Exception $e) {
            error_log('Update Transaction Error: ' . $e->getMessage());
            $response['message'] = 'An error occurred while updating the transaction.';
        }
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
exit();
