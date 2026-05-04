<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Employee.php';

$auth = new Auth();
$auth->requireLogin();

$page_title = 'Pending Salaries';
include 'includes/header.php';

$employee = new Employee();
$pending_salaries = $employee->getPendingSalaries();

?>
<div class="page-animate w-full py-8 px-4">
    <!-- Header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-clock text-amber-500"></i> Pending Salary Payments
            </h1>
            <p class="text-gray-500 mt-1">Review and process unpaid employee salaries.</p>
        </div>
    </div>

    <!-- Stats or Info Alert -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="card-premium p-6 flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center text-xl shadow-sm">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Total Pending</p>
                <h3 class="text-2xl font-bold text-gray-800"><?php echo count($pending_salaries); ?> Records</h3>
            </div>
        </div>
    </div>

    <!-- Search / Filter Area -->
    <div class="card-premium mb-8">
        <div class="p-4 md:p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-search"></i>
                    </div>
                    <input type="text" class="input-premium w-full pl-10" id="pendingSearchInput" placeholder="Search by name, ID or period...">
                </div>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="card-premium overflow-hidden">
        <div class="bg-gray-50 p-4 border-b border-gray-100 flex justify-between items-center">
            <h5 class="text-sm font-bold text-gray-700 m-0 uppercase tracking-wider">Unpaid Records</h5>
        </div>
        <div class="p-0">
            <?php if (empty($pending_salaries)): ?>
                <div class="text-center py-16 text-gray-500">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-gray-100 shadow-sm">
                        <i class="fas fa-check-double text-3xl text-green-500"></i>
                    </div>
                    <h4 class="text-lg font-bold text-gray-800 mb-2">Everything Paid!</h4>
                    <p class="text-sm text-gray-500">There are no pending salary payments at the moment.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto hide-dt-search">
                    <table id="pendingSalariesTable" class="display stripe hover w-full whitespace-nowrap text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200 text-xs uppercase tracking-wider text-gray-500 font-semibold">
                                <th class="px-6 py-4">Employee</th>
                                <th class="px-6 py-4">Period</th>
                                <th class="px-6 py-4">Year</th>
                                <th class="px-6 py-4 text-right">Amount</th>
                                <th class="px-6 py-4 text-center no-print">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            <?php foreach ($pending_salaries as $s): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 rounded-full bg-brand/10 text-brand flex items-center justify-center font-bold mr-3 shadow-sm border border-brand/5">
                                                <?php echo substr(htmlspecialchars($s['first_name'] ?? 'U'), 0, 1) . substr(htmlspecialchars($s['last_name'] ?? 'N'), 0, 1); ?>
                                            </div>
                                            <div>
                                                <strong class="text-gray-800 block text-sm"><?php echo htmlspecialchars(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? '')); ?></strong>
                                                <span class="text-gray-400 text-xs font-medium">ID: <?php echo htmlspecialchars($s['employee_id'] ?? ''); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                            <i class="far fa-calendar-alt mr-1.5"></i> <?php echo date('F', mktime(0, 0, 0, $s['month'], 1)); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600 font-bold">
                                        <?php echo $s['year']; ?>
                                    </td>
                                    <td class="px-6 py-4 text-right font-bold text-gray-900">
                                        <?php echo CURRENCY_SYMBOL; ?> <?php echo number_format($s['amount'], 2); ?>
                                    </td>
                                    <td class="px-6 py-4 text-center no-print">
                                        <div class="flex items-center justify-center gap-3">
                                            <a href="pay_salary.php?id=<?php echo $s['id']; ?>" class="flex items-center gap-1.5 px-4 py-1.5 rounded-xl bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all duration-200 text-xs font-bold border border-emerald-100 shadow-sm">
                                                <i class="fas fa-money-bill-wave"></i> Process Payment
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
<?php include 'includes/footer.php'; ?>