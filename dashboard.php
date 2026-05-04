<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';

$auth = new Auth();
$auth->requireLogin();

require_once 'classes/Report.php';

$page_title = 'Dashboard';
include 'includes/header.php';

$report = new Report();
$dashboardData = $report->getDashboardData();
$expense_chart_data = $report->getExpenseBreakdownForChart();
$income_expense_trend = $report->getIncomeExpenseTrendForChart();
?>

<div class="page-animate">
    <div class="w-full">
        <!-- Dashboard Header -->
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div class="relative">
                <div class="flex items-center gap-4 mb-2">
                    <h1 class="text-4xl font-black text-gray-900 tracking-tight m-0 flex items-center gap-3">
                        Dashboardd
                    </h1>
                    <span
                        class="bg-brand text-white text-[10px] font-black px-2.5 py-1 rounded-md shadow-lg shadow-brand/20 uppercase tracking-tighter">Live</span>
                </div>
                <p class="text-gray-500 font-medium text-sm m-0">
                    Welcome back, <span
                        class="text-brand font-bold underline decoration-brand/30 underline-offset-4"><?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?></span>.
                    Here's your financial status.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="reports.php"
                    class="h-11 px-5 bg-white text-gray-700 border-3 border-gray-100 rounded-xl font-bold text-xs uppercase tracking-widest hover:border-brand hover:text-brand transition-all flex items-center gap-2 shadow-sm">
                    <i class="fas fa-chart-bar text-[10px]"></i> Reports
                </a>
                <a href="add_transaction.php"
                    class="h-11 px-6 bg-brand text-white rounded-xl font-black text-xs uppercase tracking-widest hover:bg-black transition-all flex items-center gap-2 shadow-xl shadow-brand/20">
                    <i class="fas fa-plus text-[10px]"></i> New Entry
                </a>
            </div>
        </div>

        <!-- Main Dashboard Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <!-- KPI Cards -->
            <a href="transactions.php?type=income" class="group block relative">
                <div
                    class="bg-white rounded-2xl p-6 border-3 border-gray-100 shadow-sm transition-all duration-300 group-hover:border-emerald-500 group-hover:-translate-y-1 group-hover:shadow-xl group-hover:shadow-emerald-500/10 h-full">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Monthly Income</div>
                        <div
                            class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-lg group-hover:bg-emerald-500 group-hover:text-white transition-colors">
                            <i class="fas fa-arrow-trend-up"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-black text-gray-900 amount mb-2">
                        <?php echo CURRENCY_SYMBOL; ?> <?php echo number_format($dashboardData['monthly_income'], 2); ?>
                    </div>
                    <div class="flex items-center gap-2 text-[10px] font-bold text-emerald-500">
                        <i class="fas fa-plus"></i> 15% from last month
                    </div>
                </div>
            </a>

            <a href="transactions.php?type=expense" class="group block relative">
                <div
                    class="bg-white rounded-2xl p-6 border-3 border-gray-100 shadow-sm transition-all duration-300 group-hover:border-rose-500 group-hover:-translate-y-1 group-hover:shadow-xl group-hover:shadow-rose-500/10 h-full">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Monthly Expenses
                        </div>
                        <div
                            class="w-10 h-10 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center text-lg group-hover:bg-rose-500 group-hover:text-white transition-colors">
                            <i class="fas fa-arrow-trend-down"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-black text-gray-900 amount mb-2">
                        <?php echo CURRENCY_SYMBOL; ?>
                        <?php echo number_format($dashboardData['monthly_expenses'], 2); ?>
                    </div>
                    <div class="flex items-center gap-2 text-[10px] font-bold text-rose-500">
                        <i class="fas fa-minus"></i> 5% from last month
                    </div>
                </div>
            </a>

            <a href="reports.php" class="group block relative">
                <div
                    class="bg-white rounded-2xl p-6 border-3 border-gray-100 shadow-sm transition-all duration-300 group-hover:border-brand group-hover:-translate-y-1 group-hover:shadow-xl group-hover:shadow-brand/10 h-full">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Net Balance</div>
                        <div
                            class="w-10 h-10 rounded-xl bg-brand-light text-brand flex items-center justify-center text-lg group-hover:bg-brand group-hover:text-white transition-colors">
                            <i class="fas fa-balance-scale"></i>
                        </div>
                    </div>
                    <div
                        class="text-2xl font-black amount mb-2 <?php echo $dashboardData['monthly_balance'] >= 0 ? 'text-emerald-600' : 'text-rose-600'; ?>">
                        <?php echo CURRENCY_SYMBOL; ?>
                        <?php echo number_format($dashboardData['monthly_balance'], 2); ?>
                    </div>
                    <div class="flex items-center gap-2 text-[10px] font-bold text-gray-400">
                        Current Month Status
                    </div>
                </div>
            </a>

            <a href="employees.php" class="group block relative">
                <div
                    class="bg-white rounded-2xl p-6 border-3 border-gray-100 shadow-sm transition-all duration-300 group-hover:border-indigo-500 group-hover:-translate-y-1 group-hover:shadow-xl group-hover:shadow-indigo-500/10 h-full">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Active Personnel
                        </div>
                        <div
                            class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-500 flex items-center justify-center text-lg group-hover:bg-indigo-500 group-hover:text-white transition-colors">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-black text-gray-900 amount mb-2">
                        <?php echo $dashboardData['active_employees']; ?>
                    </div>
                    <div class="flex items-center gap-2 text-[10px] font-bold text-gray-400">
                        Manage Staff Directory
                    </div>
                </div>
            </a>
        </div>

        <!-- Charts Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
            <div class="lg:col-span-2">
                <div
                    class="bg-white rounded-3xl border-3 border-gray-100 shadow-sm p-8 h-full relative overflow-hidden group">
                    <div class="flex justify-between items-center mb-8 relative z-10">
                        <div>
                            <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Financial
                                Performance</h3>
                            <h2 class="text-lg font-black text-gray-900">Income vs Expense Trend</h2>
                        </div>
                        <div class="flex items-center gap-4 text-[10px] font-bold uppercase tracking-widest">
                            <div class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-brand"></span>
                                Income</div>
                            <div class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                Expenses</div>
                        </div>
                    </div>
                    <div class="relative h-[320px] z-10">
                        <canvas id="incomeExpenseTrendChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="lg:col-span-1">
                <div class="bg-white rounded-3xl border-3 border-gray-100 shadow-sm p-8 h-full flex flex-col group">
                    <div class="mb-8">
                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Resource Allocation
                        </h3>
                        <h2 class="text-lg font-black text-gray-900">Expense Breakdown</h2>
                    </div>
                    <div class="relative flex-grow min-h-[250px]">
                        <canvas id="expenseBreakdownChart"></canvas>
                    </div>
                    <div id="chart-legend"
                        class="mt-8 text-[10px] font-bold uppercase tracking-widest text-gray-500 flex flex-wrap justify-center gap-4">
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions Preview (Added for Pro feel) -->
        <?php if (!empty($dashboardData['recent_transactions'])): ?>
            <div class="mb-10">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-black text-gray-900 uppercase tracking-tight">Recent Activity</h2>
                    <a href="transactions.php" class="text-xs font-bold text-brand hover:underline">View All Records <i
                            class="fas fa-arrow-right ml-1 text-[8px]"></i></a>
                </div>
                <div class="bg-white rounded-3xl border-3 border-gray-100 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50/50 border-b border-gray-100">
                                    <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                        Type</th>
                                    <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                        Description</th>
                                    <th
                                        class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">
                                        Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <?php foreach ($dashboardData['recent_transactions'] as $tx): ?>
                                    <tr class="hover:bg-gray-50/50 transition-colors group">
                                        <td class="px-8 py-4">
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 rounded-md text-[9px] font-black uppercase tracking-tighter <?php echo $tx['type'] == 'income' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'; ?>">
                                                <?php echo $tx['type']; ?>
                                            </span>
                                        </td>
                                        <td class="px-8 py-4">
                                            <div class="text-sm font-bold text-gray-800">
                                                <?php echo htmlspecialchars($tx['description']); ?></div>
                                            <div class="text-[10px] text-gray-400 font-medium">
                                                <?php echo date('M d, Y', strtotime($tx['transaction_date'])); ?></div>
                                        </td>
                                        <td class="px-8 py-4 text-right font-black text-gray-900 amount">
                                            <?php echo $tx['type'] == 'income' ? '+' : '-'; ?>        <?php echo CURRENCY_SYMBOL . number_format($tx['amount'], 2); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
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
                            label: function (context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed !== null) {
                                    label += new Intl.NumberFormat('en-US', {
                                        style: 'currency',
                                        currency: 'ETB'
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
                            label: function (context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('en-US', {
                                        style: 'currency',
                                        currency: 'ETB'
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
                            callback: function (value, index, values) {
                                return '<?php echo CURRENCY_SYMBOL; ?> ' + new Intl.NumberFormat().format(value);
                            }
                        }
                    }
                }
            }
        });

    });
</script>

<?php include 'includes/footer.php'; ?>