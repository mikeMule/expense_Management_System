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

// Determine default notes based on location
$default_notes = '';
$user_location = $_SESSION['location'] ?? '';
if ($user_location === 'Bahirdar') {
    $default_notes = 'this is From Bahirdar office';
} elseif ($user_location === 'Addis Ababa') {
    $default_notes = 'this is From Addis Ababa Office';
}

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

<div class="page-animate w-full max-w-4xl mx-auto">
    <!-- Header Section -->
    <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div class="relative">
            <div class="flex items-center gap-4 mb-2">
                <h1 class="text-4xl font-black text-gray-900 tracking-tight m-0 flex items-center gap-3">
                    New Ledger
                </h1>
                <span class="bg-emerald-600 text-white text-[10px] font-black px-2.5 py-1 rounded-md shadow-lg shadow-emerald-600/20 uppercase tracking-tighter">
                    Append Entry
                </span>
            </div>
            <p class="text-gray-500 font-medium text-sm m-0">
                Initialize a new financial record in the primary transaction console.
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

            <form id="addTransactionForm" method="POST" class="space-y-10" novalidate autocomplete="off" enctype="multipart/form-data">
                <!-- Core Classification -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="group relative">
                        <label for="type" class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block ml-1">Type Allocation *</label>
                        <div class="relative">
                            <select class="w-full h-14 px-4 bg-gray-50 border-3 border-gray-200 text-gray-900 rounded-2xl focus:bg-white focus:border-brand outline-none transition-all font-bold text-sm appearance-none cursor-pointer" id="type" name="type" required>
                                <option value="">Select Type</option>
                                <option value="income" <?php echo $preselected_type == 'income' ? 'selected' : ''; ?>>Income / Inflow</option>
                                <option value="expense" <?php echo $preselected_type == 'expense' ? 'selected' : ''; ?>>Expense / Outflow</option>
                            </select>
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                                <i class="fas fa-chevron-down text-[10px]"></i>
                            </div>
                        </div>
                        <div class="text-rose-500 text-[10px] font-bold mt-1.5 ml-1 hidden" id="type-error">Please define transaction class.</div>
                    </div>

                    <div>
                        <label for="category_id" class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block ml-1">Registry Category</label>
                        <div class="relative">
                            <select class="w-full h-14 px-4 bg-gray-50 border-3 border-gray-200 text-gray-900 rounded-2xl focus:bg-white focus:border-brand outline-none transition-all font-bold text-sm appearance-none cursor-pointer" id="category_id" name="category_id">
                                <option value="">Select Classification (Optional)</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" data-type="<?php echo $cat['type']; ?>">
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
                        <input type="number" class="w-full h-14 px-4 bg-gray-50 border-3 border-gray-200 text-gray-900 rounded-2xl focus:bg-white focus:border-brand outline-none transition-all font-black text-sm amount" id="amount" name="amount" step="0.01" min="0.01" placeholder="0.00" required>
                        <div class="text-rose-500 text-[10px] font-bold mt-1.5 ml-1 hidden" id="amount-error">Valid numerical value required.</div>
                    </div>

                    <div>
                        <label for="transaction_date" class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block ml-1">Timeline Timestamp *</label>
                        <input type="date" class="w-full h-14 px-4 bg-gray-50 border-3 border-gray-200 text-gray-900 rounded-2xl focus:bg-white focus:border-brand outline-none transition-all font-bold text-sm" id="transaction_date" name="transaction_date" value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>" required>
                        <div class="text-rose-500 text-[10px] font-bold mt-1.5 ml-1 hidden" id="date-error">Valid timeline date required.</div>
                    </div>
                </div>

                <!-- Descriptive Layer -->
                <div>
                    <label for="description" class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block ml-1">Primary Description *</label>
                    <input type="text" class="w-full h-14 px-4 bg-gray-50 border-3 border-gray-200 text-gray-900 rounded-2xl focus:bg-white focus:border-brand outline-none transition-all font-bold text-sm" id="description" name="description" placeholder="Specify entry purpose..." maxlength="255" required>
                    <div class="text-rose-500 text-[10px] font-bold mt-1.5 ml-1 hidden" id="description-error">Core description required.</div>
                </div>

                <!-- Supporting Documentation -->
                <div class="p-8 bg-gray-50 rounded-3xl border-2 border-dashed border-gray-200 group hover:border-brand transition-all">
                    <label for="attachment" class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4 block text-center">Supporting Documentation</label>
                    <div class="flex flex-col items-center gap-4">
                        <div class="w-16 h-16 rounded-2xl bg-white border-2 border-gray-100 flex items-center justify-center text-gray-300 group-hover:text-brand transition-colors">
                            <i class="fas fa-cloud-upload-alt text-2xl"></i>
                        </div>
                        <input type="file" class="hidden" id="attachment" name="attachment" accept=".pdf,.png,.jpg,.jpeg,.gif">
                        <button type="button" onclick="document.getElementById('attachment').click()" class="h-10 px-6 bg-white border-2 border-gray-200 rounded-xl text-[9px] font-black uppercase tracking-widest hover:border-black transition-all">
                            Browse Local Repository
                        </button>
                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-tighter">PDF, PNG, JPG, GIF • MAX 5MB</p>
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

                <!-- Administrative Meta -->
                <div>
                    <label for="notes" class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block ml-1">Administrative Notes</label>
                    <textarea class="w-full p-4 bg-gray-50 border-3 border-gray-200 text-gray-900 rounded-2xl focus:bg-white focus:border-brand outline-none transition-all font-bold text-sm min-h-[120px]" id="notes" name="notes" placeholder="Optional meta data..."><?php echo htmlspecialchars($default_notes); ?></textarea>
                </div>

                <!-- Execution Layer -->
                <div class="flex flex-col sm:flex-row justify-end items-center gap-4 pt-10 border-t border-gray-100">
                    <button type="submit" class="w-full sm:w-64 h-16 bg-black text-white rounded-2xl font-black text-xs uppercase tracking-[0.2em] hover:bg-gray-800 transition-all shadow-2xl shadow-black/20 flex items-center justify-center gap-3" id="saveTransactionBtn">
                        <i class="fas fa-check-circle"></i> Commit Entry
                    </button>
                </div>
            </form>
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
                        window.location.href = 'transactions.php';
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