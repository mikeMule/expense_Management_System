<?php
require_once '../config/database.php';
require_once '../classes/Auth.php';
require_once '../classes/Employee.php';

session_start();
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

try {
    $auth = new Auth();
    if (!$auth->isLoggedIn()) {
        throw new Exception('You are not logged in.');
    }

    $employee = new Employee();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $position = trim($_POST['position'] ?? '');
        $monthly_salary = floatval($_POST['monthly_salary'] ?? 0);
        $hire_date = trim($_POST['hire_date'] ?? date('Y-m-d'));

        if (empty($first_name) || empty($last_name) || empty($email) || empty($position) || $monthly_salary <= 0 || empty($hire_date)) {
            throw new Exception('Please fill all required fields correctly.');
        }

        $max_attempts = 5;
        $attempt = 0;
        $new_employee = null;

        while ($attempt < $max_attempts) {
            $employee_id = 'MW-' . random_int(100000, 999999);
            $result = $employee->addEmployee($employee_id, $first_name, $last_name, $email, $phone, $position, $monthly_salary, $hire_date);

            if ($result) {
                // Fetch the newly created employee to return it
                $db = new Database();
                $db->query('SELECT * FROM employees WHERE employee_id = :employee_id');
                $db->bind(':employee_id', $employee_id);
                $new_employee = $db->single();

                $response['success'] = true;
                $response['message'] = 'Employee added successfully!';
                $response['employee'] = $new_employee;
                break;
            }
            $attempt++;
        }

        if (!$new_employee) {
            throw new Exception('Failed to add employee after multiple attempts. Please try again.');
        }
    } else {
        throw new Exception('Invalid request method.');
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log('Add Employee Error: ' . $e->getMessage());
}

echo json_encode($response);
exit();
