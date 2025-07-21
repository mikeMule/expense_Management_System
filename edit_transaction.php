<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Transaction.php';

$page_title = 'Edit Transaction';
include 'includes/header.php';

$auth = new Auth();
$auth->requireLogin();

$transaction = new Transaction();

// Get transaction ID
$transaction_id = $_GET['id'] ?? 0;
if (!$transaction_id) {
    header('Location: transactions.php');
    exit();
}

// Get transaction details
$tx = $transaction->getTransactionById($transaction_id);
if (!$tx) {
    $_SESSION['error'] = 'Transaction not found.';
    header('Location: transactions.php');
    exit();
}

// Get categories for dropdown
$categories = $transaction->getCategories();

include 'includes/navbar.php';
?>

<div class="container py-4 page-animate">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm main-info-card">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">
                        <i class="fas fa-edit me-2"></i>Edit Transaction #<?php echo htmlspecialchars($tx['id']); ?>
                    </h4>
                </div>
                <div class="card-body">
                    <div id="alert-container"></div>

                    <form id="editTransactionForm" method="POST" class="needs-validation" novalidate autocomplete="off">
                        <input type="hidden" name="transaction_id" value="<?php echo htmlspecialchars($tx['id']); ?>">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">
                                    <i class="fas fa-exchange-alt me-1"></i>Transaction Type *
                                </label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="">Select Type</option>
                                    <option value="income" <?php echo $tx['type'] == 'income' ? 'selected' : ''; ?>>
                                        Income
                                    </option>
                                    <option value="expense" <?php echo $tx['type'] == 'expense' ? 'selected' : ''; ?>>
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
                                            <?php echo $tx['category_id'] == $cat['id'] ? 'selected' : ''; ?>
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
                                    value="<?php echo htmlspecialchars($tx['amount']); ?>"
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
                                    value="<?php echo htmlspecialchars($tx['transaction_date']); ?>"
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
                                value="<?php echo htmlspecialchars($tx['description']); ?>"
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
                                placeholder="Additional notes (optional)"><?php echo htmlspecialchars($tx['notes']); ?></textarea>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Created: <?php echo date('M d, Y, g:i A', strtotime($tx['created_at'])); ?>
                                </small>
                            </div>
                            <?php if ($tx['updated_at'] && $tx['updated_at'] != $tx['created_at']): ?>
                                <div class="col-md-6 text-md-end">
                                    <small class="text-muted">
                                        <i class="fas fa-edit me-1"></i>
                                        Last Updated: <?php echo date('M d, Y, g:i A', strtotime($tx['updated_at'])); ?>
                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="transactions.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Back to List
                            </a>
                            <div>
                                <a href="delete_transaction.php?id=<?php echo $tx['id']; ?>"
                                    class="btn btn-danger me-2 btn-delete"
                                    data-item-name="transaction #<?php echo htmlspecialchars($tx['id']); ?>"
                                    data-item-type="transaction">
                                    <i class="fas fa-trash me-1"></i>Delete
                                </a>
                                <button type="submit" class="btn btn-primary" id="updateTransactionBtn">
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Category filter logic
        const typeSelect = document.getElementById('type');
        const categorySelect = document.getElementById('category_id');
        const categoryOptions = Array.from(categorySelect.options);

        function filterCategories() {
            const selectedType = typeSelect.value;
            const currentCategoryValue = categorySelect.value;

            categorySelect.innerHTML = '';
            categorySelect.appendChild(categoryOptions[0]);

            categoryOptions.forEach(option => {
                if (option.value === "" || !option.dataset.type || option.dataset.type === selectedType) {
                    categorySelect.appendChild(option);
                }
            });

            const currentOption = categorySelect.querySelector(`option[value="${currentCategoryValue}"]`);
            if (!currentOption) {
                categorySelect.value = '';
            } else {
                categorySelect.value = currentCategoryValue;
            }
        }

        typeSelect.addEventListener('change', filterCategories);
        filterCategories();

        // AJAX form submission
        const form = document.getElementById('editTransactionForm');
        const saveBtn = document.getElementById('updateTransactionBtn');
        const alertContainer = document.getElementById('alert-container');

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }

            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...';

            const formData = new FormData(form);

            fetch('actions/edit_transaction.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json'
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Redirect to confirmation page with a success flag
                        window.location.href = `transaction_confirmation.php?id=${data.transaction_id}&updated=true`;
                    } else {
                        alertContainer.innerHTML = '';
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                        alertDiv.role = 'alert';
                        alertDiv.innerHTML = `
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    ${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                        alertContainer.appendChild(alertDiv);
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    alertContainer.innerHTML = '';
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                    alertDiv.role = 'alert';
                    alertDiv.innerHTML = `
                <i class="fas fa-exclamation-triangle me-2"></i>
                A network error occurred. Please try again.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
                    alertContainer.appendChild(alertDiv);
                })
                .finally(() => {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i class="fas fa-save me-1"></i>Update Transaction';
                });
        });
    });
</script>

<?php include 'includes/footer.php'; ?>