<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Transaction.php';

$auth = new Auth();
$auth->requireLogin();

$transaction = new Transaction();

// Pre-select type if provided in URL
$preselected_type = $_GET['type'] ?? 'expense';

// Get categories for dropdown
$categories = $transaction->getCategories();

// All PHP logic is now complete. We can start the HTML output.
$page_title = 'Add Transaction';
include 'includes/header.php';
?>

<!-- Specific styles for this page's modals, safely included after the header -->
<style>
    /* Simple Modal for Image Preview */
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
        from {
            transform: translate(-50%, -50%) scale(0.1)
        }

        to {
            transform: translate(-50%, -50%) scale(1)
        }
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
</style>

<!-- Image Preview Modal -->
<div id="imagePreviewModal" class="preview-modal">
    <span class="preview-modal-close">&times;</span>
    <img class="preview-modal-content" id="modalImage">
</div>

<?php include 'includes/navbar.php'; ?>

<div class="container py-4 page-animate">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm main-info-card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-plus-circle me-2"></i>Add New Transaction
                    </h4>
                </div>
                <div class="card-body">
                    <div id="alert-container"></div>

                    <form id="addTransactionForm" method="POST" class="needs-validation" novalidate autocomplete="off" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">
                                    <i class="fas fa-exchange-alt me-1"></i>Transaction Type *
                                </label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="">Select Type</option>
                                    <option value="income" <?php echo $preselected_type == 'income' ? 'selected' : ''; ?>>
                                        Income
                                    </option>
                                    <option value="expense" <?php echo $preselected_type == 'expense' ? 'selected' : ''; ?>>
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
                                    value="<?php echo date('Y-m-d'); ?>"
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
                                placeholder="Enter transaction description" maxlength="255" required>
                            <div class="invalid-feedback">
                                Please enter a description.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="attachment" class="form-label">
                                <i class="fas fa-paperclip me-1"></i>Attach File (Optional)
                            </label>
                            <input type="file" class="form-control" id="attachment" name="attachment" accept=".pdf,.png,.jpg,.jpeg,.gif">
                            <small class="form-text text-muted">
                                Allowed types: PDF, PNG, JPG, GIF. Max size: 5MB.
                            </small>
                            <div id="attachment-preview-container" class="mt-2" style="display: none;">
                                <div class="d-flex align-items-center justify-content-between p-2 border rounded">
                                    <div id="attachment-preview" style="max-width: 80%; overflow: hidden;"></div>
                                    <button type="button" id="view-attachment-btn" class="btn btn-sm btn-outline-secondary" style="display: none;">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="notes" class="form-label">
                                <i class="fas fa-sticky-note me-1"></i>Notes
                            </label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"
                                placeholder="Additional notes (optional)">Mule Wave Technology - Addis Ababa ETHIOPIA</textarea>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="transactions.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Back to List
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
    // --- All scripts are now inside a single, clean DOMContentLoaded listener ---
    document.addEventListener('DOMContentLoaded', function() {

        // 1. Initialize Flatpickr Date Picker
        flatpickr("#transaction_date", {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "F j, Y",
            maxDate: "today"
        });

        // 2. Setup Category Filtering
        const typeSelect = document.getElementById('type');
        const categorySelect = document.getElementById('category_id');
        const categoryOptions = Array.from(categorySelect.options);

        function filterCategories() {
            const selectedType = typeSelect.value;
            const currentCategoryValue = categorySelect.value;

            // Clear and re-populate the category dropdown
            categorySelect.innerHTML = '';
            categorySelect.appendChild(categoryOptions[0]); // Keep the "Select Category" option

            categoryOptions.forEach(option => {
                if (option.value === "" || !option.dataset.type || option.dataset.type === selectedType) {
                    categorySelect.appendChild(option);
                }
            });

            // If the previously selected category is not compatible, reset it
            const currentOption = categorySelect.querySelector(`option[value="${currentCategoryValue}"]`);
            if (!currentOption) {
                categorySelect.value = '';
            } else {
                categorySelect.value = currentCategoryValue;
            }
        }

        typeSelect.addEventListener('change', filterCategories);
        // Initial filter on page load
        filterCategories();

        // 3. Setup Form Submission via AJAX
        const form = document.getElementById('addTransactionForm');
        const saveBtn = document.getElementById('saveTransactionBtn');
        const alertContainer = document.getElementById('alert-container');

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';

            const formData = new FormData(form);
            fetch('actions/add_transaction.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json'
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = `transaction_confirmation.php?id=${data.transaction_id}`;
                    } else {
                        showAlert(data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Submission error:', error);
                    showAlert('A network error occurred. Please try again.', 'danger');
                })
                .finally(() => {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i class="fas fa-save me-1"></i> Save Transaction';
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

        // 4. Setup Attachment Preview and Modal
        const attachmentInput = document.getElementById('attachment');
        const previewContainer = document.getElementById('attachment-preview-container');
        const preview = document.getElementById('attachment-preview');
        const viewBtn = document.getElementById('view-attachment-btn');
        const imagePreviewModal = document.getElementById('imagePreviewModal');
        const modalImage = document.getElementById('modalImage');
        const modalCloseBtn = document.querySelector('.preview-modal-close');
        let currentFile = null;

        attachmentInput.addEventListener('change', function() {
            currentFile = this.files[0];
            if (currentFile) {
                previewContainer.style.display = 'block';
                const fileType = currentFile.type;

                if (fileType.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.innerHTML = `<img src="${e.target.result}" class="img-fluid rounded" style="max-height: 50px;" alt="Preview">`;
                    };
                    reader.readAsDataURL(currentFile);
                    viewBtn.style.display = 'inline-block';
                } else if (fileType === 'application/pdf') {
                    preview.innerHTML = `<div class="d-flex align-items-center"><i class="fas fa-file-pdf fa-2x text-danger me-2"></i><span>${currentFile.name}</span></div>`;
                    viewBtn.style.display = 'inline-block'; // Show view button for PDF
                } else {
                    const icon = '<i class="fas fa-file fa-2x text-secondary me-2"></i>';
                    preview.innerHTML = `<div class="d-flex align-items-center">${icon}<span>${currentFile.name}</span></div>`;
                    viewBtn.style.display = 'none';
                }
            } else {
                previewContainer.style.display = 'none';
                viewBtn.style.display = 'none';
                preview.innerHTML = '';
            }
        });

        viewBtn.addEventListener('click', function() {
            if (!currentFile) return;

            const fileType = currentFile.type;

            if (fileType.startsWith('image/')) {
                // Handle image preview in modal
                const reader = new FileReader();
                reader.onload = function(e) {
                    modalImage.src = e.target.result;
                    imagePreviewModal.style.display = 'block';
                };
                reader.readAsDataURL(currentFile);
            } else if (fileType === 'application/pdf') {
                // Handle PDF preview in new tab
                const fileURL = URL.createObjectURL(currentFile);
                window.open(fileURL, '_blank');
            }
        });

        const closeModal = () => imagePreviewModal.style.display = 'none';
        modalCloseBtn.addEventListener('click', closeModal);
        imagePreviewModal.addEventListener('click', (e) => {
            if (e.target === imagePreviewModal) {
                closeModal();
            }
        });
    });
</script>

<?php include 'includes/footer.php'; ?>