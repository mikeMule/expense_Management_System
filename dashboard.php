<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Report.php';

$page_title = 'Dashboard';
include 'includes/header.php';

$auth = new Auth();
$auth->requireLogin();

$report = new Report();
$dashboardData = $report->getDashboardData();

include 'includes/navbar.php';
?>

<style>
    #loading-overlay.swipe-up {
        animation: swipeUp 0.7s cubic-bezier(0.77, 0, 0.18, 1) forwards;
    }

    @keyframes swipeUp {
        0% {
            transform: translateY(0);
            opacity: 1;
        }

        80% {
            opacity: 1;
        }

        100% {
            transform: translateY(-120%);
            opacity: 0;
        }
    }
</style>

<body class="dashboard-page">
    <div id="loading-overlay">
        <div class="loader-container">
            <div style="display: flex; justify-content: center; align-items: center; margin-bottom: 1.5rem;">
                <div style="width: 120px; height: 120px; border-radius: 50%; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.12); background: #fff; display: flex; align-items: center; justify-content: center;">
                    <img src="https://media2.giphy.com/media/v1.Y2lkPTc5MGI3NjExbmJteDdjOTMwYXc5Z2x0dHM3Mm5yMzE4ZjB4OGVvY21jOXNhZjhxcyZlcD12MV9pbnRlcm5hbF9naWZfYnlfaWQmY3Q9Zw/e90kry2Jse4IbdEcrB/giphy.gif" alt="Loading..." style="width: 100px; height: 100px; object-fit: cover; display: block;" />
                </div>
            </div>
            <div class="loading-text">Loading...<br>Collecting Data</div>
        </div>
    </div>

    <div class="page-animate">
        <div class="container-fluid py-4">
            <!-- Dashboard Header -->
            <div class="dashboard-header text-center">
                <div class="container">
                    <h1 class="dashboard-title">
                        <i class="fas fa-tachometer-alt me-3"></i>Dashboard
                    </h1>
                    <p class="dashboard-subtitle">Welcome back, <?php echo $_SESSION['full_name'] ?? $_SESSION['username']; ?>!</p>
                </div>
            </div>

            <!-- Alerts for pending salaries -->
            <?php if ($dashboardData['pending_salaries'] > 0): ?>
                <div class="alert alert-warning" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Attention!</strong> You have <?php echo $dashboardData['pending_salaries']; ?> pending salary payment(s).
                    <a href="salaries.php" class="alert-link">View Details</a>
                </div>
            <?php endif; ?>

            <!-- Financial Overview Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card stat-card income h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h2 class="stat-value text-success">$<?php echo number_format($dashboardData['total_income'], 2); ?></h2>
                                    <p class="stat-label">Total Income</p>
                                </div>
                                <i class="fas fa-arrow-up fa-2x text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card stat-card expense h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h2 class="stat-value text-danger">$<?php echo number_format($dashboardData['total_expenses'], 2); ?></h2>
                                    <p class="stat-label">Total Expenses</p>
                                </div>
                                <i class="fas fa-arrow-down fa-2x text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card stat-card balance h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h2 class="stat-value <?php echo $dashboardData['balance'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                        $<?php echo number_format($dashboardData['balance'], 2); ?>
                                    </h2>
                                    <p class="stat-label">Net Balance</p>
                                </div>
                                <i class="fas fa-balance-scale fa-2x text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card stat-card warning h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h2 class="stat-value text-primary"><?php echo $dashboardData['active_employees']; ?></h2>
                                    <p class="stat-label">Active Employees</p>
                                    <small class="text-muted">Monthly Budget: $<?php echo number_format($dashboardData['monthly_salary_budget'], 2); ?></small>
                                </div>
                                <i class="fas fa-users fa-2x text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Overview -->
            <div class="row mb-4">
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>This Month</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Income:</span>
                                    <span class="text-success fw-bold">$<?php echo number_format($dashboardData['monthly_income'], 2); ?></span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Expenses:</span>
                                    <span class="text-danger fw-bold">$<?php echo number_format($dashboardData['monthly_expenses'], 2); ?></span>
                                </div>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold">Balance:</span>
                                <span class="fw-bold <?php echo $dashboardData['monthly_balance'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                    $<?php echo number_format($dashboardData['monthly_balance'], 2); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-calendar-day me-2"></i>Today</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Income:</span>
                                    <span class="text-success fw-bold">$<?php echo number_format($dashboardData['daily_income'], 2); ?></span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Expenses:</span>
                                    <span class="text-danger fw-bold">$<?php echo number_format($dashboardData['daily_expenses'], 2); ?></span>
                                </div>
                            </div>
                            <hr>
                            <div class="text-center">
                                <a href="add_transaction.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus me-1"></i>Add Transaction
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 mb-3">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="add_transaction.php?type=income" class="btn btn-success btn-sm">
                                    <i class="fas fa-plus-circle me-1"></i>Add Income
                                </a>
                                <a href="add_transaction.php?type=expense" class="btn btn-danger btn-sm">
                                    <i class="fas fa-minus-circle me-1"></i>Add Expense
                                </a>
                                <a href="employees.php" class="btn btn-info btn-sm">
                                    <i class="fas fa-user-plus me-1"></i>Manage Employees
                                </a>
                                <a href="reports.php" class="btn btn-warning btn-sm">
                                    <i class="fas fa-chart-bar me-1"></i>View Reports
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Transactions</h5>
                            <a href="transactions.php" class="btn btn-outline-primary btn-sm">
                                View All <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($dashboardData['recent_transactions'])): ?>
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p>No transactions found. <a href="add_transaction.php">Add your first transaction</a></p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Type</th>
                                                <th>Description</th>
                                                <th>Category</th>
                                                <th class="text-end">Amount</th>
                                                <th class="no-print">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($dashboardData['recent_transactions'] as $transaction): ?>
                                                <tr>
                                                    <td><?php echo date('M d, Y', strtotime($transaction['transaction_date'])); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $transaction['type'] == 'income' ? 'success' : 'danger'; ?>">
                                                            <i class="fas fa-<?php echo $transaction['type'] == 'income' ? 'arrow-up' : 'arrow-down'; ?> me-1"></i>
                                                            <?php echo ucfirst($transaction['type']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                                                    <td>
                                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($transaction['category_name'] ?? 'N/A'); ?></span>
                                                    </td>
                                                    <td class="text-end">
                                                        <span class="fw-bold <?php echo $transaction['type'] == 'income' ? 'text-success' : 'text-danger'; ?>">
                                                            <?php echo $transaction['type'] == 'income' ? '+' : '-'; ?>$<?php echo number_format($transaction['amount'], 2); ?>
                                                        </span>
                                                    </td>
                                                    <td class="no-print">
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="edit_transaction.php?id=<?php echo $transaction['id']; ?>"
                                                                class="btn btn-outline-primary" title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <a href="delete_transaction.php?id=<?php echo $transaction['id']; ?>"
                                                                class="btn btn-outline-danger btn-delete"
                                                                data-item="transaction" title="Delete">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
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
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <!-- Chart.js scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.body.classList.add('loading');
        window.addEventListener('load', function() {
            setTimeout(function() {
                var overlay = document.getElementById('loading-overlay');
                overlay.classList.add('swipe-up');
                setTimeout(function() {
                    overlay.style.display = 'none';
                    document.body.classList.remove('loading');
                }, 700); // match animation duration
            }, 5000); // 5 seconds delay
        });
    </script>