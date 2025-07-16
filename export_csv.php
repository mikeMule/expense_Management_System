<?php
// export_csv.php: Export employee report as CSV
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Employee.php';

session_start();
$auth = new Auth();
$auth->requireLogin();

$employee = new Employee();
$employees = $employee->getAllEmployees();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=employee_report_' . date('Ymd_His') . '.csv');

$output = fopen('php://output', 'w');
// CSV header
fputcsv($output, ['Employee ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Position', 'Monthly Salary', 'Hire Date', 'Registration Date']);

foreach ($employees as $emp) {
    fputcsv($output, [
        $emp['employee_id'] ?? '',
        $emp['first_name'] ?? '',
        $emp['last_name'] ?? '',
        $emp['email'] ?? '',
        $emp['phone'] ?? '',
        $emp['position'] ?? '',
        $emp['monthly_salary'] ?? '',
        $emp['hire_date'] ?? '',
        isset($emp['created_at']) ? date('Y-m-d', strtotime($emp['created_at'])) : ''
    ]);
}
fclose($output);
exit;
