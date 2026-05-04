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

<div class="page-animate w-full max-w-4xl mx-auto">
    <!-- Header Section -->
    <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div class="relative">
            <div class="flex items-center gap-4 mb-2">
                <h1 class="text-4xl font-black text-gray-900 tracking-tight m-0 flex items-center gap-3">
                    Edit Ledger
                </h1>
                <span class="bg-amber-600 text-white text-[10px] font-black px-2.5 py-1 rounded-md shadow-lg shadow-amber-600/20 uppercase tracking-tighter">
                    Modify Entry #<?php echo htmlspecialchars($tx['id']); ?>
                </span>
            </div>
            <p class="text-gray-500 font-medium text-sm m-0">
                Update existing financial records in the primary transaction console.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="transactions.php" class="h-11 px-5 bg-white text-gray-900 border-3 border-gray-100 rounded-xl font-bold text-xs uppercase tracking-widest hover:border-black transition-all flex items-center gap-2 shadow-sm">
                <i class="fas fa-arrow-left text-[10px]"></i> Discard
            </a>
        </div>
    </div>

    <!-- Form Module -->
    <div class="bg-white rounded-[40px] border-3 border-gray-100 shadow-2xl shadow-black/5 overflow-hidden">
        <div class="p-8 md:p-12">
            <div id="alert-container"></div>

            <form id="editTransactionForm" method="POST" class="space-y-10" novalidate autocomplete="off" enctype="multipart/form-data">
                <input type="hidden" name="transaction_id" value="<?php echo htmlspecialchars($tx['id']); ?>">

                <!-- Core Classification -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <label for="type" class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block ml-1">Type Allocation *</label>
                        <div class="relative">
                            <select class="w-full h-14 px-4 bg-gray-50 border-3 border-gray-200 text-gray-900 rounded-2xl focus:bg-white focus:border-brand outline-none transition-all font-bold text-sm appearance-none cursor-pointer" id="type" name="type" required>
                                <option value="">Select Type</option>
                                <option value="income" <?php echo $tx['type'] == 'income' ? 'selected' : ''; ?>>Income / Inflow</option>
                                <option value="expense" <?php echo $tx['type'] == 'expense' ? 'selected' : ''; ?>>Expense / Outflow</option>
                            </select>
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                                <i class="fas fa-chevron-down text-[10px]"></i>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="category_id" class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block ml-1">Registry Category</label>
                        <div class="relative">
                            <select class="w-full h-14 px-4 bg-gray-50 border-3 border-gray-200 text-gray-900 rounded-2xl focus:bg-white focus:border-brand outline-none transition-all font-bold text-sm appearance-none cursor-pointer" id="category_id" name="category_id">
                                <option value="">Select Classification (Optional)</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo $tx['category_id'] == $cat['id'] ? 'selected' : ''; ?> data-type="<?php echo $cat['type']; ?>">
                                        <?php echo htmlspecialchars($cat['name']); ?> (<?php echo ucfirst($cat['type']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                                <i class="fas fa-layer-group text-[10px]"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Financial Metrics -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <label for="amount" class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block ml-1">Net Value (<?php echo CURRENCY_SYMBOL; ?>) *</label>
                        <input type="number" class="w-full h-14 px-4 bg-gray-50 border-3 border-gray-200 text-gray-900 rounded-2xl focus:bg-white focus:border-brand outline-none transition-all font-black text-sm amount" id="amount" name="amount" value="<?php echo htmlspecialchars($tx['amount']); ?>" step="0.01" min="0.01" placeholder="0.00" required>
                    </div>

                    <div>
                        <label for="transaction_date" class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block ml-1">Timeline Timestamp *</label>
                        <input type="date" class="w-full h-14 px-4 bg-gray-50 border-3 border-gray-200 text-gray-900 rounded-2xl focus:bg-white focus:border-brand outline-none transition-all font-bold text-sm" id="transaction_date" name="transaction_date" value="<?php echo htmlspecialchars($tx['transaction_date']); ?>" max="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>

                <!-- Descriptive Layer -->
                <div>
                    <label for="description" class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block ml-1">Primary Description *</label>
                    <input type="text" class="w-full h-14 px-4 bg-gray-50 border-3 border-gray-200 text-gray-900 rounded-2xl focus:bg-white focus:border-brand outline-none transition-all font-bold text-sm" id="description" name="description" value="<?php echo htmlspecialchars($tx['description']); ?>" placeholder="Specify entry purpose..." maxlength="255" required>
                </div>

                <!-- Administrative Meta -->
                <div>
                    <label for="notes" class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block ml-1">Administrative Notes</label>
                    <textarea class="w-full p-4 bg-gray-50 border-3 border-gray-200 text-gray-900 rounded-2xl focus:bg-white focus:border-brand outline-none transition-all font-bold text-sm min-h-[120px]" id="notes" name="notes" placeholder="Optional meta data..."><?php echo htmlspecialchars($tx['notes'] ?? ''); ?></textarea>
                </div>

                <!-- Attachment Management -->
                <div class="p-8 bg-gray-50 rounded-3xl border-3 border-gray-100 group">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-6 block ml-1">Supporting Documentation</label>
                    
                    <?php if (!empty($tx['attachment_path'])): ?>
                        <?php
                            $ext      = strtolower(pathinfo($tx['attachment_path'], PATHINFO_EXTENSION));
                            $isImage  = in_array($ext, ['jpg', 'jpeg', 'png', 'gif']);
                        ?>
                        <div class="bg-white p-6 rounded-2xl border-2 border-gray-100 mb-8 flex items-center justify-between shadow-sm">
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 rounded-xl border border-gray-100 overflow-hidden bg-gray-50 flex items-center justify-center">
                                    <?php if ($isImage): ?>
                                        <img src="<?php echo htmlspecialchars($tx['attachment_path']); ?>" class="max-w-full max-h-full object-cover">
                                    <?php else: ?>
                                        <i class="fas fa-file-pdf text-rose-500 text-xl"></i>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1">Active File</p>
                                    <p class="text-xs font-black text-gray-900 truncate max-w-[200px]"><?php echo htmlspecialchars(basename($tx['attachment_path'])); ?></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <a href="<?php echo htmlspecialchars($tx['attachment_path']); ?>" target="_blank" class="h-8 px-4 bg-gray-50 text-gray-900 rounded-lg text-[8px] font-black uppercase tracking-widest hover:bg-black hover:text-white transition-all flex items-center gap-2">
                                    <i class="fas fa-expand text-[8px]"></i> Preview
                                </a>
                                <div class="flex items-center gap-2 px-3 py-2 bg-rose-50 rounded-lg border border-rose-100">
                                    <input type="checkbox" id="remove_attachment" name="remove_attachment" value="1" class="w-3 h-3 text-rose-600 border-rose-200 rounded">
                                    <label for="remove_attachment" class="text-[8px] font-black text-rose-600 uppercase tracking-widest cursor-pointer">Revoke</label>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="flex flex-col items-center gap-4 py-4">
                        <div class="w-12 h-12 rounded-xl bg-white border-2 border-gray-100 flex items-center justify-center text-gray-300 group-hover:text-brand transition-colors">
                            <i class="fas fa-cloud-upload-alt text-lg"></i>
                        </div>
                        <input type="file" class="hidden" id="attachment" name="attachment" accept=".pdf,.png,.jpg,.jpeg,.gif">
                        <button type="button" onclick="document.getElementById('attachment').click()" class="h-10 px-6 bg-white border-2 border-gray-200 rounded-xl text-[9px] font-black uppercase tracking-widest hover:border-black transition-all">
                            Replace Source File
                        </button>
                    </div>

                    <div id="attachment-preview-container" class="mt-6 hidden">
                        <div class="flex items-center justify-between p-4 bg-white rounded-2xl border border-gray-100 shadow-sm">
                            <div id="attachment-preview" class="flex items-center gap-3"></div>
                            <button type="button" id="view-attachment-btn" class="h-8 px-4 bg-gray-900 text-white rounded-lg text-[8px] font-black uppercase tracking-widest hover:bg-brand transition-all flex items-center gap-2">
                                <i class="fas fa-expand text-[8px]"></i> Preview
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row justify-between items-center gap-6 pt-10 border-t border-gray-100">
                    <div class="flex flex-col gap-1">
                        <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest">Entry Creation Timestamp</p>
                        <p class="text-[10px] font-bold text-gray-900 amount uppercase"><?php echo date('M d, Y • g:i A', strtotime($tx['created_at'])); ?></p>
                    </div>
                    <div class="flex items-center gap-4 w-full sm:w-auto">
                        <a href="delete_transaction.php?id=<?php echo $tx['id']; ?>" class="h-16 px-8 bg-rose-50 text-rose-600 border-2 border-rose-100 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-rose-600 hover:text-white transition-all flex items-center justify-center gap-2">
                            <i class="fas fa-trash-alt"></i> Purge
                        </a>
                        <button type="submit" class="flex-grow sm:flex-grow-0 h-16 px-12 bg-black text-white rounded-2xl font-black text-xs uppercase tracking-[0.2em] hover:bg-gray-800 transition-all shadow-2xl shadow-black/20 flex items-center justify-center gap-3" id="updateTransactionBtn">
                            <i class="fas fa-save"></i> Synchronize
                        </button>
                    </div>
                </div>
            </form>
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
            const bgColor = type === 'success' ? 'bg-green-50 border-green-500' : 'bg-red-50 border-red-500';
            const textColor = type === 'success' ? 'text-green-700' : 'text-red-700';
            const icon = type === 'success' ? 'fa-check-circle text-green-500' : 'fa-exclamation-triangle text-red-500';

            alertContainer.innerHTML = `
                <div class="${bgColor} border-l-4 p-4 mb-6 rounded-r-xl flex items-center shadow-sm">
                    <i class="fas ${icon} mr-3 text-lg"></i>
                    <p class="${textColor} text-sm font-medium flex-grow">${message}</p>
                    <button type="button" class="text-gray-400 hover:text-gray-600 focus:outline-none" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
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