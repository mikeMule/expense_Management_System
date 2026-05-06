<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Report.php';

$auth = new Auth();
$auth->requireLogin();

$page_title = 'Reports';
include 'includes/header.php';

$report = new Report();

// Get report data
$dashboardData = $report->getDashboardData();
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
$monthlyReport = $report->getMonthlyReport($month, $year);
$yearlyReport = $report->getYearlyReport($year);

?>
<div class="page-animate w-full">
    <!-- Header Section -->
    <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div class="relative">
            <div class="flex items-center gap-4 mb-2">
                <h1 class="text-4xl font-black text-gray-900 tracking-tight m-0 flex items-center gap-3">
                    Analytics
                </h1>
                <span class="bg-brand text-white text-[10px] font-black px-2.5 py-1 rounded-md shadow-lg shadow-brand/20 uppercase tracking-tighter">
                    Executive View
                </span>
            </div>
            <p class="text-gray-500 font-medium text-sm m-0">
                Financial performance for <?php echo date('F Y', mktime(0, 0, 0, $month, 1, $year)); ?>.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <form method="GET" class="flex items-center gap-2">
                <select name="month" class="h-11 px-4 bg-white border-3 border-gray-100 rounded-xl font-bold text-xs">
                    <?php for($m=1; $m<=12; $m++): ?>
                        <option value="<?php echo $m; ?>" <?php echo $m == $month ? 'selected' : ''; ?>>
                            <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                        </option>
                    <?php endfor; ?>
                </select>
                <select name="year" class="h-11 px-4 bg-white border-3 border-gray-100 rounded-xl font-bold text-xs">
                    <?php for($y=date('Y'); $y>=2020; $y--): ?>
                        <option value="<?php echo $y; ?>" <?php echo $y == $year ? 'selected' : ''; ?>>
                            <?php echo $y; ?>
                        </option>
                    <?php endfor; ?>
                </select>
                <button type="submit" class="h-11 px-4 bg-brand text-white rounded-xl font-black text-xs uppercase tracking-widest hover:bg-black transition-all">
                    Filter
                </button>
            </form>
            <button onclick="window.print()" class="h-11 px-5 bg-white text-gray-900 border-3 border-gray-100 rounded-xl font-bold text-xs uppercase tracking-widest hover:border-black transition-all flex items-center gap-2 shadow-sm">
                <i class="fas fa-print text-[10px]"></i> Print Report
            </button>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <div class="bg-white rounded-3xl border-3 border-gray-100 shadow-sm p-6 group hover:border-emerald-500 transition-all">
            <div class="flex items-center justify-between mb-4">
                <div class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Period Revenue</div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center group-hover:bg-emerald-500 group-hover:text-white transition-colors">
                    <i class="fas fa-arrow-down"></i>
                </div>
            </div>
            <div class="text-xl font-black text-gray-900 amount">
                <?php echo CURRENCY_SYMBOL . number_format($monthlyReport['total_income'], 2); ?>
            </div>
        </div>

        <div class="bg-white rounded-3xl border-3 border-gray-100 shadow-sm p-6 group hover:border-rose-500 transition-all">
            <div class="flex items-center justify-between mb-4">
                <div class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Period Outflow</div>
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center group-hover:bg-rose-500 group-hover:text-white transition-colors">
                    <i class="fas fa-arrow-up"></i>
                </div>
            </div>
            <div class="text-xl font-black text-gray-900 amount">
                <?php echo CURRENCY_SYMBOL . number_format($monthlyReport['total_expenses'], 2); ?>
            </div>
        </div>

        <div class="bg-white rounded-3xl border-3 border-gray-100 shadow-sm p-6 group hover:border-blue-500 transition-all">
            <div class="flex items-center justify-between mb-4">
                <div class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Period Net</div>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center group-hover:bg-blue-500 group-hover:text-white transition-colors">
                    <i class="fas fa-wallet"></i>
                </div>
            </div>
            <div class="text-xl font-black text-gray-900 amount">
                <?php echo CURRENCY_SYMBOL . number_format($monthlyReport['total_income'] - $monthlyReport['total_expenses'], 2); ?>
            </div>
        </div>

        <div class="bg-white rounded-3xl border-3 border-gray-100 shadow-sm p-6 group hover:border-indigo-500 transition-all">
            <div class="flex items-center justify-between mb-4">
                <div class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Payroll Liability</div>
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-500 flex items-center justify-center group-hover:bg-indigo-500 group-hover:text-white transition-colors">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="text-xl font-black text-gray-900 amount">
                <?php echo CURRENCY_SYMBOL . number_format($dashboardData['monthly_salary_budget'], 2); ?>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
        <div class="bg-white rounded-3xl border-3 border-gray-100 shadow-sm p-8 group hover:border-brand transition-all relative">
            <div class="flex items-center gap-3 mb-8">
                <div class="w-8 h-8 rounded-lg bg-brand/10 text-brand flex items-center justify-center">
                    <i class="fas fa-chart-pie text-xs"></i>
                </div>
                <div>
                    <h3 class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1 text-left">Resource Allocation</h3>
                    <h2 class="text-sm font-black text-gray-900 text-left">Expense Breakdown</h2>
                </div>
            </div>
            <div class="relative h-[300px]">
                <?php if (empty($monthlyReport['expenses_by_category']) || array_sum(array_column($monthlyReport['expenses_by_category'], 'total')) == 0): ?>
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-300">
                        <i class="fas fa-chart-pie text-5xl mb-4 opacity-20"></i>
                        <p class="text-xs font-bold uppercase tracking-widest">No Expense Data</p>
                    </div>
                <?php endif; ?>
                <canvas id="expensesByCategoryChart"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-3xl border-3 border-gray-100 shadow-sm p-8 group hover:border-brand transition-all relative">
            <div class="flex items-center gap-3 mb-8">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i class="fas fa-chart-pie text-xs"></i>
                </div>
                <div>
                    <h3 class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1 text-left">Revenue Stream</h3>
                    <h2 class="text-sm font-black text-gray-900 text-left">Income Sources</h2>
                </div>
            </div>
            <div class="relative h-[300px]">
                <?php if (empty($monthlyReport['income_by_category']) || array_sum(array_column($monthlyReport['income_by_category'], 'total')) == 0): ?>
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-300">
                        <i class="fas fa-chart-pie text-5xl mb-4 opacity-20"></i>
                        <p class="text-xs font-bold uppercase tracking-widest">No Income Data</p>
                    </div>
                <?php endif; ?>
                <canvas id="incomeByCategoryChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Cash Flow History -->
    <div class="bg-white rounded-3xl border-3 border-gray-100 shadow-sm p-8 mb-10 group hover:border-brand transition-all">
        <div class="flex items-center gap-3 mb-8">
            <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <i class="fas fa-chart-line text-xs"></i>
            </div>
            <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest">Monthly Cash Flow Trend</h3>
        </div>
        <div class="relative h-[400px]">
            <canvas id="monthlyCashFlowChart"></canvas>
        </div>
    </div>

    <!-- Recent Activity Ledger -->
    <div class="bg-white rounded-3xl border-3 border-gray-100 shadow-sm overflow-hidden mb-10">
        <div class="p-8 border-b border-gray-100 flex justify-between items-center">
            <h2 class="text-sm font-black text-gray-900 uppercase tracking-widest">Recent Activity Ledger</h2>
            <a href="transactions.php" class="text-[10px] font-black text-brand uppercase tracking-widest hover:text-black transition-colors flex items-center gap-2">
                View Transaction Console <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Timeline</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Allocation</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Class</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Value</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php if (empty($dashboardData['recent_transactions'])): ?>
                    <tr><td colspan="4" class="px-8 py-20 text-center text-gray-400 font-bold text-sm">No recent activity detected.</td></tr>
                    <?php else: ?>
                    <?php foreach ($dashboardData['recent_transactions'] as $t): ?>
                    <tr class="hover:bg-gray-50/50 transition-all border-l-4 border-l-transparent <?php echo $t['type'] === 'income' ? 'hover:border-l-emerald-500' : 'hover:border-l-rose-500'; ?>">
                        <td class="px-8 py-6">
                            <div class="text-sm font-black text-gray-900 mb-0.5"><?php echo date('M d, Y', strtotime($t['transaction_date'])); ?></div>
                            <div class="text-[9px] font-bold text-gray-400 uppercase tracking-widest"><?php echo date('h:i A', strtotime($t['created_at'] ?? 'now')); ?></div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="text-sm font-black text-gray-800"><?php echo htmlspecialchars($t['description']); ?></div>
                            <div class="text-[10px] font-bold text-gray-400"><?php echo htmlspecialchars($t['category_name'] ?? 'Uncategorized'); ?></div>
                        </td>
                        <td class="px-8 py-6">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-tighter <?php echo $t['type'] === 'income' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-rose-50 text-rose-700 border border-rose-100'; ?>">
                                <?php echo $t['type']; ?>
                            </span>
                        </td>
                        <td class="px-8 py-6 text-right font-black amount <?php echo $t['type'] === 'income' ? 'text-emerald-600' : 'text-rose-600'; ?>">
                            <?php echo ($t['type'] === 'income' ? '+' : '-') . CURRENCY_SYMBOL . number_format($t['amount'], 2); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
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
        type: 'pie',
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
        type: 'pie',
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