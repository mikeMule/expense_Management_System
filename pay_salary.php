<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Employee.php';
require_once 'classes/Transaction.php';

session_start();
$auth = new Auth();
$auth->requireLogin();

$employee = new Employee();
$transaction = new Transaction();

$payment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($payment_id > 0) {
    try {
        // Mark salary as paid first
        $result = $employee->markSalaryAsPaid($payment_id);

        if ($result) {
            // Get the details of the salary payment we just made
            $salary_payment = $employee->getSalaryPaymentById($payment_id);

            if ($salary_payment) {
                // Create a corresponding transaction
                $description = "Salary: " . $salary_payment['first_name'] . " " . $salary_payment['last_name'];
                $notes = "Payment for " . date('F Y', mktime(0, 0, 0, $salary_payment['month'], 1, $salary_payment['year']));
                $transaction_date = date('Y-m-d', strtotime($salary_payment['payment_date']));

                // The new 'Salaries' category will have ID 11
                $transaction->addTransaction(
                    'expense',
                    11,
                    $salary_payment['amount'],
                    $description,
                    $transaction_date,
                    $notes
                );

                $_SESSION['success'] = 'Salary paid and transaction recorded successfully.';
            } else {
                $_SESSION['error'] = 'Could not retrieve salary details to create transaction.';
            }
        } else {
            $_SESSION['error'] = 'Failed to mark salary as paid.';
        }
    } catch (Exception $e) {
        // Log the error and set a generic error message
        error_log("Salary payment error: " . $e->getMessage());
        $_SESSION['error'] = 'An unexpected error occurred during salary payment.';
    }
} else {
    $_SESSION['error'] = 'Invalid salary payment ID provided.';
}

header('Location: salaries.php');
exit();
