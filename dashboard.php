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
$expense_chart_data = $report->getExpenseBreakdownForChart();
$income_expense_trend = $report->getIncomeExpenseTrendForChart();

include 'includes/navbar.php';
?>

<style>
    /* Dashboard Grid Layout - Updated for 4 KPIs */
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        grid-gap: 1.5rem;
    }

    @media (max-width: 991px) {
        .dashboard-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 576px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
        }
    }

    /* KPI Card Style - Rebuilt to match the image */
    .kpi-card {
        background: #FFFFFF;
        border-radius: 0.75rem;
        padding: 1.5rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        border: 1px solid #e9ecef;
    }

    .kpi-card .card-title {
        font-size: 0.9rem;
        color: #6c757d;
        margin-bottom: 0.5rem;
    }

    .kpi-card .card-value {
        font-size: 2rem;
        font-weight: 700;
        color: #212529;
    }

    .kpi-card .card-extra {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 1rem;
    }

    .kpi-card .card-change {
        font-size: 0.9rem;
        font-weight: 500;
    }

    .kpi-card .card-icon {
        width: 40px;
        height: 40px;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .status-item .details .value {
        font-size: 0.9rem;
        color: #6c757d;
    }

    .status-item-clickable:hover {
        background-color: rgba(40, 167, 69, 0.1);
        cursor: pointer;
        border-radius: 0.5rem;
    }

    @keyframes spin {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }

    a.kpi-card-link {
        text-decoration: none;
        color: inherit;
    }

    a.kpi-card-link .kpi-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        border-color: #1976d2;
    }
</style>

<div class="page-animate">
    <div class="container-fluid py-4">
        <!-- Dashboard Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h2 mb-0">Dashboard</h1>
                <p class="text-muted">Welcome, <?php echo $_SESSION['full_name'] ?? $_SESSION['username']; ?>!</p>
            </div>
        </div>

        <!-- Main Dashboard Grid -->
        <div class="dashboard-grid">
            <!-- KPI Cards -->
            <a href="transactions.php?type=income" class="kpi-card-link">
                <div class="kpi-card">
                    <div class="card-title">This Month's Income</div>
                    <div class="card-value">$<?php echo number_format($dashboardData['monthly_income'], 2); ?></div>
                    <div class="card-extra">
                        <span class="card-change text-success"><i class="fas fa-arrow-up"></i> 15%</span>
                        <div class="card-icon" style="background-color: #e0f2f1; color: #00796b;"><i class="fas fa-dollar-sign"></i></div>
                    </div>
                </div>
            </a>
            <a href="transactions.php?type=expense" class="kpi-card-link">
                <div class="kpi-card">
                    <div class="card-title">This Month's Expenses</div>
                    <div class="card-value">$<?php echo number_format($dashboardData['monthly_expenses'], 2); ?></div>
                    <div class="card-extra">
                        <span class="card-change text-danger"><i class="fas fa-arrow-down"></i> 5%</span>
                        <div class="card-icon" style="background-color: #fff3e0; color: #f57c00;"><i class="fas fa-shopping-cart"></i></div>
                    </div>
                </div>
            </a>
            <a href="reports.php" class="kpi-card-link">
                <div class="kpi-card">
                    <div class="card-title">This Month's Net Balance</div>
                    <div class="card-value <?php echo $dashboardData['monthly_balance'] >= 0 ? 'text-success' : 'text-danger'; ?>">$<?php echo number_format($dashboardData['monthly_balance'], 2); ?></div>
                    <div class="card-extra">
                        <span class="card-change text-success"><i class="fas fa-arrow-up"></i> 10%</span>
                        <div class="card-icon" style="background-color: #e3f2fd; color: #1976d2;"><i class="fas fa-balance-scale"></i></div>
                    </div>
                </div>
            </a>
            <a href="employees.php" class="kpi-card-link">
                <div class="kpi-card">
                    <div class="card-title">Active Employees</div>
                    <div class="card-value"><?php echo $dashboardData['active_employees']; ?></div>
                    <div class="card-extra">
                        <span class="card-change text-muted">View</span>
                        <div class="card-icon" style="background-color: #f3e5f5; color: #8e24aa;"><i class="fas fa-users"></i></div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Charts and Recent Activity Grid -->
        <div class="row mt-5">
            <div class="col-lg-8">
                <div class="card shadow-sm h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-chart-pie me-1"></i>Expense Breakdown</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-pie pt-4 pb-2">
                            <canvas id="expenseBreakdownChart"></canvas>
                        </div>
                        <div class="mt-4 text-center small" id="chart-legend">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-chart-line me-1"></i>Income vs Expense (30 Days)</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-area">
                            <canvas id="incomeExpenseTrendChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Expense Breakdown Chart
        const expenseCtx = document.getElementById('expenseBreakdownChart').getContext('2d');
        const expenseData = <?php echo json_encode($expense_chart_data, JSON_NUMERIC_CHECK); ?>;
        const expenseLabels = Object.keys(expenseData);
        const expenseValues = Object.values(expenseData);

        const expenseChart = new Chart(expenseCtx, {
            type: 'doughnut',
            data: {
                labels: expenseLabels,
                datasets: [{
                    data: expenseValues,
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796'],
                    hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#be2617', '#60616f'],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }],
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                plugins: {
                    legend: {
                        display: false // We will generate a custom legend
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed !== null) {
                                    label += new Intl.NumberFormat('en-US', {
                                        style: 'currency',
                                        currency: 'USD'
                                    }).format(context.parsed);
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });

        // Custom Legend
        const legendContainer = document.getElementById('chart-legend');
        expenseChart.data.labels.forEach((label, i) => {
            const color = expenseChart.data.datasets[0].backgroundColor[i];
            legendContainer.innerHTML += `
            <span class="mr-2" style="margin-right: 1rem;">
                <i class="fas fa-circle" style="color:${color}"></i> ${label}
            </span>
        `;
        });


        // Income vs Expense Trend Chart
        const trendCtx = document.getElementById('incomeExpenseTrendChart').getContext('2d');
        const trendData = <?php echo json_encode($income_expense_trend); ?>;

        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: trendData.labels,
                datasets: [{
                    label: "Income",
                    lineTension: 0.3,
                    backgroundColor: "rgba(78, 115, 223, 0.05)",
                    borderColor: "rgba(78, 115, 223, 1)",
                    pointRadius: 3,
                    pointBackgroundColor: "rgba(78, 115, 223, 1)",
                    pointBorderColor: "rgba(78, 115, 223, 1)",
                    pointHoverRadius: 3,
                    pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                    pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                    data: trendData.income,
                }, {
                    label: "Expenses",
                    lineTension: 0.3,
                    backgroundColor: "rgba(231, 74, 59, 0.05)",
                    borderColor: "rgba(231, 74, 59, 1)",
                    pointRadius: 3,
                    pointBackgroundColor: "rgba(231, 74, 59, 1)",
                    pointBorderColor: "rgba(231, 74, 59, 1)",
                    pointHoverRadius: 3,
                    pointHoverBackgroundColor: "rgba(231, 74, 59, 1)",
                    pointHoverBorderColor: "rgba(231, 74, 59, 1)",
                    data: trendData.expenses,
                }],
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('en-US', {
                                        style: 'currency',
                                        currency: 'USD'
                                    }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        ticks: {
                            callback: function(value, index, values) {
                                return '$' + new Intl.NumberFormat().format(value);
                            }
                        }
                    }
                }
            }
        });

    });
</script>

<?php include 'includes/footer.php'; ?>