<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Transaction.php';

$page_title = 'Add Transaction';
include 'includes/header.php';

$auth = new Auth();
$auth->requireLogin();

$transaction = new Transaction();
$error = '';
$success = '';

// Pre-select type if provided in URL
$preselected_type = $_GET['type'] ?? '';

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
        // Add transaction
        try {
            if ($transaction->addTransaction($type, $category_id ?: null, $amount, $description, $transaction_date, $notes)) {
                $success = 'Transaction added successfully!';
                // Clear form data
                $type = $category_id = $amount = $description = $transaction_date = $notes = '';
            } else {
                $error = 'Failed to add transaction. Please try again.';
            }
        } catch (Exception $e) {
            $error = 'An error occurred: ' . $e->getMessage();
        }
    }
}

// Get categories for dropdown
$categories = $transaction->getCategories();

include 'includes/navbar.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-plus-circle me-2"></i>Add New Transaction
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

                    <form id="addTransactionForm" method="POST" class="needs-validation" novalidate autocomplete="off">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">
                                    <i class="fas fa-exchange-alt me-1"></i>Transaction Type *
                                </label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="">Select Type</option>
                                    <option value="income" <?php echo ($type ?? $preselected_type) == 'income' ? 'selected' : ''; ?>>
                                        Income
                                    </option>
                                    <option value="expense" <?php echo ($type ?? $preselected_type) == 'expense' ? 'selected' : ''; ?>>
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
                                            <?php echo ($category_id ?? '') == $cat['id'] ? 'selected' : ''; ?>
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
                                    value="<?php echo htmlspecialchars($amount ?? ''); ?>"
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
                                    value="<?php echo htmlspecialchars($transaction_date ?? date('Y-m-d')); ?>"
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
                                value="<?php echo htmlspecialchars($description ?? ''); ?>"
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
                                placeholder="Additional notes (optional)"><?php echo htmlspecialchars($notes ?? ''); ?></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="transactions.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Back to Transactions
                            </a>
                            <button type="submit" class="btn btn-primary" id="saveTransactionBtn">
                                <i class="fas fa-save me-1"></i>Save Transaction
                            </button>
                        </div>
                    </form>
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

        // AJAX submit for add transaction
        const form = document.getElementById('addTransactionForm');
        const saveBtn = document.getElementById('saveTransactionBtn');
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
            const formData = new FormData(form);
            fetch('actions/add_transaction.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i class="fas fa-save me-1"></i>Save Transaction';
                    // Remove previous alerts
                    document.querySelectorAll('.alert').forEach(el => el.remove());
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert ' + (data.success ? 'alert-success' : 'alert-danger');
                    alertDiv.innerHTML = (data.success ? '<i class="fas fa-check-circle me-2"></i>' : '<i class="fas fa-exclamation-triangle me-2"></i>') + data.message;
                    form.parentNode.insertBefore(alertDiv, form);
                    if (data.success) {
                        form.reset();
                        form.classList.remove('was-validated');
                    }
                })
                .catch(() => {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i class="fas fa-save me-1"></i>Save Transaction';
                    document.querySelectorAll('.alert').forEach(el => el.remove());
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-danger';
                    alertDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Network error. Please try again.';
                    form.parentNode.insertBefore(alertDiv, form);
                });
        });
    });
</script>

<?php include 'includes/footer.php'; ?>