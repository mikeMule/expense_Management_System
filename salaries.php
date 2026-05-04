<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';

$auth = new Auth();
$auth->requireLogin();

require_once 'classes/Employee.php';

$page_title = 'Salary Management';
include 'includes/header.php';

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

?>
<div class="page-animate w-full">
    <!-- Header Section -->
    <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div class="relative">
            <div class="flex items-center gap-4 mb-2">
                <h1 class="text-4xl font-black text-gray-900 tracking-tight m-0 flex items-center gap-3">
                    Salaries
                </h1>
                <span class="bg-indigo-600 text-white text-[10px] font-black px-2.5 py-1 rounded-md shadow-lg shadow-indigo-500/20 uppercase tracking-tighter">
                    <?php echo count($salary_payments); ?> Records
                </span>
            </div>
            <p class="text-gray-500 font-medium text-sm m-0">
                Track and manage monthly payroll disbursements and pending obligations.
            </p>
        </div>
        <div class="flex flex-col sm:flex-row items-center gap-3">
            <button type="button" class="h-11 px-5 bg-white text-emerald-600 border-3 border-gray-100 rounded-xl font-bold text-xs uppercase tracking-widest hover:border-emerald-500 transition-all flex items-center gap-2 shadow-sm w-full sm:w-auto justify-center" id="openAddSalary2025Modal">
                <i class="fas fa-plus text-[10px]"></i> Add Salary
            </button>
            <button type="button" class="h-11 px-6 bg-brand text-white rounded-xl font-black text-xs uppercase tracking-widest hover:bg-black transition-all flex items-center gap-2 shadow-xl shadow-brand/20 w-full sm:w-auto justify-center" id="openGenerateSalary2025Modal">
                <i class="fas fa-bolt text-[10px]"></i> Bulk Generate
            </button>
        </div>
    </div>

    <!-- Professional Filters Section -->
    <div class="bg-white rounded-3xl border-3 border-gray-100 shadow-sm mb-10 overflow-hidden">
        <div class="p-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-8 h-8 rounded-lg bg-brand/10 text-brand flex items-center justify-center">
                    <i class="fas fa-filter text-xs"></i>
                </div>
                <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest">Filter Records</h3>
            </div>

            <form method="GET" action="salaries.php" id="filterForm">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 items-end">
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block ml-1">Payment Month</label>
                        <div class="relative group">
                            <i class="far fa-calendar-alt absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xs group-focus-within:text-brand transition-colors"></i>
                            <select name="month" class="w-full h-12 pl-12 pr-10 bg-gray-50 border-3 border-gray-50 rounded-2xl text-sm font-bold text-gray-800 focus:bg-white focus:border-brand focus:ring-0 transition-all outline-none appearance-none cursor-pointer">
                                <option value="all" <?php echo is_null($filter_month) ? 'selected' : ''; ?>>All Months</option>
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?php echo $m; ?>" <?php echo $filter_month == $m ? 'selected' : ''; ?>><?php echo date('F', mktime(0, 0, 0, $m, 1)); ?></option>
                                <?php endfor; ?>
                            </select>
                            <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 text-[10px] pointer-events-none"></i>
                        </div>
                    </div>

                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block ml-1">Payment Year</label>
                        <div class="relative group">
                            <i class="fas fa-history absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xs group-focus-within:text-brand transition-colors"></i>
                            <select name="year" class="w-full h-12 pl-12 pr-10 bg-gray-50 border-3 border-gray-50 rounded-2xl text-sm font-bold text-gray-800 focus:bg-white focus:border-brand focus:ring-0 transition-all outline-none appearance-none cursor-pointer">
                                <option value="all" <?php echo is_null($filter_year) ? 'selected' : ''; ?>>All Years</option>
                                <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                    <option value="<?php echo $y; ?>" <?php echo $filter_year == $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                                <?php endfor; ?>
                            </select>
                            <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 text-[10px] pointer-events-none"></i>
                        </div>
                    </div>

                    <div class="lg:col-span-2 flex gap-3">
                        <div class="flex-grow">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block ml-1">Search Employee</label>
                            <div class="relative group">
                                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xs group-focus-within:text-brand transition-colors"></i>
                                <input type="text" name="search" placeholder="Search name, ID or notes..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                                       class="w-full h-12 pl-12 pr-4 bg-gray-50 border-3 border-gray-50 rounded-2xl text-sm font-bold text-gray-800 focus:bg-white focus:border-brand focus:ring-0 transition-all outline-none placeholder:text-gray-400 placeholder:font-medium">
                            </div>
                        </div>
                        <button type="submit" class="h-12 w-12 bg-gray-900 text-white rounded-2xl flex items-center justify-center hover:bg-brand transition-colors shadow-lg">
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Salary Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <div class="bg-white rounded-3xl border-3 border-gray-100 shadow-sm p-6 group hover:border-blue-500 transition-all">
            <div class="flex items-center justify-between mb-4">
                <div class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Monthly Budget</div>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center group-hover:bg-blue-500 group-hover:text-white transition-colors">
                    <i class="fas fa-money-check-alt"></i>
                </div>
            </div>
            <div class="text-xl font-black text-gray-900 amount">
                <?php echo CURRENCY_SYMBOL . number_format($total_monthly_budget, 2); ?>
            </div>
        </div>

        <div class="bg-white rounded-3xl border-3 border-gray-100 shadow-sm p-6 group hover:border-amber-500 transition-all">
            <div class="flex items-center justify-between mb-4">
                <div class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Pending Payments</div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:bg-amber-500 group-hover:text-white transition-colors">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            <div class="text-xl font-black text-amber-600 amount">
                <?php echo count($pending_salaries); ?>
            </div>
        </div>

        <div class="bg-white rounded-3xl border-3 border-gray-100 shadow-sm p-6 group hover:border-emerald-500 transition-all">
            <div class="flex items-center justify-between mb-4">
                <div class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Paid This Month</div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:bg-emerald-500 group-hover:text-white transition-colors">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="text-xl font-black text-emerald-600 amount">
                <?php echo count(array_filter($salary_payments, function ($s) { return $s['status'] == 'paid'; })); ?>
            </div>
        </div>

        <div class="bg-white rounded-3xl border-3 border-gray-100 shadow-sm p-6 group hover:border-brand transition-all">
            <div class="flex items-center justify-between mb-4">
                <div class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Total Records</div>
                <div class="w-10 h-10 rounded-xl bg-brand/10 text-brand flex items-center justify-center group-hover:bg-brand group-hover:text-white transition-colors">
                    <i class="fas fa-list"></i>
                </div>
            </div>
            <div class="text-xl font-black text-brand amount">
                <?php echo count($salary_payments); ?>
            </div>
        </div>
    </div>

    <!-- Pending Salaries Alert -->
    <?php if (!empty($pending_salaries)): ?>
        <div class="bg-white rounded-3xl border-3 border-amber-200 p-8 mb-10 shadow-xl shadow-amber-500/5 relative overflow-hidden">
            <div class="absolute top-0 right-0 p-4 opacity-5 pointer-events-none">
                <i class="fas fa-exclamation-triangle text-8xl text-amber-500"></i>
            </div>
            <h5 class="text-xs font-black text-amber-600 uppercase tracking-widest mb-2 flex items-center gap-2">
                <i class="fas fa-triangle-exclamation"></i> Action Required
            </h5>
            <h2 class="text-lg font-black text-gray-900 mb-6">Pending Salary Payments</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach (array_slice($pending_salaries, 0, 6) as $pending): ?>
                    <div class="bg-amber-50/50 rounded-2xl p-4 border border-amber-100 flex justify-between items-center group hover:bg-amber-100/50 transition-colors">
                        <div>
                            <div class="text-sm font-black text-gray-800 mb-0.5"><?php echo htmlspecialchars($pending['first_name'] . ' ' . $pending['last_name']); ?></div>
                            <div class="text-[10px] font-bold text-amber-600"><?php echo CURRENCY_SYMBOL . number_format($pending['amount'], 2); ?></div>
                        </div>
                        <a href="confirm_pay_salary.php?id=<?php echo $pending['id']; ?>" class="h-9 px-4 bg-emerald-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-black transition-all flex items-center shadow-lg shadow-emerald-600/10">
                            Pay
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Desktop View: Salary Table -->
    <div class="hidden md:block bg-white rounded-3xl border-3 border-gray-100 shadow-sm overflow-hidden mb-10">
        <div class="p-8 border-b border-gray-100 flex justify-between items-center">
            <h2 class="text-sm font-black text-gray-900 uppercase tracking-widest">Payment History</h2>
            <a href="export_csv.php?<?php echo http_build_query($_GET); ?>" class="h-9 px-4 bg-gray-50 text-emerald-600 border border-gray-100 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-emerald-600 hover:text-white transition-all flex items-center gap-2">
                <i class="fas fa-file-csv"></i> Export CSV
            </a>
        </div>
        <div class="overflow-x-auto">
            <table id="salaryTable" class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Employee</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Period & Date</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Amount</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php if (empty($salary_payments)): ?>
                    <tr><td colspan="4" class="px-8 py-20 text-center text-gray-400 font-bold text-sm">No records found.</td></tr>
                    <?php else: ?>
                    <?php foreach ($salary_payments as $s): ?>
                    <tr class="hover:bg-gray-50/50 transition-all border-l-4 border-l-transparent hover:border-l-indigo-600">
                        <td class="px-8 py-6">
                            <div class="text-sm font-black text-gray-900 mb-0.5"><?php echo htmlspecialchars(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? '')); ?></div>
                            <div class="text-[10px] font-bold text-gray-400"><?php echo htmlspecialchars($s['employee_id'] ?? ''); ?></div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="text-sm font-black text-gray-800"><?php echo date('F Y', mktime(0, 0, 0, $s['month'], 1, $s['year'])); ?></div>
                            <div class="text-[9px] font-bold <?php echo !empty($s['payment_date']) ? 'text-gray-400' : 'text-amber-600'; ?> mt-1 uppercase tracking-widest">
                                <?php echo !empty($s['payment_date']) ? date('M d, Y', strtotime($s['payment_date'])) : 'Pending Payment'; ?>
                            </div>
                        </td>
                        <td class="px-8 py-6 text-right font-black text-gray-900 amount">
                            <?php echo CURRENCY_SYMBOL . number_format($s['amount'], 2); ?>
                        </td>
                        <td class="px-8 py-6">
                            <div class="flex items-center justify-center gap-2">
                                <?php if (($s['status'] ?? '') !== 'paid'): ?>
                                    <a href="confirm_pay_salary.php?id=<?php echo $s['id']; ?>" class="h-9 px-4 bg-emerald-50 text-emerald-600 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-emerald-600 hover:text-white transition-all flex items-center gap-2">
                                        Pay
                                    </a>
                                <?php else: ?>
                                    <span class="h-9 px-4 bg-emerald-50 text-emerald-600 rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-2 border border-emerald-100">
                                        <i class="fas fa-check-circle"></i> Paid
                                    </span>
                                <?php endif; ?>
                                <a href="edit_salary.php?id=<?php echo $s['id']; ?>" class="h-9 w-9 rounded-xl bg-gray-50 text-gray-400 flex items-center justify-center hover:bg-amber-500 hover:text-white transition-all border border-gray-100">
                                    <i class="fas fa-edit text-[10px]"></i>
                                </a>
                                <button type="button" class="h-9 w-9 rounded-xl bg-gray-50 text-gray-400 flex items-center justify-center hover:bg-rose-600 hover:text-white transition-all border border-gray-100 btn-delete-salary" data-delete-url="delete_salary.php?id=<?php echo $s['id']; ?>" data-item-name="Salary: <?php echo htmlspecialchars(addslashes(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? ''))); ?>">
                                    <i class="fas fa-trash-alt text-[10px]"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile View: Hybrid Cards -->
    <div class="md:hidden space-y-4 mb-10">
        <?php foreach ($salary_payments as $s): ?>
        <div class="bg-white rounded-3xl border-3 border-gray-100 shadow-sm p-6 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-2 h-full <?php echo ($s['status'] ?? '') === 'paid' ? 'bg-emerald-500' : 'bg-amber-500'; ?>"></div>
            
            <div class="flex justify-between items-start mb-6">
                <div>
                    <div class="text-base font-black text-gray-900"><?php echo htmlspecialchars(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? '')); ?></div>
                    <div class="text-[11px] font-bold text-gray-400 uppercase tracking-widest"><?php echo htmlspecialchars($s['employee_id'] ?? ''); ?></div>
                </div>
                <div class="text-right">
                    <div class="text-base font-black text-gray-900 amount"><?php echo CURRENCY_SYMBOL . number_format($s['amount'], 2); ?></div>
                    <div class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Amount</div>
                </div>
            </div>

            <div class="bg-gray-50/50 p-4 rounded-2xl border border-gray-100 mb-6">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-[9px] font-black text-gray-400 uppercase">Period</span>
                    <span class="text-xs font-black text-gray-800"><?php echo date('F Y', mktime(0, 0, 0, $s['month'], 1, $s['year'])); ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-[9px] font-black text-gray-400 uppercase">Status</span>
                    <span class="text-[10px] font-black <?php echo ($s['status'] ?? '') === 'paid' ? 'text-emerald-600' : 'text-amber-600'; ?> uppercase tracking-tighter">
                        <?php echo ($s['status'] ?? '') === 'paid' ? 'Completed' : 'Pending Action'; ?>
                    </span>
                </div>
            </div>

            <div class="flex gap-2">
                <?php if (($s['status'] ?? '') !== 'paid'): ?>
                    <a href="confirm_pay_salary.php?id=<?php echo $s['id']; ?>" class="flex-1 h-11 bg-emerald-600 text-white rounded-xl font-black text-[10px] uppercase tracking-widest flex items-center justify-center gap-2">
                        <i class="fas fa-money-bill-wave"></i> Process Payment
                    </a>
                <?php endif; ?>
                <a href="edit_salary.php?id=<?php echo $s['id']; ?>" class="w-11 h-11 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center border border-amber-100">
                    <i class="fas fa-edit"></i>
                </a>
                <button type="button" class="w-11 h-11 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center border border-rose-100 btn-delete-salary" data-delete-url="delete_salary.php?id=<?php echo $s['id']; ?>" data-item-name="Salary Record">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

                    <!-- Add Salary Modal -->
                    <div id="addSalary2025Modal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
                        <!-- Backdrop -->
                        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity opacity-0" onclick="closeModal('addSalary2025Modal')"></div>
                        
                        <!-- Modal Content -->
                        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto relative z-10 mx-4 scale-95 opacity-0 transition-all duration-300">
                            <div class="flex items-center justify-between p-6 border-b border-gray-100 bg-gray-50/50 sticky top-0 z-20">
                                <h3 class="text-xl font-bold text-gray-800 flex items-center m-0 modal2025-title">
                                    <div class="w-10 h-10 rounded-full bg-brand/10 text-brand flex items-center justify-center mr-3">
                                        <i class="fas fa-plus-circle"></i>
                                    </div>
                                    Add Salary Information
                                </h3>
                                <button type="button" class="text-gray-400 hover:text-gray-600 w-8 h-8 rounded-full hover:bg-gray-100 flex items-center justify-center transition-colors" onclick="closeModal('addSalary2025Modal')">
                                    <i class="fas fa-times text-lg"></i>
                                </button>
                            </div>
                            
                            <form method="POST" class="p-6 md:p-8 space-y-6" novalidate autocomplete="off">
                                <?php if ($salary_error): ?>
                                    <div class="bg-red-50 text-red-700 p-4 rounded-xl border border-red-200"><i class="fas fa-exclamation-triangle me-2"></i><?php echo $salary_error; ?></div>
                                <?php endif; ?>
                                <?php if ($salary_success): ?>
                                    <div class="bg-green-50 text-green-700 p-4 rounded-xl border border-green-200"><i class="fas fa-check-circle me-2"></i><?php echo $salary_success; ?></div>
                                <?php endif; ?>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="md:col-span-2">
                                        <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-2">Employee</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-user text-gray-400"></i>
                                            </div>
                                            <select class="input-premium w-full pl-10" id="employee_id" name="employee_id" required>
                                                <option value="">Select Employee</option>
                                                <?php foreach ($employee->getAllEmployees() as $emp): ?>
                                                    <option value="<?php echo htmlspecialchars($emp['employee_id']); ?>"><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name'] . ' (' . $emp['employee_id'] . ')'); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="md:col-span-2">
                                        <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">Amount</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 font-semibold"><?php echo CURRENCY_SYMBOL; ?></span>
                                            </div>
                                            <input type="number" step="0.01" class="input-premium w-full pl-10" id="amount" name="amount" required placeholder="Enter amount">
                                        </div>
                                    </div>

                                    <div>
                                        <label for="month" class="block text-sm font-medium text-gray-700 mb-2">Month</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-calendar-alt text-gray-400"></i>
                                            </div>
                                            <select class="input-premium w-full pl-10" id="month" name="month" required>
                                                <option value="">Select Month</option>
                                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                                    <option value="<?php echo $m; ?>"><?php echo date('F', mktime(0, 0, 0, $m, 1)); ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div>
                                        <label for="year" class="block text-sm font-medium text-gray-700 mb-2">Year</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-calendar text-gray-400"></i>
                                            </div>
                                            <select class="input-premium w-full pl-10" id="year" name="year" required>
                                                <option value="">Select Year</option>
                                                <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                                    <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="md:col-span-2">
                                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes (optional)</label>
                                        <div class="relative">
                                            <div class="absolute top-3 left-3 pointer-events-none">
                                                <i class="fas fa-sticky-note text-gray-400"></i>
                                            </div>
                                            <textarea class="input-premium w-full pl-10 py-3" id="notes" name="notes" rows="2" placeholder="Add any notes..."></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-end gap-3 pt-6 mt-6 border-t border-gray-100">
                                    <button type="button" class="px-5 py-2.5 rounded-xl font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors" onclick="closeModal('addSalary2025Modal')">Cancel</button>
                                    <button type="submit" name="add_salary" class="btn-primary flex items-center gap-2"><i class="fas fa-save"></i> Save Salary</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Generate Monthly Salaries Modal -->
                    <div id="generateSalary2025Modal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
                        <!-- Backdrop -->
                        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity opacity-0" onclick="closeModal('generateSalary2025Modal')"></div>
                        
                        <!-- Modal Content -->
                        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg relative z-10 mx-4 scale-95 opacity-0 transition-all duration-300">
                            <div class="flex items-center justify-between p-6 border-b border-gray-100 bg-gray-50/50">
                                <h3 class="text-xl font-bold text-gray-800 flex items-center m-0">
                                    <div class="w-10 h-10 rounded-full bg-brand/10 text-brand flex items-center justify-center mr-3">
                                        <i class="fas fa-cogs"></i>
                                    </div>
                                    Bulk Generate Salaries
                                </h3>
                                <button type="button" class="text-gray-400 hover:text-gray-600 w-8 h-8 rounded-full hover:bg-gray-100 flex items-center justify-center transition-colors" onclick="closeModal('generateSalary2025Modal')">
                                    <i class="fas fa-times text-lg"></i>
                                </button>
                            </div>
                            
                            <form method="POST" class="p-6">
                                <p class="text-gray-600 mb-6 text-sm">Generate salary records for all active employees for a specific month.</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                    <div>
                                        <label for="gen_month" class="block text-sm font-medium text-gray-700 mb-2">Month</label>
                                        <select class="input-premium w-full" id="gen_month" name="month" required>
                                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                                <option value="<?php echo $m; ?>" <?php echo date('n') == $m ? 'selected' : ''; ?>>
                                                    <?php echo date('F (m)', mktime(0, 0, 0, $m, 1)); ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="gen_year" class="block text-sm font-medium text-gray-700 mb-2">Year</label>
                                        <select class="input-premium w-full" id="gen_year" name="year" required>
                                            <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                                <option value="<?php echo $y; ?>" <?php echo date('Y') == $y ? 'selected' : ''; ?>>
                                                    <?php echo $y; ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="bg-blue-50 text-blue-700 p-4 rounded-xl border border-blue-200 text-sm flex items-start">
                                    <i class="fas fa-info-circle mt-1 mr-2 flex-shrink-0"></i>
                                    <span>This will create salary records for all active employees. If records already exist for the selected period, they will be skipped.</span>
                                </div>
                                
                                <div class="flex justify-end gap-3 mt-8">
                                    <button type="button" class="px-5 py-2.5 rounded-xl font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors" onclick="closeModal('generateSalary2025Modal')">Cancel</button>
                                    <button type="submit" name="generate_salaries" class="btn-primary flex items-center gap-2">
                                        <i class="fas fa-bolt"></i> Generate
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Delete Confirmation Modal -->
                    <div id="deleteSalaryConfirmModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
                        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity opacity-0" onclick="closeModal('deleteSalaryConfirmModal')"></div>
                        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm relative z-10 mx-4 scale-95 opacity-0 transition-all duration-300">
                            <div class="p-6 text-center">
                                <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-exclamation-triangle text-3xl text-red-500"></i>
                                </div>
                                <h3 class="text-xl font-bold text-gray-800 mb-2">Confirm Deletion</h3>
                                <p class="text-gray-500 mb-2">Are you sure you want to delete this record?</p>
                                <p class="font-medium text-gray-800 bg-gray-50 p-2 rounded-lg" id="deleteItemName"></p>
                            </div>
                            <div class="flex border-t border-gray-100">
                                <button type="button" class="flex-1 py-3 text-gray-600 font-semibold hover:bg-gray-50 rounded-bl-2xl transition-colors" onclick="closeModal('deleteSalaryConfirmModal')">Cancel</button>
                                <a href="#" id="confirmDeleteBtn" class="flex-1 py-3 text-white bg-red-500 font-semibold hover:bg-red-600 rounded-br-2xl transition-colors text-center">Delete</a>
                            </div>
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

                        // Open Modals Logic
                        const openAddSalaryBtn = document.getElementById('openAddSalary2025Modal');
                        const openAddSalaryBtnEmpty = document.getElementById('openAddSalary2025ModalEmpty');
                        const openGenerateSalaryBtn = document.getElementById('openGenerateSalary2025Modal');
                        
                        if (openAddSalaryBtn) openAddSalaryBtn.addEventListener('click', () => openModal('addSalary2025Modal'));
                        if (openAddSalaryBtnEmpty) openAddSalaryBtnEmpty.addEventListener('click', () => openModal('addSalary2025Modal'));
                        if (openGenerateSalaryBtn) openGenerateSalaryBtn.addEventListener('click', () => openModal('generateSalary2025Modal'));

                        // Delete Modal Logic
                        document.querySelectorAll('.btn-delete-salary').forEach(btn => {
                            btn.addEventListener('click', function(e) {
                                e.preventDefault();
                                const deleteUrl = this.getAttribute('data-delete-url');
                                const itemName = this.getAttribute('data-item-name');
                                document.getElementById('confirmDeleteBtn').href = deleteUrl;
                                document.getElementById('deleteItemName').textContent = itemName;
                                openModal('deleteSalaryConfirmModal');
                            });
                        });

                        // Add Salary Modal dynamic logic
                        const employeeSelect = document.getElementById('employee_id');
                        const amountInput = document.getElementById('amount');
                        const monthSelect = document.getElementById('month');
                        const yearSelect = document.getElementById('year');
                        const modalTitle = document.querySelector('.modal2025-title');

                        function updateSalaryModalFields() {
                            const empId = employeeSelect.value;
                            if (empId && window.EMPLOYEES[empId]) {
                                const emp = window.EMPLOYEES[empId];
                                const monthName = monthSelect.options[monthSelect.selectedIndex]?.text || '';
                                const yearVal = yearSelect.value;
                                
                                modalTitle.innerHTML = `<div class="w-10 h-10 rounded-full bg-brand/10 text-brand flex items-center justify-center mr-3"><i class="fas fa-plus-circle"></i></div><span class="text-sm">Add salary to</span> <b class="mx-1">${emp.first_name}</b> <span class="text-sm font-normal text-gray-500">for ${monthName} ${yearVal}</span>`;
                                
                                amountInput.value = emp.monthly_salary;
                                amountInput.readOnly = true;
                                amountInput.classList.add('bg-gray-100', 'cursor-not-allowed');
                                
                                monthSelect.disabled = false;
                                yearSelect.disabled = false;
                                
                                const paid = window.SALARIES[empId] || {};
                                const prevMonth = monthSelect.value;
                                const prevYear = yearSelect.value;
                                
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
                                
                                let selectedYear = yearSelect.value || currentYear;
                                monthSelect.innerHTML = '<option value="">Select Month</option>';
                                for (let m = 1; m <= 12; m++) {
                                    if (!paid[`${selectedYear}-${m}`]) {
                                        const mName = new Date(selectedYear, m - 1).toLocaleString('default', { month: 'long' });
                                        monthSelect.innerHTML += `<option value="${m}"${prevMonth == m ? ' selected' : ''}>${mName}</option>`;
                                    }
                                }
                                
                                if (yearSelect.options.length === 2) yearSelect.selectedIndex = 1;
                                if (monthSelect.options.length === 2) monthSelect.selectedIndex = 1;
                                
                                if (yearSelect.options.length === 1 || monthSelect.options.length === 1) {
                                    monthSelect.disabled = true;
                                    yearSelect.disabled = true;
                                    amountInput.value = '';
                                    amountInput.readOnly = true;
                                    modalTitle.innerHTML = `<div class="w-10 h-10 rounded-full bg-yellow-100 text-yellow-600 flex items-center justify-center mr-3"><i class="fas fa-exclamation-triangle"></i></div><span class="text-sm">All months already paid for</span> <b class="ml-1">${emp.first_name}</b>`;
                                }
                            } else {
                                modalTitle.innerHTML = `<div class="w-10 h-10 rounded-full bg-brand/10 text-brand flex items-center justify-center mr-3"><i class="fas fa-plus-circle"></i></div>Add Salary Information`;
                                amountInput.value = '';
                                amountInput.readOnly = false;
                                amountInput.classList.remove('bg-gray-100', 'cursor-not-allowed');
                                monthSelect.disabled = false;
                                yearSelect.disabled = false;
                            }
                        }
                        
                        if (employeeSelect) employeeSelect.addEventListener('change', updateSalaryModalFields);
                        if (monthSelect) monthSelect.addEventListener('change', updateSalaryModalFields);
                        if (yearSelect) yearSelect.addEventListener('change', updateSalaryModalFields);
                        
                        if (openAddSalaryBtn) {
                            openAddSalaryBtn.addEventListener('click', function() {
                                setTimeout(updateSalaryModalFields, 50);
                            });
                        }
                        if (openAddSalaryBtnEmpty) {
                            openAddSalaryBtnEmpty.addEventListener('click', function() {
                                setTimeout(updateSalaryModalFields, 50);
                            });
                        }
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>