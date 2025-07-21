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
            if ($transaction->updateTransaction($transaction_id, $type, $category_id ?: null, $amount, $description, $transaction_date, $notes)) {
                $response['success'] = true;
                $response['message'] = 'Transaction updated successfully!';
                $response['transaction_id'] = $transaction_id;
            } else {
                $response['message'] = 'Failed to update transaction. Please try again.';
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
