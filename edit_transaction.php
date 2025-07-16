<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Transaction.php';

$page_title = 'Edit Transaction';
include 'includes/header.php';

$auth = new Auth();
$auth->requireLogin();

$transaction = new Transaction();
$error = '';
$success = '';

// Get transaction ID
$transaction_id = $_GET['id'] ?? 0;

if (!$transaction_id) {
    header('Location: transactions.php');
    exit();
}

// Get transaction details
$transaction_data = $transaction->getTransactionById($transaction_id);

if (!$transaction_data) {
    $_SESSION['error'] = 'Transaction not found.';
    header('Location: transactions.php');
    exit();
}

// Handle form submission
if ($_POST) {
    $type = trim($_POST['type'] ?? '');
    $category_id = trim($_POST['category_id'] ?? '');
    $amount = trim($_POST['amount'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $transaction_date = trim($_POST['transaction_date'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    // Validation
    if (empty($type) || empty($amount) || empty($description) || empty($transaction_date)) {
        $error = 'Please fill in all required fields.';
    } elseif (!in_array($type, ['income', 'expense'])) {
        $error = 'Invalid transaction type.';
    } elseif (!is_numeric($amount) || $amount <= 0) {
        $error = 'Please enter a valid amount greater than 0.';
    } elseif (!strtotime($transaction_date)) {
        $error = 'Please enter a valid date.';
    } else {
        // Update transaction
        try {
            if ($transaction->updateTransaction($transaction_id, $type, $category_id ?: null, $amount, $description, $transaction_date, $notes)) {
                $success = 'Transaction updated successfully!';
                // Refresh transaction data
                $transaction_data = $transaction->getTransactionById($transaction_id);
            } else {
                $error = 'Failed to update transaction. Please try again.';
            }
        } catch (Exception $e) {
            $error = 'An error occurred: ' . $e->getMessage();
        }
    }
} else {
    // Pre-populate form with existing data
    $type = $transaction_data['type'];
    $category_id = $transaction_data['category_id'];
    $amount = $transaction_data['amount'];
    $description = $transaction_data['description'];
    $transaction_date = $transaction_data['transaction_date'];
    $notes = $transaction_data['notes'];
}

// Get categories for dropdown
$categories = $transaction->getCategories();

include 'includes/navbar.php';
?>

<style>
    .main-info-card {
        background: #fff;
        border-radius: 1.25rem;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
        padding: 2.5rem 2rem;
        margin: 2rem auto;
        max-width: 2619px;
    }

    @media (max-width: 991px) {
        .main-info-card {
            padding: 1.5rem 0.7rem;
            margin: 1.2rem 0.2rem;
        }
    }

    @media (max-width: 600px) {
        .main-info-card {
            padding: 0.7rem 0.2rem;
            margin: 0.5rem 0.1rem;
            border-radius: 0.7rem;
        }
    }
</style>

<div class="page-animate">
    <div class="main-info-card">
        <div class="container py-4">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="mb-0">
                                <i class="fas fa-edit me-2"></i>Edit Transaction
                            </h4>
                        </div>
                        <div class="card-body">
                            <?php if ($error): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($success): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" class="needs-validation" novalidate>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="type" class="form-label">
                                            <i class="fas fa-exchange-alt me-1"></i>Transaction Type *
                                        </label>
                                        <select class="form-select" id="type" name="type" required>
                                            <option value="">Select Type</option>
                                            <option value="income" <?php echo $type == 'income' ? 'selected' : ''; ?>>
                                                Income
                                            </option>
                                            <option value="expense" <?php echo $type == 'expense' ? 'selected' : ''; ?>>
                                                Expense
                                            </option>
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select a transaction type.
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="category_id" class="form-label">
                                            <i class="fas fa-tags me-1"></i>Category
                                        </label>
                                        <select class="form-select" id="category_id" name="category_id">
                                            <option value="">Select Category (Optional)</option>
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?php echo $cat['id']; ?>"
                                                    <?php echo $category_id == $cat['id'] ? 'selected' : ''; ?>
                                                    data-type="<?php echo $cat['type']; ?>">
                                                    <?php echo htmlspecialchars($cat['name']); ?> (<?php echo ucfirst($cat['type']); ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="amount" class="form-label">
                                            <i class="fas fa-dollar-sign me-1"></i>Amount *
                                        </label>
                                        <input type="number" class="form-control" id="amount" name="amount"
                                            value="<?php echo htmlspecialchars($amount); ?>"
                                            step="0.01" min="0.01" placeholder="0.00" data-currency required>
                                        <div class="invalid-feedback">
                                            Please enter a valid amount.
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="transaction_date" class="form-label">
                                            <i class="fas fa-calendar me-1"></i>Transaction Date *
                                        </label>
                                        <input type="date" class="form-control" id="transaction_date" name="transaction_date"
                                            value="<?php echo htmlspecialchars($transaction_date); ?>"
                                            max="<?php echo date('Y-m-d'); ?>" required>
                                        <div class="invalid-feedback">
                                            Please select a transaction date.
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">
                                        <i class="fas fa-file-alt me-1"></i>Description *
                                    </label>
                                    <input type="text" class="form-control" id="description" name="description"
                                        value="<?php echo htmlspecialchars($description); ?>"
                                        placeholder="Enter transaction description" maxlength="255" required>
                                    <div class="invalid-feedback">
                                        Please enter a description.
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="notes" class="form-label">
                                        <i class="fas fa-sticky-note me-1"></i>Notes
                                    </label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3"
                                        placeholder="Additional notes (optional)"><?php echo htmlspecialchars($notes); ?></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Created: <?php echo date('M d, Y \\a\\t g:i A', strtotime($transaction_data['created_at'])); ?>
                                        </small>
                                    </div>
                                    <?php if ($transaction_data['updated_at'] != $transaction_data['created_at']): ?>
                                        <div class="col-md-6 mb-3">
                                            <small class="text-muted">
                                                <i class="fas fa-edit me-1"></i>
                                                Last Updated: <?php echo date('M d, Y \\a\\t g:i A', strtotime($transaction_data['updated_at'])); ?>
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <a href="transactions.php" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-1"></i>Back to Transactions
                                    </a>
                                    <div>
                                        <a href="delete_transaction.php?id=<?php echo $transaction_id; ?>"
                                            class="btn btn-danger me-2 btn-delete"
                                            data-item="transaction '<?php echo htmlspecialchars($description); ?>'">
                                            <i class="fas fa-trash me-1"></i>Delete
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i>Update Transaction
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Filter categories based on transaction type
    document.getElementById('type').addEventListener('change', function() {
        const selectedType = this.value;
        const categorySelect = document.getElementById('category_id');
        const options = categorySelect.querySelectorAll('option[data-type]');

        options.forEach(option => {
            if (selectedType === '' || option.dataset.type === selectedType) {
                option.style.display = '';
            } else {
                option.style.display = 'none';
            }
        });

        // Reset category selection if current selection is not compatible
        if (categorySelect.selectedOptions[0] &&
            categorySelect.selectedOptions[0].dataset.type &&
            categorySelect.selectedOptions[0].dataset.type !== selectedType) {
            categorySelect.value = '';
        }
    });

    // Trigger the change event on page load to filter categories
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('type').dispatchEvent(new Event('change'));
    });
</script>

<?php include 'includes/footer.php'; ?>