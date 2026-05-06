<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Employee.php';
require_once 'classes/Transaction.php';

$auth = new Auth();
$auth->requireLogin();

$employee = new Employee();
$transaction = new Transaction();

$payment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$payment_id) {
    $_SESSION['error'] = 'Invalid salary payment ID.';
    header('Location: salaries.php');
    exit();
}

$salary_payment = $employee->getSalaryPaymentById($payment_id);

if (!$salary_payment) {
    $_SESSION['error'] = 'Salary payment not found.';
    header('Location: salaries.php');
    exit();
}

if ($salary_payment['status'] === 'paid') {
    $_SESSION['error'] = 'This salary payment has already been marked as paid.';
    header('Location: salaries.php');
    exit();
}

// Handle payment confirmation
if ($_POST && isset($_POST['confirm_payment'])) {
    try {
        $result = $employee->markSalaryAsPaid($payment_id);

        if ($result) {
            $updated_salary = $employee->getSalaryPaymentById($payment_id);
            if ($updated_salary) {
                // Ensure there is a 'Salaries' category
                $salaries_category_id = $transaction->getOrCreateCategory('Salaries', 'expense');

                // Create a corresponding transaction
                $description = "Salary: " . $updated_salary['first_name'] . " " . $updated_salary['last_name'];
                $notes = "Payment for " . date('F Y', mktime(0, 0, 0, $updated_salary['month'], 1, $updated_salary['year'])) . "\n[salary_payment_id:{$payment_id}]";
                $transaction_date = date('Y-m-d', strtotime($updated_salary['payment_date']));

                $transaction->addTransaction(
                    'expense',
                    $salaries_category_id,
                    $updated_salary['amount'],
                    $description,
                    $transaction_date,
                    $notes
                );

                $_SESSION['success'] = 'Salary payment confirmed and transaction recorded successfully.';
            } else {
                $_SESSION['error'] = 'Could not retrieve salary details to create transaction.';
            }
        } else {
            $_SESSION['error'] = 'Failed to mark salary as paid.';
        }
    } catch (Exception $e) {
        error_log("Salary payment confirmation error: " . $e->getMessage());
        $_SESSION['error'] = 'An unexpected error occurred during salary payment confirmation.';
    }

    header('Location: salaries.php');
    exit();
}

$page_title = 'Confirm Payment';
include 'includes/header.php';
?>

<div class="page-animate w-full flex justify-center items-start min-h-screen py-12">
    <div class="w-full max-w-3xl px-4">
        <!-- Breadcrumbs -->
        <nav class="flex mb-8" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3 text-xs font-black uppercase tracking-widest">
                <li class="inline-flex items-center">
                    <a href="salaries.php" class="text-gray-400 hover:text-brand flex items-center">
                        <i class="fas fa-money-check-alt mr-2"></i> Salaries
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-300 mx-2 text-[8px]"></i>
                        <span class="text-gray-900">Confirm Payment</span>
                    </div>
                </li>
            </ol>
        </nav>

        <div class="bg-white rounded-3xl md:rounded-[2.5rem] border-3 border-gray-100 shadow-2xl overflow-hidden">
            <!-- Header Section -->
            <div class="bg-gray-900 p-6 md:p-10 relative overflow-hidden">
                <div class="absolute top-0 right-0 p-8 opacity-10">
                    <i class="fas fa-file-invoice-dollar text-8xl text-white"></i>
                </div>
                <div class="relative z-10 text-center">
                    <div class="w-20 h-20 bg-brand rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-xl shadow-brand/20 rotate-3">
                        <i class="fas fa-check-double text-3xl text-white"></i>
                    </div>
                    <h1 class="text-3xl font-black text-white tracking-tight mb-2">Final Confirmation</h1>
                    <p class="text-brand-light/60 font-bold uppercase tracking-[0.2em] text-[10px]">Salary Disbursement Protocol</p>
                </div>
            </div>

            <!-- Content Section -->
            <div class="p-6 md:p-10">
                <div class="bg-emerald-50 border-3 border-emerald-100 rounded-3xl p-6 mb-10 flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div>
                        <h4 class="text-emerald-900 font-black text-sm uppercase tracking-widest mb-1 text-left">Action Summary</h4>
                        <p class="text-emerald-700/80 text-sm font-medium leading-relaxed text-left">
                            By confirming this action, the system will mark this salary as <strong>PAID</strong>, set the timestamp, and automatically generate a new <strong>Expense Transaction</strong> for bookkeeping.
                        </p>
                    </div>
                </div>

                <!-- Info Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
                    <!-- Employee Column -->
                    <div class="space-y-6">
                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block">Employee Identity</label>
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center text-gray-400 font-black text-lg border border-gray-100">
                                    <?php echo strtoupper(substr($salary_payment['first_name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <div class="text-base font-black text-gray-900 leading-tight"><?php echo htmlspecialchars($salary_payment['first_name'] . ' ' . $salary_payment['last_name']); ?></div>
                                    <div class="text-xs font-bold text-gray-400"><?php echo htmlspecialchars($salary_payment['employee_id']); ?></div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block">Disbursement Period</label>
                            <div class="flex items-center gap-3">
                                <i class="far fa-calendar-alt text-brand text-lg"></i>
                                <span class="text-base font-black text-gray-800"><?php echo date('F Y', mktime(0, 0, 0, $salary_payment['month'], 1, $salary_payment['year'])); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Financials Column -->
                    <div class="space-y-6 bg-gray-50/50 p-6 md:p-8 rounded-3xl border-3 border-gray-100 shadow-inner">
                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block">Total Amount Payable</label>
                            <div class="text-4xl font-black text-gray-900 tracking-tighter amount">
                                <?php echo CURRENCY_SYMBOL . number_format($salary_payment['amount'], 2); ?>
                            </div>
                            <div class="mt-2 inline-flex items-center gap-2 px-3 py-1 bg-emerald-100 text-emerald-700 rounded-lg text-[9px] font-black uppercase tracking-widest">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Funds Available
                            </div>
                        </div>

                        <?php if (!empty($salary_payment['notes'])): ?>
                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block">Audit Notes</label>
                            <p class="text-sm font-bold text-gray-600 leading-relaxed italic border-l-4 border-brand/20 pl-4">
                                "<?php echo htmlspecialchars($salary_payment['notes']); ?>"
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Footer Actions -->
                <form method="POST" class="flex flex-col sm:flex-row items-center gap-4 border-t-3 border-gray-100 pt-10">
                    <a href="salaries.php" class="w-full sm:w-auto px-8 py-4 bg-gray-50 text-gray-400 font-black text-xs uppercase tracking-widest rounded-2xl hover:bg-gray-100 hover:text-gray-600 transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-times-circle"></i> Abort Process
                    </a>
                    <button type="submit" name="confirm_payment" class="w-full sm:flex-grow px-10 py-5 bg-emerald-600 text-white font-black text-sm uppercase tracking-widest rounded-2xl hover:bg-black transition-all shadow-2xl shadow-emerald-600/20 flex items-center justify-center gap-3 transform active:scale-95">
                        <i class="fas fa-check-circle"></i> Confirm Disbursement & Record Transaction
                    </button>
                </form>
            </div>
        </div>

        <div class="mt-8 text-center">
            <p class="text-[10px] font-black text-gray-300 uppercase tracking-[0.3em]">Institutional Verification Required &bull; Mule Wave Tech</p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>