<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Employee.php';

$page_title = 'Add Employee';
include 'includes/header.php';

$auth = new Auth();
$auth->requireLogin();

$employee = new Employee();
$error = '';
$success = '';

// Check if we've reached the 10 employee limit
if (count($employee->getAllEmployees()) >= 10) {
    $_SESSION['error'] = 'Maximum 10 employees allowed.';
    header('Location: employees.php');
    exit();
}

// Handle form submission
if ($_POST) {
    $employee_id = trim($_POST['employee_id'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $monthly_salary = trim($_POST['monthly_salary'] ?? '');
    $hire_date = trim($_POST['hire_date'] ?? '');
    $attachment_path = null;

    // File upload handling
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['attachment'];
        $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $allowed_extensions = ['pdf', 'doc', 'docx'];

        // Get file extension
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Validate file type
        if (!in_array($file['type'], $allowed_types) || !in_array($file_extension, $allowed_extensions)) {
            $error = 'Please upload only PDF or DOC/DOCX files.';
        } elseif ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
            $error = 'File size must be less than 5MB.';
        } else {
            // Create uploads directory if it doesn't exist
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            // Generate unique filename
            $filename = 'emp_' . uniqid() . '_' . time() . '.' . $file_extension;
            $filepath = $upload_dir . $filename;

            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $attachment_path = $filepath;
            } else {
                $error = 'Failed to upload file. Please try again.';
            }
        }
    }

    // Validation
    if (empty($employee_id) || empty($first_name) || empty($last_name) || empty($position) || empty($monthly_salary)) {
        $error = 'Please fill in all required fields.';
    } elseif (!is_numeric($monthly_salary) || $monthly_salary <= 0) {
        $error = 'Please enter a valid monthly salary greater than 0.';
    } elseif ($hire_date && !strtotime($hire_date)) {
        $error = 'Please enter a valid hire date.';
    } elseif ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Add employee
        try {
            if ($employee->addEmployee($employee_id, $first_name, $last_name, $email, $phone, $position, $monthly_salary, $hire_date, $attachment_path)) {
                $_SESSION['success'] = 'Employee added successfully!';
                header('Location: employees.php');
                exit();
            } else {
                $error = 'Failed to add employee. Employee ID may already exist.';
            }
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                $error = 'Employee ID already exists. Please choose a different ID.';
            } else {
                $error = 'An error occurred: ' . $e->getMessage();
            }
        }
    }
}

include 'includes/navbar.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-user-plus me-2"></i>Add New Employee
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

                    <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="employee_id" class="form-label">
                                    <i class="fas fa-id-badge me-1"></i>Employee ID *
                                </label>
                                <input type="text" class="form-control" id="employee_id" name="employee_id"
                                    value="<?php echo htmlspecialchars($employee_id ?? ''); ?>"
                                    placeholder="e.g., EMP001" maxlength="20" required>
                                <div class="invalid-feedback">
                                    Please enter an employee ID.
                                </div>
                                <small class="form-text text-muted">Unique identifier for the employee</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="position" class="form-label">
                                    <i class="fas fa-briefcase me-1"></i>Position *
                                </label>
                                <input type="text" class="form-control" id="position" name="position"
                                    value="<?php echo htmlspecialchars($position ?? ''); ?>"
                                    placeholder="e.g., Software Developer" maxlength="100" required>
                                <div class="invalid-feedback">
                                    Please enter the employee's position.
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">
                                    <i class="fas fa-user me-1"></i>First Name *
                                </label>
                                <input type="text" class="form-control" id="first_name" name="first_name"
                                    value="<?php echo htmlspecialchars($first_name ?? ''); ?>"
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
                                    value="<?php echo htmlspecialchars($last_name ?? ''); ?>"
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
                                    value="<?php echo htmlspecialchars($email ?? ''); ?>"
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
                                    value="<?php echo htmlspecialchars($phone ?? ''); ?>"
                                    placeholder="+1 (555) 123-4567" maxlength="20">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="monthly_salary" class="form-label">
                                    <i class="fas fa-dollar-sign me-1"></i>Monthly Salary *
                                </label>
                                <input type="number" class="form-control" id="monthly_salary" name="monthly_salary"
                                    value="<?php echo htmlspecialchars($monthly_salary ?? ''); ?>"
                                    step="0.01" min="0.01" placeholder="5000.00" data-currency required>
                                <div class="invalid-feedback">
                                    Please enter a valid monthly salary.
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="hire_date" class="form-label">
                                    <i class="fas fa-calendar me-1"></i>Hire Date
                                </label>
                                <input type="date" class="form-control" id="hire_date" name="hire_date"
                                    value="<?php echo htmlspecialchars($hire_date ?? date('Y-m-d')); ?>"
                                    max="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="attachment" class="form-label">
                                    <i class="fas fa-paperclip me-1"></i>Attachment (PDF/DOC/DOCX)
                                </label>
                                <input type="file" class="form-control" id="attachment" name="attachment"
                                    accept=".pdf,.doc,.docx"
                                    onchange="validateFile(this)">
                                <div class="invalid-feedback">
                                    Please select a valid PDF or DOC file.
                                </div>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Maximum file size: 5MB. Supported formats: PDF, DOC, DOCX
                                </small>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note:</strong> New employees will be set as active by default. You can change their status later if needed.
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="employees.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Back to Employees
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Add Employee
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function validateFile(input) {
    const file = input.files[0];
    const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    const allowedExtensions = ['pdf', 'doc', 'docx'];
    const maxSize = 5 * 1024 * 1024; // 5MB
    
    if (file) {
        // Check file size
        if (file.size > maxSize) {
            alert('File size must be less than 5MB.');
            input.value = '';
            return false;
        }
        
        // Check file type
        const fileExtension = file.name.split('.').pop().toLowerCase();
        if (!allowedExtensions.includes(fileExtension) || !allowedTypes.includes(file.type)) {
            alert('Please select only PDF or DOC/DOCX files.');
            input.value = '';
            return false;
        }
    }
    
    return true;
}

// Form validation
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();
</script>

<?php include 'includes/footer.php'; ?>