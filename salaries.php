<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Employee.php';

$page_title = 'Salary Management';
include 'includes/header.php';

$auth = new Auth();
$auth->requireLogin();

$employee = new Employee();

// Always define modal error/success variables
$salary_error = '';
$salary_success = '';

// Handle success/error messages

$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
$submitted_salary = $_SESSION['submitted_salary'] ?? null;
unset($_SESSION['success'], $_SESSION['error'], $_SESSION['submitted_salary']);

// Handle add salary modal POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_salary'])) {
    $employee_id_str = trim($_POST['employee_id'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);
    $month = intval($_POST['month'] ?? 0);
    $year = intval($_POST['year'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');

    // Find the numeric id for the given employee_id string
    $all_employees = $employee->getAllEmployees();
    $numeric_employee_id = null;
    foreach ($all_employees as $emp) {
        if ($emp['employee_id'] === $employee_id_str) {
            $numeric_employee_id = $emp['id'];
            break;
        }
    }

    // Robust validation
    if (!$numeric_employee_id) {
        $salary_error = 'Please select a valid employee.';
    } elseif ($amount <= 0) {
        $salary_error = 'Please enter a valid amount.';
    } elseif ($month < 1 || $month > 12) {
        $salary_error = 'Please select a valid month.';
    } elseif ($year < 2020 || $year > date('Y')) {
        $salary_error = 'Please select a valid year.';
    } else {
        $result = $employee->addSalaryPayment($numeric_employee_id, $month, $year, $amount, $notes);
        if ($result) {
            $_SESSION['success'] = 'Salary record added successfully.';
            $_SESSION['submitted_salary'] = [
                'employee' => array_values(array_filter($all_employees, function ($emp) use ($numeric_employee_id) {
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
            $salary_error = 'Failed to add salary record. It may already exist for this period.';
        }
    }
}

// Handle generate monthly salaries
if ($_POST && isset($_POST['generate_salaries'])) {
    $month = intval($_POST['month']);
    $year = intval($_POST['year']);

    if ($month >= 1 && $month <= 12 && $year >= 2020 && $year <= date('Y')) {
        $generated = $employee->generateMonthlySalaries($month, $year);
        if ($generated > 0) {
            $_SESSION['success'] = "Generated salary records for $generated employees for " . date('F Y', mktime(0, 0, 0, $month, 1, $year)) . ".";
        } else {
            $_SESSION['error'] = 'No new salary records were generated. Records may already exist for this period.';
        }
        header('Location: salaries.php');
        exit();
    } else {
        $error = 'Invalid month or year selected.';
    }
}

// Get filter parameters (allow 'all' for month/year)
$filter_month = isset($_GET['month']) && $_GET['month'] !== 'all' ? $_GET['month'] : null;
$filter_year = isset($_GET['year']) && $_GET['year'] !== 'all' ? $_GET['year'] : null;

// Get salary payments
$salary_payments = $employee->getSalaryPayments($filter_month, $filter_year);
$pending_salaries = $employee->getPendingSalaries();
$total_monthly_budget = $employee->getTotalMonthlySalaries();

include 'includes/navbar.php';
?>

<style>
    .main-info-card {
        background: #fff;
        border-radius: 1.25rem;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
        padding: 2.5rem 2rem;
        margin: 2rem auto;
        max-width: 2619px;
    }

    @media (max-width: 991px) {
        .main-info-card {
            padding: 1.5rem 0.7rem;
            margin: 1.2rem 0.2rem;
        }
    }

    @media (max-width: 600px) {
        .main-info-card {
            padding: 0.7rem 0.2rem;
            margin: 0.5rem 0.1rem;
            border-radius: 0.7rem;
        }
    }
</style>

<div class="page-animate">
    <div class="main-info-card">
        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="fas fa-money-check-alt me-2"></i>Salary Management</h2>
                        <div class="btn-group">
                            <button type="button" class="btn btn-success" id="openAddSalary2025Modal">
                                <i class="fas fa-plus me-1"></i>Add Salary Information
                            </button>
                            <button type="button" class="btn btn-primary" id="openGenerateSalary2025Modal">
                                <i class="fas fa-plus-circle me-1"></i>Generate Monthly Salaries
                            </button>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filters</h5>
                        </div>
                        <div class="card-body">
                            <form method="GET" class="row g-3" id="filterForm">
                                <div class="col-md-3">
                                    <label for="month" class="form-label">Month</label>
                                    <select class="form-select" id="month" name="month">
                                        <option value="all" <?php echo is_null($filter_month) ? 'selected' : ''; ?>>All</option>
                                        <?php for ($m = 1; $m <= 12; $m++): ?>
                                            <option value="<?php echo $m; ?>" <?php echo $filter_month == $m ? 'selected' : ''; ?>><?php echo date('F', mktime(0, 0, 0, $m, 1)); ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="year" class="form-label">Year</label>
                                    <select class="form-select" id="year" name="year">
                                        <option value="all" <?php echo is_null($filter_year) ? 'selected' : ''; ?>>All</option>
                                        <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                            <option value="<?php echo $y; ?>" <?php echo $filter_year == $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="search" class="form-label">Search</label>
                                    <input type="text" class="form-control" id="salarySearchInput" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i>Apply Filters
                                    </button>
                                    <a href="salaries.php" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i>Clear Filters
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Alerts and Statistics (existing code) -->
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($submitted_salary): ?>
                        <div class="alert alert-info alert-dismissible fade show">
                            <strong>Submitted Salary:</strong><br>
                            <ul class="mb-0">
                                <li><strong>Employee:</strong> <?php echo htmlspecialchars($submitted_salary['employee']['first_name'] . ' ' . $submitted_salary['employee']['last_name'] . ' (' . $submitted_salary['employee']['employee_id'] . ')'); ?></li>
                                <li><strong>Amount:</strong> $<?php echo number_format($submitted_salary['amount'], 2); ?></li>
                                <li><strong>Month/Year:</strong> <?php echo date('F', mktime(0, 0, 0, $submitted_salary['month'], 1)); ?> <?php echo $submitted_salary['year']; ?></li>
                                <?php if (!empty($submitted_salary['notes'])): ?><li><strong>Notes:</strong> <?php echo htmlspecialchars($submitted_salary['notes']); ?></li><?php endif; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Salary Statistics -->
                    <div class="row mb-4">
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3 class="stat-value text-info">$<?php echo number_format($total_monthly_budget, 2); ?></h3>
                                            <p class="stat-label">Monthly Budget</p>
                                        </div>
                                        <i class="fas fa-money-check-alt fa-2x text-info"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3 class="stat-value text-warning"><?php echo count($pending_salaries); ?></h3>
                                            <p class="stat-label">Pending Payments</p>
                                        </div>
                                        <i class="fas fa-clock fa-2x text-warning"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3 class="stat-value text-success"><?php echo count(array_filter($salary_payments, function ($s) {
                                                                                    return $s['status'] == 'paid';
                                                                                })); ?></h3>
                                            <p class="stat-label">Paid This Month</p>
                                        </div>
                                        <i class="fas fa-check-circle fa-2x text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3 class="stat-value text-primary"><?php echo count($salary_payments); ?></h3>
                                            <p class="stat-label">Total Records</p>
                                        </div>
                                        <i class="fas fa-list fa-2x text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Salaries Alert -->
                    <?php if (!empty($pending_salaries)): ?>
                        <div class="alert alert-warning">
                            <h5><i class="fas fa-exclamation-triangle me-2"></i>Pending Salary Payments</h5>
                            <p>The following employees have pending salary payments:</p>
                            <div class="row">
                                <?php foreach (array_slice($pending_salaries, 0, 6) as $pending): ?>
                                    <div class="col-md-4 mb-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span><?php echo htmlspecialchars($pending['first_name'] . ' ' . $pending['last_name']); ?></span>
                                            <a href="pay_salary.php?id=<?php echo $pending['id']; ?>" class="btn btn-sm btn-success">
                                                <i class="fas fa-dollar-sign me-1"></i>Pay
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if (count($pending_salaries) > 6): ?>
                                <small class="text-muted">... and <?php echo count($pending_salaries) - 6; ?> more</small>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Salary Table (wrap in card like transactions) -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                Salary Payments (<?php echo count($salary_payments); ?> records)
                            </h5>
                            <div class="btn-group btn-group-sm">
                                <a href="export_csv.php?<?php echo http_build_query($_GET); ?>" class="btn btn-outline-success">
                                    <i class="fas fa-file-csv me-1"></i>Export CSV
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (empty($salary_payments)): ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-4x mb-3"></i>
                                    <h4>No salary payments found</h4>
                                    <p>No salary payments match your current filters.</p>
                                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addSalaryModal">
                                        <i class="fas fa-plus me-1"></i>Add First Salary
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive mt-3">
                                    <input type="text" id="salaryTableSearch" class="form-control mb-2" placeholder="Quick search in table...">
                                    <table class="table table-hover table-striped align-middle" id="salaryTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Employee</th>
                                                <th>Month</th>
                                                <th>Year</th>
                                                <th class="text-end">Amount</th>
                                                <th>Notes</th>
                                                <th>Status</th>
                                                <th class="no-print">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($salary_payments as $s): ?>
                                                <tr>
                                                    <td><strong><?php echo htmlspecialchars(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? '')); ?> (<?php echo htmlspecialchars($s['employee_id'] ?? ''); ?>)</strong></td>
                                                    <td><?php echo date('F', mktime(0, 0, 0, $s['month'], 1)); ?></td>
                                                    <td><?php echo $s['year']; ?></td>
                                                    <td class="text-end">$<?php echo number_format($s['amount'], 2); ?></td>
                                                    <td><?php echo htmlspecialchars($s['notes']); ?></td>
                                                    <td>
                                                        <?php if (($s['status'] ?? '') === 'paid'): ?>
                                                            <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Paid</span>
                                                        <?php else: ?>
                                                            <a href="pay_salary.php?id=<?php echo $s['id']; ?>" class="btn btn-warning btn-sm"><i class="fas fa-dollar-sign me-1"></i>Mark as Paid</a>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="no-print">
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="edit_salary.php?id=<?php echo $s['id']; ?>" class="btn btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                                            <a href="delete_salary.php?id=<?php echo $s['id']; ?>" class="btn btn-outline-danger btn-delete" data-item="salary payment for <?php echo htmlspecialchars(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? '')); ?>" title="Delete"><i class="fas fa-trash"></i></a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Add Salary Modal (2025Modal) -->
                    <button type="button" class="btn btn-success" id="openAddSalary2025Modal">
                        <i class="fas fa-plus me-1"></i>Add Salary Information
                    </button>
                    <div class="modal2025-overlay hide" id="addSalary2025Modal">
                        <div class="modal2025-content">
                            <div class="modal2025-header">
                                <span class="modal2025-title"><i class="fas fa-plus-circle me-2"></i>Add Salary Information</span>
                                <button type="button" class="modal2025-close" id="closeAddSalary2025Modal" aria-label="Close">&times;</button>
                            </div>
                            <form method="POST" class="needs-validation" novalidate autocomplete="off">
                                <?php if ($salary_error): ?>
                                    <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?php echo $salary_error; ?></div>
                                <?php endif; ?>
                                <?php if ($salary_success): ?>
                                    <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?php echo $salary_success; ?></div>
                                <?php endif; ?>
                                <div class="container-fluid">
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <label for="employee_id" class="form-label">Employee</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                                <select class="form-select" id="employee_id" name="employee_id" required>
                                                    <option value="">Select Employee</option>
                                                    <?php foreach ($employee->getAllEmployees() as $emp): ?>
                                                        <option value="<?php echo htmlspecialchars($emp['employee_id']); ?>"><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name'] . ' (' . $emp['employee_id'] . ')'); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="invalid-feedback">Please select an employee.</div>
                                        </div>
                                        <div class="col-md-12">
                                            <label for="amount" class="form-label">Amount</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                                <input type="number" step="0.01" class="form-control" id="amount" name="amount" required placeholder="Enter amount">
                                            </div>
                                            <div class="invalid-feedback">Please enter a valid amount.</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="month" class="form-label">Month</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                                <select class="form-select" id="month" name="month" required>
                                                    <option value="">Select Month</option>
                                                    <?php for ($m = 1; $m <= 12; $m++): ?>
                                                        <option value="<?php echo $m; ?>"><?php echo date('F', mktime(0, 0, 0, $m, 1)); ?></option>
                                                    <?php endfor; ?>
                                                </select>
                                            </div>
                                            <div class="invalid-feedback">Please select a month.</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="year" class="form-label">Year</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                                <select class="form-select" id="year" name="year" required>
                                                    <option value="">Select Year</option>
                                                    <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                                        <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                                                    <?php endfor; ?>
                                                </select>
                                            </div>
                                            <div class="invalid-feedback">Please select a year.</div>
                                        </div>
                                        <div class="col-md-12">
                                            <label for="notes" class="form-label">Notes (optional)</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-sticky-note"></i></span>
                                                <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Add any notes..."></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal2025-footer">
                                    <button type="button" class="btn btn-secondary" id="closeAddSalary2025ModalFooter">Cancel</button>
                                    <button type="submit" name="add_salary" class="btn btn-success"><i class="fas fa-save me-1"></i>Add Salary</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- Generate Monthly Salaries Modal (2025Modal) -->
                    <button type="button" class="btn btn-primary" id="openGenerateSalary2025Modal">
                        <i class="fas fa-plus-circle me-1"></i>Generate Monthly Salaries
                    </button>
                    <div class="modal2025-overlay hide" id="generateSalary2025Modal">
                        <div class="modal2025-content">
                            <div class="modal2025-header">
                                <span class="modal2025-title"><i class="fas fa-plus-circle me-2"></i>Generate Monthly Salaries</span>
                                <button type="button" class="modal2025-close" id="closeGenerateSalary2025Modal" aria-label="Close">&times;</button>
                            </div>
                            <form method="POST">
                                <div class="container-fluid">
                                    <p>Generate salary records for all active employees for a specific month.</p>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="gen_month" class="form-label">Month</label>
                                            <select class="form-select" id="gen_month" name="month" required>
                                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                                    <option value="<?php echo $m; ?>" <?php echo date('n') == $m ? 'selected' : ''; ?>>
                                                        <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                                                    </option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="gen_year" class="form-label">Year</label>
                                            <select class="form-select" id="gen_year" name="year" required>
                                                <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                                    <option value="<?php echo $y; ?>" <?php echo date('Y') == $y ? 'selected' : ''; ?>>
                                                        <?php echo $y; ?>
                                                    </option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="alert alert-info mt-3">
                                        <i class="fas fa-info-circle me-2"></i>
                                        This will create salary records for all active employees. If records already exist for the selected period, they will be skipped.
                                    </div>
                                </div>
                                <div class="modal2025-footer">
                                    <button type="button" class="btn btn-secondary" id="closeGenerateSalary2025ModalFooter">Cancel</button>
                                    <button type="submit" name="generate_salaries" class="btn btn-primary">
                                        <i class="fas fa-plus-circle me-1"></i>Generate Salaries
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <script>
                        // Output all employees as a JS object for modal use
                        window.EMPLOYEES = <?php echo json_encode(array_column($employee->getAllEmployees(), null, 'employee_id')); ?>;
                        // Output all salary records as a JS object: { employee_id: { 'year-month': true, ... }, ... }
                        window.SALARIES = {};
                        <?php foreach ($employee->getSalaryPayments() as $s): ?>
                            window.SALARIES['<?php echo $s['employee_id']; ?>'] = window.SALARIES['<?php echo $s['employee_id']; ?>'] || {};
                            window.SALARIES['<?php echo $s['employee_id']; ?>']['<?php echo $s['year']; ?>-<?php echo $s['month']; ?>'] = true;
                        <?php endforeach; ?>

                        // Add Salary Modal 2025 logic
                        const openAddSalaryBtn = document.getElementById('openAddSalary2025Modal');
                        const addSalaryModal = document.getElementById('addSalary2025Modal');
                        const closeAddSalaryBtn = document.getElementById('closeAddSalary2025Modal');
                        const closeAddSalaryFooterBtn = document.getElementById('closeAddSalary2025ModalFooter');
                        if (openAddSalaryBtn && addSalaryModal) {
                            openAddSalaryBtn.addEventListener('click', function() {
                                addSalaryModal.classList.remove('hide');
                                addSalaryModal.style.display = 'flex';
                            });
                        }

                        function closeAddSalaryModal() {
                            addSalaryModal.classList.add('hide');
                            setTimeout(() => {
                                addSalaryModal.style.display = 'none';
                            }, 300);
                        }
                        if (closeAddSalaryBtn) closeAddSalaryBtn.addEventListener('click', closeAddSalaryModal);
                        if (closeAddSalaryFooterBtn) closeAddSalaryFooterBtn.addEventListener('click', closeAddSalaryModal);
                        addSalaryModal.addEventListener('click', function(e) {
                            if (e.target === addSalaryModal) closeAddSalaryModal();
                        });

                        // Generate Salary Modal 2025 logic
                        const openGenerateSalaryBtn = document.getElementById('openGenerateSalary2025Modal');
                        const generateSalaryModal = document.getElementById('generateSalary2025Modal');
                        const closeGenerateSalaryBtn = document.getElementById('closeGenerateSalary2025Modal');
                        const closeGenerateSalaryFooterBtn = document.getElementById('closeGenerateSalary2025ModalFooter');
                        if (openGenerateSalaryBtn && generateSalaryModal) {
                            openGenerateSalaryBtn.addEventListener('click', function() {
                                generateSalaryModal.classList.remove('hide');
                                generateSalaryModal.style.display = 'flex';
                            });
                        }

                        function closeGenerateSalaryModal() {
                            generateSalaryModal.classList.add('hide');
                            setTimeout(() => {
                                generateSalaryModal.style.display = 'none';
                            }, 300);
                        }
                        if (closeGenerateSalaryBtn) closeGenerateSalaryBtn.addEventListener('click', closeGenerateSalaryModal);
                        if (closeGenerateSalaryFooterBtn) closeGenerateSalaryFooterBtn.addEventListener('click', closeGenerateSalaryModal);
                        generateSalaryModal.addEventListener('click', function(e) {
                            if (e.target === generateSalaryModal) closeGenerateSalaryModal();
                        });

                        // Add Salary Modal dynamic logic
                        const employeeSelect = document.getElementById('employee_id');
                        const amountInput = document.getElementById('amount');
                        const monthSelect = document.getElementById('month');
                        const yearSelect = document.getElementById('year');
                        const modalTitle = document.querySelector('#addSalary2025Modal .modal2025-title');

                        function updateSalaryModalFields() {
                            const empId = employeeSelect.value;
                            if (empId && window.EMPLOYEES[empId]) {
                                const emp = window.EMPLOYEES[empId];
                                // Set modal title
                                const monthName = monthSelect.options[monthSelect.selectedIndex]?.text || '';
                                const yearVal = yearSelect.value;
                                modalTitle.innerHTML = `<i class='fas fa-plus-circle me-2'></i>Add salary to <b>${emp.first_name} ${emp.last_name}</b> for <b>${monthName} ${yearVal}</b>`;
                                // Set and lock salary
                                amountInput.value = emp.monthly_salary;
                                amountInput.readOnly = true;
                                amountInput.classList.add('bg-light');
                                // Lock month/year
                                monthSelect.disabled = false;
                                yearSelect.disabled = false;
                                // Filter months/years to only those not already paid
                                const paid = window.SALARIES[empId] || {};
                                // Save current selection
                                const prevMonth = monthSelect.value;
                                const prevYear = yearSelect.value;
                                // Clear and repopulate yearSelect
                                const currentYear = new Date().getFullYear();
                                yearSelect.innerHTML = '<option value="">Select Year</option>';
                                for (let y = currentYear; y >= 2020; y--) {
                                    let hasUnpaid = false;
                                    for (let m = 1; m <= 12; m++) {
                                        if (!paid[`${y}-${m}`]) {
                                            hasUnpaid = true;
                                            break;
                                        }
                                    }
                                    if (hasUnpaid) {
                                        yearSelect.innerHTML += `<option value="${y}"${prevYear == y ? ' selected' : ''}>${y}</option>`;
                                    }
                                }
                                // If year changed, update months
                                let selectedYear = yearSelect.value || currentYear;
                                // Clear and repopulate monthSelect
                                monthSelect.innerHTML = '<option value="">Select Month</option>';
                                for (let m = 1; m <= 12; m++) {
                                    if (!paid[`${selectedYear}-${m}`]) {
                                        const monthName = new Date(selectedYear, m - 1).toLocaleString('default', {
                                            month: 'long'
                                        });
                                        monthSelect.innerHTML += `<option value="${m}"${prevMonth == m ? ' selected' : ''}>${monthName}</option>`;
                                    }
                                }
                                // If only one year/month, select it
                                if (yearSelect.options.length === 2) yearSelect.selectedIndex = 1;
                                if (monthSelect.options.length === 2) monthSelect.selectedIndex = 1;
                                // If no available months/years, show message
                                if (yearSelect.options.length === 1 || monthSelect.options.length === 1) {
                                    monthSelect.disabled = true;
                                    yearSelect.disabled = true;
                                    amountInput.value = '';
                                    amountInput.readOnly = true;
                                    modalTitle.innerHTML = `<i class='fas fa-plus-circle me-2'></i>All months/years already paid for <b>${emp.first_name} ${emp.last_name}</b>`;
                                }
                            } else {
                                modalTitle.innerHTML = `<i class='fas fa-plus-circle me-2'></i>Add Salary Information`;
                                amountInput.value = '';
                                amountInput.readOnly = false;
                                amountInput.classList.remove('bg-light');
                                monthSelect.disabled = false;
                                yearSelect.disabled = false;
                            }
                        }
                        employeeSelect.addEventListener('change', updateSalaryModalFields);
                        monthSelect.addEventListener('change', updateSalaryModalFields);
                        yearSelect.addEventListener('change', updateSalaryModalFields);
                        // On modal open, reset fields
                        openAddSalaryBtn.addEventListener('click', function() {
                            setTimeout(updateSalaryModalFields, 50);
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>