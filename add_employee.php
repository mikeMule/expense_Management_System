<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Employee.php';

$auth = new Auth();
$auth->requireLogin();

$page_title = 'Add Employee';
include 'includes/header.php';

$employee = new Employee();
$error = '';
$success = '';

// Check if we've reached the 10 employee limit
if (count($employee->getAllEmployees()) >= 10) {
    $_SESSION['error'] = 'Maximum 10 employees allowed.';
    header('Location: employees.php');
    exit();
}

// Handle form submission
if ($_POST) {
    $employee_id = trim($_POST['employee_id'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $monthly_salary = trim($_POST['monthly_salary'] ?? '');
    $hire_date = trim($_POST['hire_date'] ?? '');
    $attachment_path = null;

    // File upload handling
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['attachment'];
        $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $allowed_extensions = ['pdf', 'doc', 'docx'];

        // Get file extension
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Validate file type
        if (!in_array($file['type'], $allowed_types) || !in_array($file_extension, $allowed_extensions)) {
            $error = 'Please upload only PDF or DOC/DOCX files.';
        } elseif ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
            $error = 'File size must be less than 5MB.';
        } else {
            // Create uploads directory if it doesn't exist
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            // Generate unique filename
            $filename = 'emp_' . uniqid() . '_' . time() . '.' . $file_extension;
            $filepath = $upload_dir . $filename;

            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $attachment_path = $filepath;
            } else {
                $error = 'Failed to upload file. Please try again.';
            }
        }
    }

    // Validation
    if (empty($employee_id) || empty($first_name) || empty($last_name) || empty($position) || empty($monthly_salary)) {
        $error = 'Please fill in all required fields.';
    } elseif (!is_numeric($monthly_salary) || $monthly_salary <= 0) {
        $error = 'Please enter a valid monthly salary greater than 0.';
    } elseif ($hire_date && !strtotime($hire_date)) {
        $error = 'Please enter a valid hire date.';
    } elseif ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Add employee
        try {
            if ($employee->addEmployee($employee_id, $first_name, $last_name, $email, $phone, $position, $monthly_salary, $hire_date, $attachment_path)) {
                $_SESSION['success'] = 'Employee added successfully!';
                header('Location: employees.php');
                exit();
            } else {
                $error = 'Failed to add employee. Employee ID may already exist.';
            }
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                $error = 'Employee ID already exists. Please choose a different ID.';
            } else {
                $error = 'An error occurred: ' . $e->getMessage();
            }
        }
    }
}

?>

