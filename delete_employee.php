<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Employee.php';

session_start();
$auth = new Auth();
$auth->requireLogin();

$employee = new Employee();

// Get employee ID
$employee_id = $_GET['id'] ?? 0;

if (!$employee_id) {
    $_SESSION['error'] = 'Invalid employee ID.';
    header('Location: employees.php');
    exit();
}

// Get employee details for confirmation
$employee_data = $employee->getEmployeeById($employee_id);

if (!$employee_data) {
    $_SESSION['error'] = 'Employee not found.';
    header('Location: employees.php');
    exit();
}

// Handle deletion confirmation
if ($_POST && isset($_POST['confirm_delete'])) {
    try {
        if ($employee->deleteEmployee($employee_id)) {
            $_SESSION['success'] = 'Employee deleted successfully.';
        } else {
            $_SESSION['error'] = 'Failed to delete employee.';
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'An error occurred: ' . $e->getMessage();
    }
    
    header('Location: employees.php');
    exit();
}

$page_title = 'Delete Employee';
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>Delete Employee
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-warning me-2"></i>
                        <strong>Warning!</strong> This action will permanently delete the employee and all associated salary records. This cannot be undone.
                    </div>
                    
                    <p>Are you sure you want to delete the following employee?</p>
                    
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-4"><strong>Employee ID:</strong></div>
                                <div class="col-sm-8"><?php echo htmlspecialchars($employee_data['employee_id']); ?></div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-4"><strong>Name:</strong></div>
                                <div class="col-sm-8"><?php echo htmlspecialchars($employee_data['first_name'] . ' ' . $employee_data['last_name']); ?></div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-4"><strong>Position:</strong></div>
                                <div class="col-sm-8"><?php echo htmlspecialchars($employee_data['position']); ?></div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-4"><strong>Monthly Salary:</strong></div>
                                <div class="col-sm-8">$<?php echo number_format($employee_data['monthly_salary'], 2); ?></div>
                            </div>
                            <?php if ($employee_data['email']): ?>
                            <hr>
                            <div class="row">
                                <div class="col-sm-4"><strong>Email:</strong></div>
                                <div class="col-sm-8"><?php echo htmlspecialchars($employee_data['email']); ?></div>
                            </div>
                            <?php endif; ?>
                            <hr>
                            <div class="row">
                                <div class="col-sm-4"><strong>Status:</strong></div>
                                <div class="col-sm-8">
                                    <span class="badge bg-<?php echo $employee_data['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($employee_data['status']); ?>
                                    </span>
                                </div>
                            </div>
                            <?php if ($employee_data['hire_date']): ?>
                            <hr>
                            <div class="row">
                                <div class="col-sm-4"><strong>Hire Date:</strong></div>
                                <div class="col-sm-8"><?php echo date('M d, Y', strtotime($employee_data['hire_date'])); ?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <form method="POST">
                        <div class="d-flex justify-content-between">
                            <a href="employees.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Cancel
                            </a>
                            <button type="submit" name="confirm_delete" class="btn btn-danger">
                                <i class="fas fa-trash me-1"></i>Yes, Delete Employee
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>