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

    $employee_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($employee_id <= 0) {
        throw new Exception('Invalid employee ID.');
    }

    $employee = new Employee();
    $employee_details = $employee->getEmployeeById($employee_id);

    if (!$employee_details) {
        throw new Exception('Employee not found.');
    }

    // Custom query to get salary payments for this specific employee
    $db = new Database();
    $db->query('SELECT * FROM salary_payments WHERE employee_id = :employee_id ORDER BY year DESC, month DESC');
    $db->bind(':employee_id', $employee_id);
    $salary_history = $db->resultset();

    $response['success'] = true;
    $response['employee'] = $employee_details;
    $response['salaries'] = $salary_history;

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log('Get Employee Details Error: ' . $e->getMessage());
}

echo json_encode($response);
exit(); 