<div class="page-animate w-full max-w-4xl mx-auto">
    <!-- Header Section -->
    <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div class="relative">
            <div class="flex items-center gap-4 mb-2">
                <h1 class="text-4xl font-black text-gray-900 tracking-tight m-0 flex items-center gap-3">
                    Enrollment
                </h1>
                <span class="bg-brand text-white text-[10px] font-black px-2.5 py-1 rounded-md shadow-lg shadow-brand/20 uppercase tracking-tighter">
                    Append Personnel
                </span>
            </div>
            <p class="text-gray-500 font-medium text-sm m-0">
                Initialize a new operator profile in the organizational directory.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="employees.php" class="h-11 px-5 bg-white text-gray-900 border-3 border-gray-100 rounded-xl font-bold text-xs uppercase tracking-widest hover:border-black transition-all flex items-center gap-2 shadow-sm">
                <i class="fas fa-arrow-left text-[10px]"></i> Discard
            </a>
        </div>
    </div>

    <!-- Form Module -->
    <div class="bg-white rounded-[40px] border-3 border-gray-100 shadow-2xl shadow-black/5 overflow-hidden">
        <div class="p-8 md:p-12">
            <?php if ($error): ?>
                <div class="bg-rose-50 text-rose-700 p-5 rounded-2xl border-2 border-rose-100 mb-8 flex items-center">
                    <i class="fas fa-exclamation-circle text-rose-500 text-xl mr-4"></i>
                    <span class="font-black text-xs uppercase tracking-tight"><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="space-y-10" novalidate>
                <!-- Core Identity -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <label for="employee_id" class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block ml-1">Unique Identifier *</label>
                        <input type="text" class="w-full h-14 px-4 bg-gray-50 border-3 border-gray-50 text-gray-900 rounded-2xl focus:bg-white focus:border-brand outline-none transition-all font-bold text-sm" id="employee_id" name="employee_id"
                            value="<?php echo htmlspecialchars($employee_id ?? ''); ?>"
                            placeholder="e.g., EMP-1001" maxlength="20" required>
                    </div>

                    <div>
                        <label for="position" class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block ml-1">Organizational Role *</label>
                        <input type="text" class="w-full h-14 px-4 bg-gray-50 border-3 border-gray-50 text-gray-900 rounded-2xl focus:bg-white focus:border-brand outline-none transition-all font-bold text-sm" id="position" name="position"
                            value="<?php echo htmlspecialchars($position ?? ''); ?>"
                            placeholder="e.g., Financial Analyst" maxlength="100" required>
                    </div>
                </div>

                <!-- Personal Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <label for="first_name" class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block ml-1">Given Name *</label>
                        <input type="text" class="w-full h-14 px-4 bg-gray-50 border-3 border-gray-200 text-gray-900 rounded-2xl focus:bg-white focus:border-brand outline-none transition-all font-bold text-sm" id="first_name" name="first_name"
                            value="<?php echo htmlspecialchars($first_name ?? ''); ?>"
                            placeholder="Enter first name" maxlength="50" required>
                    </div>

                    <div>
                        <label for="last_name" class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block ml-1">Family Name *</label>
                        <input type="text" class="w-full h-14 px-4 bg-gray-50 border-3 border-gray-200 text-gray-900 rounded-2xl focus:bg-white focus:border-brand outline-none transition-all font-bold text-sm" id="last_name" name="last_name"
                            value="<?php echo htmlspecialchars($last_name ?? ''); ?>"
                            placeholder="Enter last name" maxlength="50" required>
                    </div>
                </div>

                <!-- Contact Vector -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <label for="email" class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block ml-1">Electronic Mail</label>
                        <input type="email" class="w-full h-14 px-4 bg-gray-50 border-3 border-gray-200 text-gray-900 rounded-2xl focus:bg-white focus:border-brand outline-none transition-all font-bold text-sm" id="email" name="email"
                            value="<?php echo htmlspecialchars($email ?? ''); ?>"
                            placeholder="name@organization.com" maxlength="100">
                    </div>

                    <div>
                        <label for="phone" class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block ml-1">Communication Line</label>
                        <input type="tel" class="w-full h-14 px-4 bg-gray-50 border-3 border-gray-200 text-gray-900 rounded-2xl focus:bg-white focus:border-brand outline-none transition-all font-bold text-sm" id="phone" name="phone"
                            value="<?php echo htmlspecialchars($phone ?? ''); ?>"
                            placeholder="+251 ..." maxlength="20">
                    </div>
                </div>

                <!-- Remuneration & Timeline -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <label for="monthly_salary" class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block ml-1">Base Remuneration (<?php echo CURRENCY_SYMBOL; ?>) *</label>
                        <input type="number" class="w-full h-14 px-4 bg-gray-50 border-3 border-gray-200 text-gray-900 rounded-2xl focus:bg-white focus:border-brand outline-none transition-all font-black text-sm amount" id="monthly_salary" name="monthly_salary"
                            value="<?php echo htmlspecialchars($monthly_salary ?? ''); ?>"
                            step="0.01" min="0.01" placeholder="0.00" required>
                    </div>

                    <div>
                        <label for="hire_date" class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block ml-1">Commencement Date</label>
                        <input type="date" class="w-full h-14 px-4 bg-gray-50 border-3 border-gray-200 text-gray-900 rounded-2xl focus:bg-white focus:border-brand outline-none transition-all font-bold text-sm" id="hire_date" name="hire_date"
                            value="<?php echo htmlspecialchars($hire_date ?? date('Y-m-d')); ?>"
                            max="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>

                <!-- Credential Repository -->
                <div class="p-8 bg-gray-50 rounded-3xl border-2 border-dashed border-gray-200 group hover:border-brand transition-all">
                    <label for="attachment" class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4 block text-center">Personnel Credentials</label>
                    <div class="flex flex-col items-center gap-4">
                        <div class="w-16 h-16 rounded-2xl bg-white border-2 border-gray-100 flex items-center justify-center text-gray-300 group-hover:text-brand transition-colors">
                            <i class="fas fa-file-contract text-2xl"></i>
                        </div>
                        <input type="file" class="hidden" id="attachment" name="attachment" accept=".pdf,.doc,.docx" onchange="validateFile(this)">
                        <button type="button" onclick="document.getElementById('attachment').click()" class="h-10 px-6 bg-white border-2 border-gray-200 rounded-xl text-[9px] font-black uppercase tracking-widest hover:border-black transition-all">
                            Browse Local Repository
                        </button>
                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-tighter">PDF, DOC, DOCX • MAX 5MB</p>
                    </div>
                </div>

                <div class="bg-indigo-50/50 p-6 rounded-2xl border-2 border-indigo-100 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-indigo-500 text-white flex items-center justify-center flex-shrink-0 shadow-lg shadow-indigo-500/20">
                        <i class="fas fa-info-circle text-sm"></i>
                    </div>
                    <p class="text-[10px] font-bold text-indigo-700 uppercase tracking-tight m-0">
                        Personnel will be initialized with <strong>Active Status</strong>. Enrollment capacity is limited to 10 active operators.
                    </p>
                </div>

                <!-- Execution Layer -->
                <div class="flex justify-end pt-10 border-t border-gray-100">
                    <button type="submit" class="w-full sm:w-64 h-16 bg-black text-white rounded-2xl font-black text-xs uppercase tracking-[0.2em] hover:bg-gray-800 transition-all shadow-2xl shadow-black/20 flex items-center justify-center gap-3">
                        <i class="fas fa-user-check"></i> Finalize Enrollment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function validateFile(input) {
    const file = input.files[0];
    const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    const allowedExtensions = ['pdf', 'doc', 'docx'];
    const maxSize = 5 * 1024 * 1024; // 5MB
    
    if (file) {
        // Check file size
        if (file.size > maxSize) {
            alert('File size must be less than 5MB.');
            input.value = '';
            return false;
        }
        
        // Check file type
        const fileExtension = file.name.split('.').pop().toLowerCase();
        if (!allowedExtensions.includes(fileExtension) || !allowedTypes.includes(file.type)) {
            alert('Please select only PDF or DOC/DOCX files.');
            input.value = '';
            return false;
        }
    }
    
    return true;
}

// Form validation
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();
</script>

<?php include 'includes/footer.php'; ?>