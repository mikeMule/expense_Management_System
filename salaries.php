<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Employee.php';

$page_title = 'Salary Management';
include 'includes/header.php';

$auth = new Auth();
$auth->requireLogin();

$employee = new Employee();

// Handle success/error messages
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

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

// Get filter parameters
$filter_month = $_GET['month'] ?? date('n');
$filter_year = $_GET['year'] ?? date('Y');

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
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generateModal">
                                <i class="fas fa-plus-circle me-1"></i>Generate Monthly Salaries
                            </button>
                        </div>
                    </div>

                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
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

                    <!-- Filter -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Salary Records</h5>
                        </div>
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <div class="col-md-3">
                                    <label for="month" class="form-label">Month</label>
                                    <select class="form-select" id="month" name="month">
                                        <?php for ($m = 1; $m <= 12; $m++): ?>
                                            <option value="<?php echo $m; ?>" <?php echo $filter_month == $m ? 'selected' : ''; ?>>
                                                <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="year" class="form-label">Year</label>
                                    <select class="form-select" id="year" name="year">
                                        <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                            <option value="<?php echo $y; ?>" <?php echo $filter_year == $y ? 'selected' : ''; ?>>
                                                <?php echo $y; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>

                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="fas fa-search me-1"></i>Filter
                                    </button>
                                    <a href="salaries.php" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i>Clear
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Salary Records Table -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                Salary Records for <?php echo date('F Y', mktime(0, 0, 0, $filter_month, 1, $filter_year)); ?>
                                (<?php echo count($salary_payments); ?> records)
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($salary_payments)): ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="fas fa-money-check-alt fa-4x mb-3"></i>
                                    <h4>No salary records found</h4>
                                    <p>No salary records found for the selected period.</p>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generateModal">
                                        <i class="fas fa-plus-circle me-1"></i>Generate Monthly Salaries
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Employee</th>
                                                <th>Position</th>
                                                <th>Period</th>
                                                <th>Amount</th>
                                                <th>Payment Date</th>
                                                <th>Status</th>
                                                <th>Notes</th>
                                                <th class="no-print">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $total_paid = 0;
                                            $total_pending = 0;
                                            foreach ($salary_payments as $payment):
                                                if ($payment['status'] == 'paid') {
                                                    $total_paid += $payment['amount'];
                                                } else {
                                                    $total_pending += $payment['amount'];
                                                }
                                            ?>
                                                <tr>
                                                    <td>
                                                        <div>
                                                            <strong><?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?></strong>
                                                            <br><small class="text-muted"><?php echo htmlspecialchars($payment['employee_id']); ?></small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($payment['position']); ?></span>
                                                    </td>
                                                    <td>
                                                        <?php echo date('F Y', mktime(0, 0, 0, $payment['month'], 1, $payment['year'])); ?>
                                                    </td>
                                                    <td>
                                                        <strong class="text-success">$<?php echo number_format($payment['amount'], 2); ?></strong>
                                                    </td>
                                                    <td>
                                                        <?php if ($payment['payment_date']): ?>
                                                            <?php echo date('M d, Y', strtotime($payment['payment_date'])); ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $payment['status'] == 'paid' ? 'success' : 'warning'; ?>">
                                                            <i class="fas fa-<?php echo $payment['status'] == 'paid' ? 'check' : 'clock'; ?> me-1"></i>
                                                            <?php echo ucfirst($payment['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if ($payment['notes']): ?>
                                                            <span class="text-muted" title="<?php echo htmlspecialchars($payment['notes']); ?>">
                                                                <?php echo strlen($payment['notes']) > 20 ? substr(htmlspecialchars($payment['notes']), 0, 20) . '...' : htmlspecialchars($payment['notes']); ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="no-print">
                                                        <?php if ($payment['status'] == 'pending'): ?>
                                                            <a href="pay_salary.php?id=<?php echo $payment['id']; ?>"
                                                                class="btn btn-success btn-sm" title="Mark as Paid">
                                                                <i class="fas fa-dollar-sign"></i> Pay
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="badge bg-success">Paid</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <th colspan="3">Totals:</th>
                                                <th>
                                                    <div class="text-success">Paid: $<?php echo number_format($total_paid, 2); ?></div>
                                                    <div class="text-warning">Pending: $<?php echo number_format($total_pending, 2); ?></div>
                                                    <div class="fw-bold">Total: $<?php echo number_format($total_paid + $total_pending, 2); ?></div>
                                                </th>
                                                <th colspan="4"></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Generate Monthly Salaries Modal -->
<div class="modal fade" id="generateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Generate Monthly Salaries</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
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

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        This will create salary records for all active employees. If records already exist for the selected period, they will be skipped.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="generate_salaries" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-1"></i>Generate Salaries
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>