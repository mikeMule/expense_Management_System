<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Employee.php';

session_start();

$auth = new Auth();
$auth->requireLogin();

$employee = new Employee();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    try {
        $result = $employee->deleteSalaryPayment($id);
        if ($result) {
            $_SESSION['success'] = 'Salary record deleted successfully.';
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
