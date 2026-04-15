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

<!-- Styles for image preview modal and attachment card -->
<style>
    .preview-modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.85);
        backdrop-filter: blur(5px);
    }

    .preview-modal-content {
        margin: auto;
        display: block;
        width: auto;
        max-width: 80%;
        max-height: 80%;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        animation-name: zoom;
        animation-duration: 0.3s;
    }

    @keyframes zoom {
        from { transform: translate(-50%, -50%) scale(0.1) }
        to   { transform: translate(-50%, -50%) scale(1)   }
    }

    .preview-modal-close {
        position: absolute;
        top: 15px;
        right: 35px;
        color: #f1f1f1;
        font-size: 40px;
        font-weight: bold;
        transition: 0.3s;
        cursor: pointer;
    }

    .preview-modal-close:hover,
    .preview-modal-close:focus {
        color: #bbb;
        text-decoration: none;
    }

    .attachment-current {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 0.75rem;
    }
</style>

<!-- Image Preview Modal -->
<div id="imagePreviewModal" class="preview-modal">
    <span class="preview-modal-close">&times;</span>
    <img class="preview-modal-content" id="modalImage" alt="Attachment preview">
</div>

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

                    <form id="editTransactionForm" method="POST" class="needs-validation" novalidate autocomplete="off" enctype="multipart/form-data">
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

                        <div class="mb-3">
                            <label for="notes" class="form-label">
                                <i class="fas fa-sticky-note me-1"></i>Notes
                            </label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"
                                placeholder="Additional notes (optional)"><?php echo htmlspecialchars($tx['notes'] ?? ''); ?></textarea>
                        </div>

                        <!-- ===== Attachment Section ===== -->
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-paperclip me-1"></i>Attachment
                            </label>

                            <?php if (!empty($tx['attachment_path'])): ?>
                                <?php
                                    $ext      = strtolower(pathinfo($tx['attachment_path'], PATHINFO_EXTENSION));
                                    $isImage  = in_array($ext, ['jpg', 'jpeg', 'png', 'gif']);
                                ?>
                                <!-- Current attachment card -->
                                <div class="attachment-current mb-2">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <?php if ($isImage): ?>
                                                <img src="<?php echo htmlspecialchars($tx['attachment_path']); ?>"
                                                     class="rounded"
                                                     style="max-height: 50px; max-width: 80px; object-fit: cover;"
                                                     alt="Current attachment">
                                            <?php else: ?>
                                                <i class="fas fa-file-pdf fa-2x text-danger"></i>
                                            <?php endif; ?>
                                            <div>
                                                <small class="text-muted d-block">Current attachment</small>
                                                <small class="fw-semibold text-truncate" style="max-width:220px; display:block;">
                                                    <?php echo htmlspecialchars(basename($tx['attachment_path'])); ?>
                                                </small>
                                            </div>
                                        </div>
                                        <a href="<?php echo htmlspecialchars($tx['attachment_path']); ?>"
                                           target="_blank"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye me-1"></i>View
                                        </a>
                                    </div>
                                    <!-- Remove checkbox -->
                                    <div class="form-check mt-2 border-top pt-2">
                                        <input class="form-check-input" type="checkbox"
                                               id="remove_attachment" name="remove_attachment" value="1">
                                        <label class="form-check-label text-danger small" for="remove_attachment">
                                            <i class="fas fa-trash-alt me-1"></i>Remove this attachment
                                        </label>
                                    </div>
                                </div>
                                <small class="text-muted d-block mb-1">
                                    Upload a new file below to <strong>replace</strong> the current one:
                                </small>
                            <?php else: ?>
                                <small class="text-muted d-block mb-1">
                                    No attachment currently. Upload one (optional):
                                </small>
                            <?php endif; ?>

                            <!-- New file input -->
                            <input type="file" class="form-control" id="attachment" name="attachment"
                                   accept=".pdf,.doc,.docx,.png,.jpg,.jpeg,.gif">
                            <small class="form-text text-muted">
                                Allowed types: PDF, DOC, DOCX, PNG, JPG, GIF. Max size: 5MB.
                            </small>

                            <!-- New file preview -->
                            <div id="attachment-preview-container" class="mt-2" style="display: none;">
                                <div class="d-flex align-items-center justify-content-between p-2 border rounded">
                                    <div id="attachment-preview" style="max-width: 80%; overflow: hidden;"></div>
                                    <button type="button" id="view-attachment-btn"
                                            class="btn btn-sm btn-outline-secondary" style="display: none;">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- ===== End Attachment Section ===== -->

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

        // 1. Flatpickr Date Picker (if available)
        if (typeof flatpickr !== 'undefined') {
            flatpickr("#transaction_date", {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "F j, Y",
                maxDate: "today"
            });
        }

        // 2. Category filter logic
        const typeSelect     = document.getElementById('type');
        const categorySelect = document.getElementById('category_id');
        const categoryOptions = Array.from(categorySelect.options);

        function filterCategories() {
            const selectedType         = typeSelect.value;
            const currentCategoryValue = categorySelect.value;

            categorySelect.innerHTML = '';
            categorySelect.appendChild(categoryOptions[0]);

            categoryOptions.forEach(option => {
                if (option.value === "" || !option.dataset.type || option.dataset.type === selectedType) {
                    categorySelect.appendChild(option);
                }
            });

            const currentOption = categorySelect.querySelector(`option[value="${currentCategoryValue}"]`);
            categorySelect.value = currentOption ? currentCategoryValue : '';
        }

        typeSelect.addEventListener('change', filterCategories);
        filterCategories();

        // 3. AJAX form submission
        const form           = document.getElementById('editTransactionForm');
        const saveBtn        = document.getElementById('updateTransactionBtn');
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
                    headers: { 'Accept': 'application/json' },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = `transaction_confirmation.php?id=${data.transaction_id}&updated=true`;
                    } else {
                        showAlert(data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    showAlert('A network error occurred. Please try again.', 'danger');
                })
                .finally(() => {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i class="fas fa-save me-1"></i>Update Transaction';
                });
        });

        function showAlert(message, type = 'danger') {
            alertContainer.innerHTML = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
        }

        // 4. New file attachment preview
        const attachmentInput    = document.getElementById('attachment');
        const previewContainer   = document.getElementById('attachment-preview-container');
        const preview            = document.getElementById('attachment-preview');
        const viewBtn            = document.getElementById('view-attachment-btn');
        const imagePreviewModal  = document.getElementById('imagePreviewModal');
        const modalImage         = document.getElementById('modalImage');
        const modalCloseBtn      = document.querySelector('.preview-modal-close');
        const removeCheckbox     = document.getElementById('remove_attachment');
        let currentFile = null;

        attachmentInput.addEventListener('change', function() {
            currentFile = this.files[0];
            if (currentFile) {
                // Auto-uncheck "remove" when a new file is chosen
                if (removeCheckbox) {
                    removeCheckbox.checked  = false;
                    removeCheckbox.disabled = true;
                }

                previewContainer.style.display = 'block';
                const fileType = currentFile.type;

                if (fileType.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = e => {
                        preview.innerHTML = `<img src="${e.target.result}" class="img-fluid rounded" style="max-height:50px;" alt="Preview">`;
                    };
                    reader.readAsDataURL(currentFile);
                    viewBtn.style.display = 'inline-block';
                } else if (fileType === 'application/pdf') {
                    preview.innerHTML = `<div class="d-flex align-items-center"><i class="fas fa-file-pdf fa-2x text-danger me-2"></i><span>${currentFile.name}</span></div>`;
                    viewBtn.style.display = 'inline-block';
                } else {
                    preview.innerHTML = `<div class="d-flex align-items-center"><i class="fas fa-file fa-2x text-secondary me-2"></i><span>${currentFile.name}</span></div>`;
                    viewBtn.style.display = 'none';
                }
            } else {
                previewContainer.style.display = 'none';
                viewBtn.style.display  = 'none';
                preview.innerHTML      = '';
                currentFile            = null;
                if (removeCheckbox) removeCheckbox.disabled = false;
            }
        });

        viewBtn.addEventListener('click', function() {
            if (!currentFile) return;
            const fileType = currentFile.type;
            if (fileType.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = e => {
                    modalImage.src = e.target.result;
                    imagePreviewModal.style.display = 'block';
                };
                reader.readAsDataURL(currentFile);
            } else if (fileType === 'application/pdf') {
                window.open(URL.createObjectURL(currentFile), '_blank');
            }
        });

        const closeModal = () => imagePreviewModal.style.display = 'none';
        modalCloseBtn.addEventListener('click', closeModal);
        imagePreviewModal.addEventListener('click', e => {
            if (e.target === imagePreviewModal) closeModal();
        });
    });
</script>

<?php include 'includes/footer.php'; ?>