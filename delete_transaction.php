<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Transaction.php';

session_start();
$auth = new Auth();
$auth->requireLogin();

$transaction = new Transaction();

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
        if ($transaction->deleteTransaction($transaction_id)) {
            $_SESSION['success'] = 'Transaction deleted successfully.';
        } else {
            $_SESSION['error'] = 'Failed to delete transaction.';
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
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>Delete Transaction
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-warning me-2"></i>
                        <strong>Warning!</strong> This action cannot be undone.
                    </div>
                    
                    <p>Are you sure you want to delete the following transaction?</p>
                    
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-4"><strong>Type:</strong></div>
                                <div class="col-sm-8">
                                    <span class="badge bg-<?php echo $transaction_data['type'] == 'income' ? 'success' : 'danger'; ?>">
                                        <i class="fas fa-<?php echo $transaction_data['type'] == 'income' ? 'arrow-up' : 'arrow-down'; ?> me-1"></i>
                                        <?php echo ucfirst($transaction_data['type']); ?>
                                    </span>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-4"><strong>Description:</strong></div>
                                <div class="col-sm-8"><?php echo htmlspecialchars($transaction_data['description']); ?></div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-4"><strong>Amount:</strong></div>
                                <div class="col-sm-8">
                                    <span class="fw-bold <?php echo $transaction_data['type'] == 'income' ? 'text-success' : 'text-danger'; ?>">
                                        <?php echo $transaction_data['type'] == 'income' ? '+' : '-'; ?>$<?php echo number_format($transaction_data['amount'], 2); ?>
                                    </span>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-4"><strong>Date:</strong></div>
                                <div class="col-sm-8"><?php echo date('M d, Y', strtotime($transaction_data['transaction_date'])); ?></div>
                            </div>
                            <?php if ($transaction_data['category_name']): ?>
                            <hr>
                            <div class="row">
                                <div class="col-sm-4"><strong>Category:</strong></div>
                                <div class="col-sm-8">
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($transaction_data['category_name']); ?></span>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php if ($transaction_data['notes']): ?>
                            <hr>
                            <div class="row">
                                <div class="col-sm-4"><strong>Notes:</strong></div>
                                <div class="col-sm-8"><?php echo htmlspecialchars($transaction_data['notes']); ?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <form method="POST">
                        <div class="d-flex justify-content-between">
                            <a href="transactions.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Cancel
                            </a>
                            <button type="submit" name="confirm_delete" class="btn btn-danger">
                                <i class="fas fa-trash me-1"></i>Yes, Delete Transaction
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>