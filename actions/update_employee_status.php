<?php
require_once '../config/database.php';
require_once '../classes/Auth.php';
require_once '../classes/Employee.php';

session_start();
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'An error occurred.'];

try {
    $auth = new Auth();
    if (!$auth->isLoggedIn()) {
        throw new Exception('User not logged in.');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    $employee_id = isset($_POST['employee_id']) ? intval($_POST['employee_id']) : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : '';

    if ($employee_id <= 0 || !in_array($status, ['active', 'inactive'])) {
        throw new Exception('Invalid input data.');
    }

    $employee = new Employee();
    $result = $employee->updateEmployeeStatus($employee_id, $status);

    if ($result) {
        $response['success'] = true;
        $response['message'] = 'Employee status updated successfully.';
    } else {
        throw new Exception('Failed to update employee status.');
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log('Update Employee Status Error: ' . $e->getMessage());
}

echo json_encode($response);
exit();
