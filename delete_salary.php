<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Employee.php';
require_once 'classes/Transaction.php'; // Ensure Transaction class is included

session_start();

$auth = new Auth();
$auth->requireLogin();

$employee = new Employee();
$transaction = new Transaction(); // Create a new transaction object

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    try {
        // Find and delete the corresponding transaction first
        $all_transactions = $transaction->getAllTransactions(null, 0, true); // Get all transactions, including salaries
        $transaction_to_delete = null;

        foreach ($all_transactions as $tx) {
            if (strpos($tx['notes'], "[salary_payment_id:{$id}]") !== false) {
                $transaction_to_delete = $tx;
                break;
            }
        }

        if ($transaction_to_delete) {
            $transaction->deleteTransaction($transaction_to_delete['id']);
        }

        // Now, delete the salary payment record
        $result = $employee->deleteSalaryPayment($id);

        if ($result) {
            $_SESSION['success'] = 'Salary record and corresponding transaction deleted successfully.';
        } else {
            $_SESSION['error'] = 'Failed to delete salary record.';
        }
    } catch (Exception $e) {
        // Log error, do not display to user
        error_log(date('[Y-m-d H:i:s] ') . 'Delete Salary Error: ' . $e->getMessage() . "\n", 3, __DIR__ . '/logs/pdo_errors.log');
        $_SESSION['error'] = 'An error occurred while deleting the salary record.';
    }
} else {
    $_SESSION['error'] = 'Invalid salary ID.';
}

header('Location: salaries.php');
exit();
