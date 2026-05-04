<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Transaction.php';
require_once 'classes/Employee.php';

$auth = new Auth();
$auth->requireLogin();

$transaction = new Transaction();
$employee = new Employee();

// Get transaction ID
$transaction_id = $_GET['id'] ?? 0;

if (!$transaction_id) {
    $_SESSION['error'] = 'Invalid transaction ID.';
    header('Location: transactions.php');
    exit();
}

// Get transaction details for confirmation
$transaction_data = $transaction->getTransactionById($transaction_id);

if (!$transaction_data) {
    $_SESSION['error'] = 'Transaction not found.';
    header('Location: transactions.php');
    exit();
}

// Handle deletion confirmation
if ($_POST && isset($_POST['confirm_delete'])) {
    try {
        if (strpos($transaction_data['notes'], '[salary_payment_id:') !== false) {
            preg_match('/\[salary_payment_id:(\d+)\]/', $transaction_data['notes'], $matches);
            $salary_payment_id = $matches[1] ?? null;

            if ($salary_payment_id) {
                $employee->revertSalaryPaymentStatus($salary_payment_id);
                $transaction->deleteTransaction($transaction_id);
                $_SESSION['success_message'] = 'Transaction deleted and salary payment reverted to Pending.';
            } else {
                $transaction->deleteTransaction($transaction_id);
                $_SESSION['success_message'] = 'Transaction deleted successfully.';
            }
        } else {
            if ($transaction->deleteTransaction($transaction_id)) {
                $_SESSION['success_message'] = 'Transaction deleted successfully.';
            } else {
                $_SESSION['error'] = 'Failed to delete transaction.';
            }
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'An error occurred: ' . $e->getMessage();
    }

    header('Location: transactions.php');
    exit();
}

$page_title = 'Delete Transaction';
include 'includes/header.php';
include 'includes/navbar.php';

$is_income = $transaction_data['type'] === 'income';
$type_color = $is_income ? 'emerald' : 'rose';
$type_icon  = $is_income ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down';
?>

<div class="page-animate w-full max-w-2xl mx-auto">

    <!-- Page Header -->
    <div class="mb-8 flex items-center gap-4">
        <a href="transactions.php" class="w-10 h-10 flex items-center justify-center rounded-xl border-3 border-gray-100 bg-white hover:border-black transition-all text-gray-500 hover:text-gray-900 shadow-sm">
            <i class="fas fa-arrow-left text-xs"></i>
        </a>
        <div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight m-0">Delete Transaction</h1>
            <p class="text-gray-400 text-xs font-bold uppercase tracking-widest m-0">This action is permanent</p>
        </div>
    </div>

    <!-- Warning Banner -->
    <div class="flex items-start gap-4 bg-rose-50 border-l-4 border-rose-500 rounded-2xl p-5 mb-6">
        <div class="w-9 h-9 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center flex-shrink-0 mt-0.5">
            <i class="fas fa-triangle-exclamation"></i>
        </div>
        <div>
            <p class="font-black text-rose-700 text-sm m-0 mb-1">Irreversible Action</p>
            <p class="text-rose-500 text-xs font-medium m-0">This transaction record will be permanently removed from the ledger and cannot be recovered.</p>
        </div>
    </div>

    <!-- Transaction Detail Card -->
    <div class="bg-white rounded-[32px] border-3 border-gray-100 shadow-2xl shadow-black/5 overflow-hidden mb-6">

        <!-- Card header strip -->
        <div class="px-8 py-5 border-b border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest m-0 mb-1">Transaction Record</p>
                <p class="text-xs font-bold text-gray-600 m-0">#<?php echo $transaction_id; ?> · <?php echo date('M d, Y', strtotime($transaction_data['transaction_date'])); ?></p>
            </div>
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-tighter
                <?php echo $is_income ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'; ?>">
                <i class="fas <?php echo $type_icon; ?>"></i>
                <?php echo ucfirst($transaction_data['type']); ?>
            </span>
        </div>

        <!-- Details list -->
        <div class="divide-y divide-gray-50">

            <!-- Amount — most prominent -->
            <div class="px-8 py-5 flex items-center justify-between">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Net Value</span>
                <span class="text-2xl font-black <?php echo $is_income ? 'text-emerald-600' : 'text-rose-600'; ?>">
                    <?php echo $is_income ? '+' : '-'; ?><?php echo CURRENCY_SYMBOL . number_format($transaction_data['amount'], 2); ?>
                </span>
            </div>

            <!-- Description -->
            <div class="px-8 py-4 flex items-center justify-between gap-4">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest flex-shrink-0">Description</span>
                <span class="text-sm font-bold text-gray-800 text-right"><?php echo htmlspecialchars($transaction_data['description']); ?></span>
            </div>

            <!-- Date -->
            <div class="px-8 py-4 flex items-center justify-between">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Date</span>
                <span class="text-sm font-bold text-gray-700"><?php echo date('F j, Y', strtotime($transaction_data['transaction_date'])); ?></span>
            </div>

            <?php if (!empty($transaction_data['category_name'])): ?>
            <!-- Category -->
            <div class="px-8 py-4 flex items-center justify-between">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Category</span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-gray-100 text-gray-700 text-[10px] font-black uppercase tracking-tighter">
                    <i class="fas fa-layer-group"></i>
                    <?php echo htmlspecialchars($transaction_data['category_name']); ?>
                </span>
            </div>
            <?php endif; ?>

            <?php if (!empty($transaction_data['notes'])): ?>
            <!-- Notes -->
            <div class="px-8 py-4 flex items-start justify-between gap-4">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest flex-shrink-0 pt-0.5">Notes</span>
                <span class="text-xs font-medium text-gray-500 text-right"><?php echo htmlspecialchars($transaction_data['notes']); ?></span>
            </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- Action Buttons -->
    <form method="POST">
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="transactions.php" class="flex-1 h-14 flex items-center justify-center gap-2 bg-white border-3 border-gray-100 text-gray-700 rounded-2xl font-bold text-xs uppercase tracking-widest hover:border-black hover:text-gray-900 transition-all shadow-sm">
                <i class="fas fa-arrow-left text-[10px]"></i> Cancel, Go Back
            </a>
            <button type="submit" name="confirm_delete" value="1"
                class="flex-1 h-14 flex items-center justify-center gap-2 bg-rose-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-rose-700 transition-all shadow-xl shadow-rose-600/25">
                <i class="fas fa-trash"></i> Yes, Delete Permanently
            </button>
        </div>
    </form>

</div>

<?php include 'includes/footer.php'; ?>