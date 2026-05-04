<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Employee.php';

session_start();

$auth = new Auth();
$auth->requireLogin();

$employee = new Employee();
$error = '';
$success = '';

// Get all employees for dropdown (must be before form handling)
$employees = $employee->getAllEmployees();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the numeric employee id from the employee_id (string) selected in the form
    $employee_id_str = trim($_POST['employee_id'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);
    $month = intval($_POST['month'] ?? 0);
    $year = intval($_POST['year'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');

    // Find the numeric id for the given employee_id string
    $numeric_employee_id = null;
    foreach ($employees as $emp) {
        if ($emp['employee_id'] === $employee_id_str) {
            $numeric_employee_id = $emp['id'];
            break;
        }
    }

    if (!$numeric_employee_id || $amount <= 0 || $month < 1 || $month > 12 || $year < 2020 || $year > date('Y')) {
        $error = 'Please fill all fields correctly.';
    } else {
        $result = $employee->addSalaryPayment($numeric_employee_id, $month, $year, $amount, $notes);
        if ($result) {
            // Store submitted salary info in session for display
            $_SESSION['success'] = 'Salary record added successfully.';
            $_SESSION['submitted_salary'] = [
                'employee' => array_values(array_filter($employees, function ($emp) use ($numeric_employee_id) {
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
            $error = 'Failed to add salary record. It may already exist for this period.';
        }
    }
}
$page_title = 'Add Salary Information';
?>
<?php
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="page-animate w-full max-w-4xl mx-auto px-6">
    <!-- Header Section -->
    <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div class="relative">
            <div class="flex items-center gap-4 mb-2">
                <h1 class="text-4xl font-black text-gray-900 tracking-tight m-0 flex items-center gap-3">
                    Compensation
                </h1>
                <span class="bg-blue-600 text-white text-[10px] font-black px-2.5 py-1 rounded-md shadow-lg shadow-blue-600/20 uppercase tracking-tighter">
                    Remuneration Entry
                </span>
            </div>
            <p class="text-gray-500 font-medium text-sm m-0">
                Initialize a new salary disbursement record for verified personnel.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="salaries.php" class="h-11 px-5 bg-white text-gray-900 border-3 border-gray-100 rounded-xl font-bold text-xs uppercase tracking-widest hover:border-black transition-all flex items-center gap-2 shadow-sm">
                <i class="fas fa-arrow-left text-[10px]"></i> Discard
            </a>
        </div>
    </div>

    <!-- Form Module -->
    <div class="bg-white rounded-[40px] border-3 border-gray-100 shadow-2xl shadow-black/5 overflow-hidden mb-20">
        <div class="p-8 md:p-12">
            <?php if ($error): ?>
                <div class="bg-rose-50 text-rose-700 p-5 rounded-2xl border-2 border-rose-100 mb-8 flex items-center">
                    <i class="fas fa-exclamation-circle text-rose-500 text-xl mr-4"></i>
                    <span class="font-black text-xs uppercase tracking-tight"><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-10" novalidate autocomplete="off">
                <!-- Personnel Selection -->
                <div>
                    <label for="employee_id" class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block ml-1">Verified Personnel *</label>
                    <div class="relative">
                        <select class="w-full h-14 px-4 bg-gray-50 border-3 border-gray-200 text-gray-900 rounded-2xl focus:bg-white focus:border-brand outline-none transition-all font-bold text-sm appearance-none cursor-pointer" id="employee_id" name="employee_id" required>
                            <option value="">Select Recipient</option>
                            <?php foreach ($employees as $emp): ?>
                                <option value="<?php echo htmlspecialchars($emp['employee_id']); ?>"><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name'] . ' (' . $emp['employee_id'] . ')'); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                            <i class="fas fa-chevron-down text-[10px]"></i>
                        </div>
                    </div>
                </div>

                <!-- Financial Metrics -->
                <div>
                    <label for="amount" class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block ml-1">Disbursement Value (<?php echo CURRENCY_SYMBOL; ?>) *</label>
                    <input type="number" step="0.01" class="w-full h-14 px-4 bg-gray-50 border-3 border-gray-200 text-gray-900 rounded-2xl focus:bg-white focus:border-brand outline-none transition-all font-black text-sm amount" id="amount" name="amount" required placeholder="0.00">
                </div>

                <!-- Temporal Allocation -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <label for="month" class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block ml-1">Allocation Month *</label>
                        <div class="relative">
                            <select class="w-full h-14 px-4 bg-gray-50 border-3 border-gray-200 text-gray-900 rounded-2xl focus:bg-white focus:border-brand outline-none transition-all font-bold text-sm appearance-none cursor-pointer" id="month" name="month" required>
                                <option value="">Select Period</option>
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?php echo $m; ?>"><?php echo date('F', mktime(0, 0, 0, $m, 1)); ?></option>
                                <?php endfor; ?>
                            </select>
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                                <i class="fas fa-chevron-down text-[10px]"></i>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label for="year" class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block ml-1">Fiscal Year *</label>
                        <div class="relative">
                            <select class="w-full h-14 px-4 bg-gray-50 border-3 border-gray-200 text-gray-900 rounded-2xl focus:bg-white focus:border-brand outline-none transition-all font-bold text-sm appearance-none cursor-pointer" id="year" name="year" required>
                                <option value="">Select Year</option>
                                <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                    <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                                <?php endfor; ?>
                            </select>
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                                <i class="fas fa-calendar-alt text-[10px]"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Meta Documentation -->
                <div>
                    <label for="notes" class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block ml-1">Administrative Documentation</label>
                    <textarea class="w-full p-4 bg-gray-50 border-3 border-gray-200 text-gray-900 rounded-2xl focus:bg-white focus:border-brand outline-none transition-all font-bold text-sm min-h-[120px]" id="notes" name="notes" placeholder="Optional meta data..."></textarea>
                </div>

                <!-- Execution Layer -->
                <div class="flex justify-end pt-10 border-t border-gray-100">
                    <button type="submit" class="w-full sm:w-64 h-16 bg-black text-white rounded-2xl font-black text-xs uppercase tracking-[0.2em] hover:bg-gray-800 transition-all shadow-2xl shadow-black/20 flex items-center justify-center gap-3">
                        <i class="fas fa-paper-plane"></i> Initialize Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>