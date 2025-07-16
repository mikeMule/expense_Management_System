<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Report.php';

$page_title = 'Reports';
include 'includes/header.php';

$auth = new Auth();
$auth->requireLogin();

$report = new Report();

// Get report data
$dashboardData = $report->getDashboardData();
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
$monthlyReport = $report->getMonthlyReport($month, $year);
$yearlyReport = $report->getYearlyReport($year);

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
        <div class="container py-4">
            <div class="dashboard-header text-center mb-4">
                <h1 class="dashboard-title">
                    <i class="fas fa-chart-bar me-3"></i>Reports & Analytics
                </h1>
                <p class="dashboard-subtitle">Comprehensive financial insights for your organization</p>
            </div>
            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card stat-card income h-100">
                        <div class="card-body">
                            <h2 class="stat-value text-success">$<?php echo number_format($dashboardData['total_income'], 2); ?></h2>
                            <p class="stat-label">Total Income</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card stat-card expense h-100">
                        <div class="card-body">
                            <h2 class="stat-value text-danger">$<?php echo number_format($dashboardData['total_expenses'], 2); ?></h2>
                            <p class="stat-label">Total Expenses</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card stat-card balance h-100">
                        <div class="card-body">
                            <h2 class="stat-value text-primary">$<?php echo number_format($dashboardData['balance'], 2); ?></h2>
                            <p class="stat-label">Current Balance</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card stat-card salary h-100">
                        <div class="card-body">
                            <h2 class="stat-value text-info">$<?php echo number_format($dashboardData['monthly_salary_budget'], 2); ?></h2>
                            <p class="stat-label">Monthly Salary Budget</p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Charts Row -->
            <div class="row mb-4">
                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Expenses by Category (<?php echo date('F Y', strtotime("$year-$month-01")); ?>)</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="expensesByCategoryChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Income by Category (<?php echo date('F Y', strtotime("$year-$month-01")); ?>)</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="incomeByCategoryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Monthly Cash Flow Chart -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card h-100">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Monthly Cash Flow (<?php echo $year; ?>)</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="monthlyCashFlowChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Recent Transactions Table -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card h-100">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
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
                                    <table class="table table-striped align-middle">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Description</th>
                                                <th>Category</th>
                                                <th>Type</th>
                                                <th>Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($dashboardData['recent_transactions'] as $t): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($t['transaction_date']); ?></td>
                                                    <td><?php echo htmlspecialchars($t['description']); ?></td>
                                                    <td><?php echo htmlspecialchars($t['category_name'] ?? ''); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $t['type'] === 'income' ? 'success' : 'danger'; ?>">
                                                            <?php echo ucfirst($t['type']); ?>
                                                        </span>
                                                    </td>
                                                    <td>$<?php echo number_format($t['amount'], 2); ?></td>
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
<!-- Chart.js scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Expenses by Category
    const expensesByCategory = <?php echo json_encode($monthlyReport['expenses_by_category']); ?>;
    const expensesLabels = expensesByCategory.map(e => e.name);
    const expensesData = expensesByCategory.map(e => parseFloat(e.total));
    new Chart(document.getElementById('expensesByCategoryChart'), {
        type: 'doughnut',
        data: {
            labels: expensesLabels,
            datasets: [{
                data: expensesData,
                backgroundColor: [
                    '#764ba2', '#667eea', '#43cea2', '#f7971e', '#fd5c63', '#36d1c4', '#f7797d', '#fcb045', '#fdc830', '#e96443'
                ],
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            animation: false
        }
    });
    // Income by Category
    const incomeByCategory = <?php echo json_encode($monthlyReport['income_by_category']); ?>;
    const incomeLabels = incomeByCategory.map(e => e.name);
    const incomeData = incomeByCategory.map(e => parseFloat(e.total));
    new Chart(document.getElementById('incomeByCategoryChart'), {
        type: 'doughnut',
        data: {
            labels: incomeLabels,
            datasets: [{
                data: incomeData,
                backgroundColor: [
                    '#43cea2', '#764ba2', '#667eea', '#f7971e', '#fd5c63', '#36d1c4', '#f7797d', '#fcb045', '#fdc830', '#e96443'
                ],
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            animation: false
        }
    });
    // Monthly Cash Flow
    const monthlyCashFlow = <?php echo json_encode($yearlyReport['monthly_cash_flow']); ?>;
    const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    const incomeFlow = Array(12).fill(0);
    const expenseFlow = Array(12).fill(0);
    monthlyCashFlow.forEach(row => {
        const idx = row.month - 1;
        incomeFlow[idx] = parseFloat(row.income);
        expenseFlow[idx] = parseFloat(row.expenses);
    });
    new Chart(document.getElementById('monthlyCashFlowChart'), {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                    label: 'Income',
                    data: incomeFlow,
                    borderColor: '#43cea2',
                    backgroundColor: 'rgba(67,206,162,0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Expenses',
                    data: expenseFlow,
                    borderColor: '#fd5c63',
                    backgroundColor: 'rgba(253,92,99,0.1)',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            animation: false
        }
    });
</script>