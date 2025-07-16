<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Employee.php';

$page_title = 'Pending Salaries';
include 'includes/header.php';

$auth = new Auth();
$auth->requireLogin();

$employee = new Employee();
$pending_salaries = $employee->getPendingSalaries();

include 'includes/navbar.php';
?>
<div class="page-animate">
    <div class="main-info-card">
        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="fas fa-clock me-2"></i>Pending Salary Payments</h2>
                    </div>
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                Pending Salaries (<?php echo count($pending_salaries); ?> records)
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($pending_salaries)): ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-4x mb-3"></i>
                                    <h4>No pending salaries found</h4>
                                    <p>All salaries have been paid.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Employee</th>
                                                <th>Month</th>
                                                <th>Year</th>
                                                <th class="text-end">Amount</th>
                                                <th class="no-print">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($pending_salaries as $s): ?>
                                                <tr>
                                                    <td><strong><?php echo htmlspecialchars(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? '')); ?> (<?php echo htmlspecialchars($s['employee_id'] ?? ''); ?>)</strong></td>
                                                    <td><?php echo date('F', mktime(0, 0, 0, $s['month'], 1)); ?></td>
                                                    <td><?php echo $s['year']; ?></td>
                                                    <td class="text-end">$<?php echo number_format($s['amount'], 2); ?></td>
                                                    <td class="no-print">
                                                        <a href="pay_salary.php?id=<?php echo $s['id']; ?>" class="btn btn-success btn-sm">
                                                            <i class="fas fa-dollar-sign me-1"></i>Pay
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
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
<?php include 'includes/footer.php'; ?>