<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Report.php';
require_once 'classes/Employee.php';

$auth = new Auth();
$auth->requireLogin();

$report = new Report();
$employee_obj = new Employee();

$selected_employee_id = isset($_GET['employee_id']) ? intval($_GET['employee_id']) : null;
$selected_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

$all_employees = $employee_obj->getAllEmployees();
$salary_history = [];
$employee_details = null;

if ($selected_employee_id) {
    $salary_history = $report->getEmployeeSalaryReport($selected_employee_id, $selected_year);
    // Find employee details from all_employees array
    foreach ($all_employees as $emp) {
        if ($emp['id'] == $selected_employee_id) {
            $employee_details = $emp;
            break;
        }
    }
}

$page_title = 'Salary Report';
include 'includes/header.php';
?>

<div class="page-animate w-full px-4">
    <!-- Header Section -->
    <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div class="relative">
            <div class="flex items-center gap-4 mb-2">
                <h1 class="text-4xl font-black text-gray-900 tracking-tight m-0 flex items-center gap-3">
                    Salary <span class="text-brand">Report</span>
                </h1>
                <span class="bg-gray-900 text-white text-[10px] font-black px-2.5 py-1 rounded-md shadow-lg shadow-gray-900/20 uppercase tracking-tighter">
                    Employee Archive
                </span>
            </div>
            <p class="text-gray-500 font-medium text-sm m-0 text-left">
                Track historical disbursement data and compensation trends for individual staff members.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="h-11 px-6 bg-white text-gray-900 border-3 border-gray-100 rounded-xl font-bold text-xs uppercase tracking-widest hover:border-black transition-all flex items-center gap-2 shadow-sm">
                <i class="fas fa-print text-[10px]"></i> Print Dossier
            </button>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-3xl md:rounded-[2rem] border-3 border-gray-100 shadow-sm p-6 md:p-8 mb-10">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6 items-end">
            <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 text-left block">Select Employee</label>
                <div class="relative">
                    <i class="fas fa-user-tie absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <select name="employee_id" class="input-premium w-full pl-10" required>
                        <option value="">Choose Employee...</option>
                        <?php foreach ($all_employees as $emp): ?>
                            <option value="<?php echo $emp['id']; ?>" <?php echo $selected_employee_id == $emp['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name'] . ' (' . $emp['employee_id'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 text-left block">Fiscal Year</label>
                <div class="relative">
                    <i class="fas fa-calendar-alt absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <select name="year" class="input-premium w-full pl-10">
                        <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                            <option value="<?php echo $y; ?>" <?php echo $selected_year == $y ? 'selected' : ''; ?>>
                                <?php echo $y; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <button type="submit" class="h-[52px] bg-brand text-white rounded-xl font-black text-xs uppercase tracking-widest hover:bg-black transition-all shadow-xl shadow-brand/20 flex items-center justify-center gap-2">
                <i class="fas fa-search"></i> Generate Report
            </button>
        </form>
    </div>

    <?php if ($selected_employee_id && $employee_details): ?>
        <!-- Statistics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="bg-gray-900 rounded-3xl md:rounded-[2rem] p-6 md:p-8 text-white relative overflow-hidden group">
                <div class="absolute -right-4 -bottom-4 text-white/5 text-8xl group-hover:scale-110 transition-transform duration-500">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="relative z-10">
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-brand-light/50 mb-2">Total Paid (<?php echo $selected_year; ?>)</p>
                    <?php 
                        $total_paid = array_sum(array_column($salary_history, 'amount'));
                        $payment_count = count($salary_history);
                        $avg_paid = $payment_count > 0 ? $total_paid / $payment_count : 0;
                    ?>
                    <h2 class="text-3xl font-black amount tracking-tighter">
                        <?php echo CURRENCY_SYMBOL . number_format($total_paid, 2); ?>
                    </h2>
                </div>
            </div>

            <div class="bg-white rounded-3xl md:rounded-[2rem] border-3 border-gray-100 p-6 md:p-8 shadow-sm group hover:border-brand transition-all">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-2">Average Monthly</p>
                <h2 class="text-3xl font-black text-gray-900 amount tracking-tighter">
                    <?php echo CURRENCY_SYMBOL . number_format($avg_paid, 2); ?>
                </h2>
            </div>

            <div class="bg-white rounded-3xl md:rounded-[2rem] border-3 border-gray-100 p-6 md:p-8 shadow-sm group hover:border-brand transition-all">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-2">Disbursement Count</p>
                <h2 class="text-3xl font-black text-gray-900 amount tracking-tighter">
                    <?php echo $payment_count; ?> <span class="text-xs text-gray-400 uppercase tracking-widest">Months</span>
                </h2>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
            <!-- Salary History Table -->
            <div class="lg:col-span-2 bg-white rounded-3xl md:rounded-[2rem] border-3 border-gray-100 shadow-sm overflow-hidden">
                <div class="p-6 md:p-8 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-xs font-black text-gray-900 uppercase tracking-widest flex items-center gap-2 text-left">
                        <i class="fas fa-list-ul text-brand"></i> Payment History Archive
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50">
                                <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Period</th>
                                <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Status</th>
                                <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php if (empty($salary_history)): ?>
                                <tr><td colspan="3" class="px-8 py-20 text-center text-gray-400 font-bold">No disbursement records found for this fiscal year.</td></tr>
                            <?php else: ?>
                                <?php foreach ($salary_history as $row): ?>
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="px-8 py-6">
                                            <div class="text-sm font-black text-gray-900"><?php echo date('F Y', mktime(0, 0, 0, $row['month'], 1, $row['year'])); ?></div>
                                            <div class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">Ref: #SLR-<?php echo str_pad($row['id'], 5, '0', STR_PAD_LEFT); ?></div>
                                        </td>
                                        <td class="px-8 py-6 text-center">
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest <?php echo $row['status'] === 'paid' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-amber-50 text-amber-600 border border-amber-100'; ?>">
                                                <span class="w-1.5 h-1.5 rounded-full <?php echo $row['status'] === 'paid' ? 'bg-emerald-500' : 'bg-amber-500 animate-pulse'; ?>"></span>
                                                <?php echo $row['status']; ?>
                                            </span>
                                        </td>
                                        <td class="px-8 py-6 text-right font-black amount text-gray-900">
                                            <?php echo CURRENCY_SYMBOL . number_format($row['amount'], 2); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Employee Profile Sidebar -->
            <div class="space-y-8">
                <div class="bg-white rounded-[2rem] border-3 border-gray-100 shadow-sm p-8 text-center">
                    <div class="w-24 h-24 rounded-3xl bg-gray-900 text-white flex items-center justify-center mx-auto mb-6 text-3xl font-black border-4 border-white shadow-2xl shadow-gray-900/10">
                        <?php echo strtoupper(substr($employee_details['first_name'], 0, 1)); ?>
                    </div>
                    <h4 class="text-xl font-black text-gray-900 leading-tight mb-1"><?php echo htmlspecialchars($employee_details['first_name'] . ' ' . $employee_details['last_name']); ?></h4>
                    <p class="text-[10px] font-black text-brand uppercase tracking-widest mb-6"><?php echo htmlspecialchars($employee_details['position']); ?></p>
                    
                    <div class="grid grid-cols-2 gap-4 text-left border-t-2 border-gray-50 pt-6">
                        <div>
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Emp ID</p>
                            <p class="text-xs font-black text-gray-700"><?php echo htmlspecialchars($employee_details['employee_id']); ?></p>
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Base Salary</p>
                            <p class="text-xs font-black text-emerald-600 amount"><?php echo CURRENCY_SYMBOL . number_format($employee_details['monthly_salary'], 2); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl md:rounded-[2rem] border-3 border-gray-100 shadow-sm p-6 md:p-8 group hover:border-brand transition-all">
                    <h3 class="text-xs font-black text-gray-900 uppercase tracking-widest mb-6 flex items-center gap-2 text-left">
                        <i class="fas fa-chart-line text-brand"></i> compensation Trend
                    </h3>
                    <div class="h-48 relative">
                        <canvas id="salaryTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    <?php elseif ($selected_employee_id): ?>
        <div class="bg-white rounded-[2rem] border-3 border-gray-100 shadow-sm p-20 text-center">
            <div class="w-20 h-20 bg-gray-50 rounded-3xl flex items-center justify-center mx-auto mb-6 text-gray-300">
                <i class="fas fa-user-slash text-3xl"></i>
            </div>
            <h3 class="text-xl font-black text-gray-900 mb-2">Employee Not Found</h3>
            <p class="text-gray-400 font-bold max-w-xs mx-auto">The requested record could not be located in our active directory.</p>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-[2.5rem] border-3 border-gray-100 shadow-sm p-20 text-center border-dashed">
            <div class="w-24 h-24 bg-brand/5 rounded-full flex items-center justify-center mx-auto mb-8 animate-bounce">
                <i class="fas fa-fingerprint text-4xl text-brand"></i>
            </div>
            <h3 class="text-2xl font-black text-gray-900 mb-4 tracking-tight">Identity Required</h3>
            <p class="text-gray-400 font-bold max-w-sm mx-auto leading-relaxed">Please select an employee and fiscal period above to generate a comprehensive compensation report.</p>
        </div>
    <?php endif; ?>
</div>

<?php if ($selected_employee_id && !empty($salary_history)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('salaryTrendChart').getContext('2d');
    
    // Prepare data for the chart (monthly labels and amounts)
    const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    const chartData = Array(12).fill(0);
    
    <?php foreach ($salary_history as $row): ?>
        chartData[<?php echo $row['month']; ?> - 1] = <?php echo $row['amount']; ?>;
    <?php endforeach; ?>

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: 'Salary Paid',
                data: chartData,
                borderColor: '#764ba2',
                backgroundColor: 'rgba(118, 75, 162, 0.1)',
                borderWidth: 4,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#764ba2',
                pointBorderWidth: 2,
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { display: false },
                    ticks: { display: false }
                },
                x: {
                    grid: { display: false },
                    ticks: { 
                        font: { size: 9, weight: 'bold' },
                        color: '#9ca3af'
                    }
                }
            }
        }
    });
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
