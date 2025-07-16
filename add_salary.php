<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Employee.php';

session_start();

$auth = new Auth();
$auth->requireLogin();

$employee = new Employee();
$error = '';
$success = '';

// Get all employees for dropdown (must be before form handling)
$employees = $employee->getAllEmployees();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the numeric employee id from the employee_id (string) selected in the form
    $employee_id_str = trim($_POST['employee_id'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);
    $month = intval($_POST['month'] ?? 0);
    $year = intval($_POST['year'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');

    // Find the numeric id for the given employee_id string
    $numeric_employee_id = null;
    foreach ($employees as $emp) {
        if ($emp['employee_id'] === $employee_id_str) {
            $numeric_employee_id = $emp['id'];
            break;
        }
    }

    if (!$numeric_employee_id || $amount <= 0 || $month < 1 || $month > 12 || $year < 2020 || $year > date('Y')) {
        $error = 'Please fill all fields correctly.';
    } else {
        $result = $employee->addSalaryPayment($numeric_employee_id, $month, $year, $amount, $notes);
        if ($result) {
            // Store submitted salary info in session for display
            $_SESSION['success'] = 'Salary record added successfully.';
            $_SESSION['submitted_salary'] = [
                'employee' => array_values(array_filter($employees, function ($emp) use ($numeric_employee_id) {
                    return $emp['id'] == $numeric_employee_id;
                }))[0] ?? null,
                'amount' => $amount,
                'month' => $month,
                'year' => $year,
                'notes' => $notes
            ];
            header('Location: salaries.php');
            exit();
        } else {
            $error = 'Failed to add salary record. It may already exist for this period.';
        }
    }
}
$page_title = 'Add Salary Information';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title . ' - ' . APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>

<body class="bg-light">
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/navbar.php'; ?>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-7 col-xl-6">
                <div class="card shadow-lg border-0 rounded-4">
                    <div class="card-header bg-gradient bg-primary text-white rounded-top-4">
                        <h4 class="mb-0 fw-bold"><i class="fas fa-plus-circle me-2"></i>Add Salary Information</h4>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger d-flex align-items-center gap-2"><i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success d-flex align-items-center gap-2"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
                        <?php endif; ?>
                        <form method="POST" class="needs-validation" novalidate autocomplete="off">
                            <div class="mb-3">
                                <label for="employee_id" class="form-label fw-semibold">Employee</label>
                                <select class="form-select form-select-lg" id="employee_id" name="employee_id" required>
                                    <option value="">Select Employee</option>
                                    <?php foreach ($employees as $emp): ?>
                                        <option value="<?php echo htmlspecialchars($emp['employee_id']); ?>"><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name'] . ' (' . $emp['employee_id'] . ')'); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Please select an employee.</div>
                            </div>
                            <div class="mb-3">
                                <label for="amount" class="form-label fw-semibold">Amount</label>
                                <input type="number" step="0.01" class="form-control form-control-lg" id="amount" name="amount" required placeholder="Enter amount">
                                <div class="invalid-feedback">Please enter a valid amount.</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="month" class="form-label fw-semibold">Month</label>
                                    <select class="form-select form-select-lg" id="month" name="month" required>
                                        <option value="">Select Month</option>
                                        <?php for ($m = 1; $m <= 12; $m++): ?>
                                            <option value="<?php echo $m; ?>"><?php echo date('F', mktime(0, 0, 0, $m, 1)); ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    <div class="invalid-feedback">Please select a month.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="year" class="form-label fw-semibold">Year</label>
                                    <select class="form-select form-select-lg" id="year" name="year" required>
                                        <option value="">Select Year</option>
                                        <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                            <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    <div class="invalid-feedback">Please select a year.</div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="notes" class="form-label fw-semibold">Notes (optional)</label>
                                <textarea class="form-control form-control-lg" id="notes" name="notes" rows="2" placeholder="Add any notes..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm"><i class="fas fa-save me-2"></i>Add Salary</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        body.bg-light {
            background: #f5f6fa !important;
        }

        .card {
            border-radius: 1.5rem !important;
        }

        .card-header.bg-gradient {
            background: linear-gradient(90deg, #1976d2 0%, #42a5f5 100%) !important;
        }

        .form-label {
            color: #1976d2;
        }

        .btn-primary {
            background: linear-gradient(90deg, #1976d2 0%, #42a5f5 100%) !important;
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(90deg, #1565c0 0%, #1e88e5 100%) !important;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #1976d2;
            box-shadow: 0 0 0 0.2rem rgba(25, 118, 210, 0.15);
        }

        .card {
            box-shadow: 0 4px 24px rgba(25, 118, 210, 0.08) !important;
        }

        .alert {
            font-size: 1.05rem;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (() => {
            'use strict';
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
    <?php include 'includes/footer.php'; ?>
</body>

</html>