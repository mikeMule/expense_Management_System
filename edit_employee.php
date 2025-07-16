<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Employee.php';

$page_title = 'Edit Employee';
include 'includes/header.php';

$auth = new Auth();
$auth->requireLogin();

$employee = new Employee();
$error = '';
$success = '';

// Get employee ID
$employee_id = $_GET['id'] ?? 0;

if (!$employee_id) {
    header('Location: employees.php');
    exit();
}

// Get employee details
$employee_data = $employee->getEmployeeById($employee_id);

if (!$employee_data) {
    $_SESSION['error'] = 'Employee not found.';
    header('Location: employees.php');
    exit();
}

// Handle form submission
if ($_POST) {
    $emp_id = trim($_POST['employee_id'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $monthly_salary = trim($_POST['monthly_salary'] ?? '');
    $hire_date = trim($_POST['hire_date'] ?? '');
    $status = trim($_POST['status'] ?? '');
    
    // Validation
    if (empty($emp_id) || empty($first_name) || empty($last_name) || empty($position) || empty($monthly_salary) || empty($status)) {
        $error = 'Please fill in all required fields.';
    } elseif (!is_numeric($monthly_salary) || $monthly_salary <= 0) {
        $error = 'Please enter a valid monthly salary greater than 0.';
    } elseif ($hire_date && !strtotime($hire_date)) {
        $error = 'Please enter a valid hire date.';
    } elseif ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (!in_array($status, ['active', 'inactive'])) {
        $error = 'Invalid status selected.';
    } else {
        // Update employee
        try {
            if ($employee->updateEmployee($employee_id, $emp_id, $first_name, $last_name, $email, $phone, $position, $monthly_salary, $hire_date, $status)) {
                $success = 'Employee updated successfully!';
                // Refresh employee data
                $employee_data = $employee->getEmployeeById($employee_id);
            } else {
                $error = 'Failed to update employee. Employee ID may already exist.';
            }
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                $error = 'Employee ID already exists. Please choose a different ID.';
            } else {
                $error = 'An error occurred: ' . $e->getMessage();
            }
        }
    }
} else {
    // Pre-populate form with existing data
    $emp_id = $employee_data['employee_id'];
    $first_name = $employee_data['first_name'];
    $last_name = $employee_data['last_name'];
    $email = $employee_data['email'];
    $phone = $employee_data['phone'];
    $position = $employee_data['position'];
    $monthly_salary = $employee_data['monthly_salary'];
    $hire_date = $employee_data['hire_date'];
    $status = $employee_data['status'];
}

include 'includes/navbar.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-user-edit me-2"></i>Edit Employee
                    </h4>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="employee_id" class="form-label">
                                    <i class="fas fa-id-badge me-1"></i>Employee ID *
                                </label>
                                <input type="text" class="form-control" id="employee_id" name="employee_id" 
                                       value="<?php echo htmlspecialchars($emp_id); ?>" 
                                       placeholder="e.g., EMP001" maxlength="20" required>
                                <div class="invalid-feedback">
                                    Please enter an employee ID.
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">
                                    <i class="fas fa-toggle-on me-1"></i>Status *
                                </label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="active" <?php echo $status == 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo $status == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                                <div class="invalid-feedback">
                                    Please select a status.
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">
                                    <i class="fas fa-user me-1"></i>First Name *
                                </label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?php echo htmlspecialchars($first_name); ?>" 
                                       placeholder="John" maxlength="50" required>
                                <div class="invalid-feedback">
                                    Please enter the first name.
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">
                                    <i class="fas fa-user me-1"></i>Last Name *
                                </label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?php echo htmlspecialchars($last_name); ?>" 
                                       placeholder="Doe" maxlength="50" required>
                                <div class="invalid-feedback">
                                    Please enter the last name.
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-1"></i>Email
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($email); ?>" 
                                       placeholder="john.doe@company.com" maxlength="100">
                                <div class="invalid-feedback">
                                    Please enter a valid email address.
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">
                                    <i class="fas fa-phone me-1"></i>Phone
                                </label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($phone); ?>" 
                                       placeholder="+1 (555) 123-4567" maxlength="20">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="position" class="form-label">
                                    <i class="fas fa-briefcase me-1"></i>Position *
                                </label>
                                <input type="text" class="form-control" id="position" name="position" 
                                       value="<?php echo htmlspecialchars($position); ?>" 
                                       placeholder="e.g., Software Developer" maxlength="100" required>
                                <div class="invalid-feedback">
                                    Please enter the employee's position.
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="monthly_salary" class="form-label">
                                    <i class="fas fa-dollar-sign me-1"></i>Monthly Salary *
                                </label>
                                <input type="number" class="form-control" id="monthly_salary" name="monthly_salary" 
                                       value="<?php echo htmlspecialchars($monthly_salary); ?>" 
                                       step="0.01" min="0.01" placeholder="5000.00" data-currency required>
                                <div class="invalid-feedback">
                                    Please enter a valid monthly salary.
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="hire_date" class="form-label">
                                    <i class="fas fa-calendar me-1"></i>Hire Date
                                </label>
                                <input type="date" class="form-control" id="hire_date" name="hire_date" 
                                       value="<?php echo htmlspecialchars($hire_date); ?>" 
                                       max="<?php echo date('Y-m-d'); ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Created: <?php echo date('M d, Y \\a\\t g:i A', strtotime($employee_data['created_at'])); ?>
                                </small>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="employees.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Back to Employees
                            </a>
                            <div>
                                <a href="delete_employee.php?id=<?php echo $employee_id; ?>" 
                                   class="btn btn-danger me-2 btn-delete" 
                                   data-item="employee '<?php echo htmlspecialchars($first_name . ' ' . $last_name); ?>'">
                                    <i class="fas fa-trash me-1"></i>Delete
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Update Employee
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>