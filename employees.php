<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Employee.php';

$auth = new Auth();
$auth->requireLogin();

// Handle modal form submission
if ($_POST && isset($_POST['add_employee'])) {
    require_once 'classes/Employee.php';
    $employee = new Employee();
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
            $_SESSION['error'] = 'Please upload only PDF or DOC/DOCX files.';
        } elseif ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
            $_SESSION['error'] = 'File size must be less than 5MB.';
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
                $_SESSION['error'] = 'Failed to upload file. Please try again.';
            }
        }
    }

    // Validation
    if (empty($employee_id) || empty($first_name) || empty($last_name) || empty($position) || empty($monthly_salary)) {
        $_SESSION['error'] = 'Please fill in all required fields.';
    } elseif (!is_numeric($monthly_salary) || $monthly_salary <= 0) {
        $_SESSION['error'] = 'Please enter a valid monthly salary greater than 0.';
    } elseif ($hire_date && !strtotime($hire_date)) {
        $_SESSION['error'] = 'Please enter a valid hire date.';
    } elseif ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Please enter a valid email address.';
    } else {
        // Add employee
        try {
            if ($employee->addEmployee($employee_id, $first_name, $last_name, $email, $phone, $position, $monthly_salary, $hire_date, $attachment_path)) {
                $_SESSION['success'] = 'Employee added successfully!';
                header('Location: employees.php');
                exit();
            } else {
                $_SESSION['error'] = 'Failed to add employee. Employee ID may already exist.';
            }
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                $_SESSION['error'] = 'Employee ID already exists. Please choose a different ID.';
            } else {
                $_SESSION['error'] = 'An error occurred: ' . $e->getMessage();
            }
        }
    }

    // Redirect to refresh the page and show messages
    header('Location: employees.php');
    exit();
}

$page_title = 'Employees';
include 'includes/header.php';

require_once 'classes/Employee.php';
$employee = new Employee();

// Get all employees
$employees = $employee->getAllEmployees();
$employee_count = $employee->getEmployeeCount();

// Handle success/error messages from session
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

?>
<style>
    /* Custom hidden class for tailwind modal transition */
    .modal-hidden {
        display: none !important;
    }
</style>
<div class="page-animate w-full">
    <!-- Header Section -->
    <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div class="relative">
            <div class="flex items-center gap-4 mb-2">
                <h1 class="text-4xl font-black text-gray-900 tracking-tight m-0 flex items-center gap-3">
                    Employees
                </h1>
                <span class="bg-brand text-white text-[10px] font-black px-2.5 py-1 rounded-md shadow-lg shadow-brand/20 uppercase tracking-tighter">
                    <?php echo count($employees); ?> Active
                </span>
            </div>
            <p class="text-gray-500 font-medium text-sm m-0">
                Manage your organization's personnel, positions, and payroll structures.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="export_employees_pdf.php" class="h-11 px-5 bg-white text-rose-600 border-3 border-gray-100 rounded-xl font-bold text-xs uppercase tracking-widest hover:border-rose-500 transition-all flex items-center gap-2 shadow-sm" id="exportPdfBtn">
                <i class="fas fa-file-pdf text-[10px]"></i> Personnel PDF
            </a>
            <?php if ($employee_count < 15): ?>
                <button type="button" class="h-11 px-6 bg-brand text-white rounded-xl font-black text-xs uppercase tracking-widest hover:bg-black transition-all flex items-center gap-2 shadow-xl shadow-brand/20" id="openAddEmployee2025Modal">
                    <i class="fas fa-user-plus text-[10px]"></i> Add Personnel
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Professional Filters Section -->
    <div class="bg-white rounded-3xl border-3 border-gray-100 shadow-sm mb-10 overflow-hidden">
        <div class="p-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-8 h-8 rounded-lg bg-brand/10 text-brand flex items-center justify-center">
                    <i class="fas fa-filter text-xs"></i>
                </div>
                <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest">Filter Personnel</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 items-end">
                <div class="lg:col-span-2">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block ml-1">Search Directory</label>
                    <div class="relative group">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xs group-focus-within:text-brand transition-colors"></i>
                        <input type="text" id="searchInput" placeholder="Search name, ID, or position..." 
                               class="w-full h-12 pl-12 pr-4 bg-gray-50 border-3 border-gray-50 rounded-2xl text-sm font-bold text-gray-800 focus:bg-white focus:border-brand focus:ring-0 transition-all outline-none placeholder:text-gray-400 placeholder:font-medium">
                    </div>
                </div>

                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block ml-1">Employment Status</label>
                    <div class="relative group">
                        <i class="fas fa-user-tag absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xs group-focus-within:text-brand transition-colors"></i>
                        <select id="statusFilter" class="w-full h-12 pl-12 pr-10 bg-gray-50 border-3 border-gray-50 rounded-2xl text-sm font-bold text-gray-800 focus:bg-white focus:border-brand focus:ring-0 transition-all outline-none appearance-none cursor-pointer">
                            <option value="">All Statuses</option>
                            <option value="active">Active Employees</option>
                            <option value="inactive">Inactive / Past</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 text-[10px] pointer-events-none"></i>
                    </div>
                </div>

                <div class="flex gap-3">
                    <a href="salaries.php" class="flex-1 h-12 flex items-center justify-center gap-2 bg-indigo-50 text-indigo-600 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-indigo-600 hover:text-white transition-all border-3 border-transparent shadow-sm">
                        <i class="fas fa-money-check-alt"></i> Salaries
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Desktop View: Professional Table -->
    <div class="hidden md:block bg-white rounded-3xl border-3 border-gray-100 shadow-sm overflow-hidden mb-10">
        <div class="overflow-x-auto">
            <table id="employeeTable" class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Employee</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Position</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Location</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Monthly Salary</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Status</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php foreach ($employees as $emp): ?>
                    <tr class="hover:bg-gray-50/50 transition-all duration-200 group border-l-4 border-l-transparent hover:border-l-brand" data-status="<?php echo $emp['status']; ?>">
                        <td class="px-8 py-6">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-brand/10 text-brand flex items-center justify-center font-black text-sm border-2 border-brand/5 shadow-inner">
                                    <?php echo substr(htmlspecialchars($emp['first_name']), 0, 1) . substr(htmlspecialchars($emp['last_name']), 0, 1); ?>
                                </div>
                                <div>
                                    <div class="text-sm font-black text-gray-900 mb-0.5"><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></div>
                                    <div class="text-[10px] font-bold text-gray-400 flex items-center gap-2">
                                        <span class="text-brand"><?php echo htmlspecialchars($emp['employee_id']); ?></span>
                                        <span class="w-1 h-1 rounded-full bg-gray-200"></span>
                                        <span><?php echo htmlspecialchars($emp['email'] ?: 'No Email'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <span class="inline-flex items-center px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-tighter bg-indigo-50 text-indigo-700 border border-indigo-100">
                                <?php echo htmlspecialchars($emp['position']); ?>
                            </span>
                        </td>
                        <td class="px-8 py-6 text-center">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter bg-gray-50 text-gray-600 border border-gray-100">
                                <i class="fas fa-map-marker-alt text-brand"></i>
                                <?php echo htmlspecialchars($emp['location'] ?? 'Addis Ababa'); ?>
                            </span>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <div class="text-sm font-black text-gray-900 amount">
                                <?php echo CURRENCY_SYMBOL . number_format($emp['monthly_salary'], 2); ?>
                            </div>
                            <div class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mt-1">
                                Hired: <?php echo $emp['hire_date'] ? date('M d, Y', strtotime($emp['hire_date'])) : '-'; ?>
                            </div>
                        </td>
                        <td class="px-8 py-6 text-center">
                            <?php if (($emp['status'] ?? 'active') == 'active'): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-tighter bg-emerald-50 text-emerald-700 border border-emerald-100">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                                    Active
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-tighter bg-rose-50 text-rose-700 border border-rose-100">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-1.5"></span>
                                    Inactive
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-8 py-6">
                            <div class="flex items-center justify-center gap-2">
                                <button type="button" class="h-9 w-9 rounded-xl bg-gray-50 text-gray-400 flex items-center justify-center hover:bg-brand hover:text-white transition-all border border-gray-100 shadow-sm btn-view-details" data-employee-id="<?php echo $emp['id']; ?>">
                                    <i class="fas fa-eye text-[10px]"></i>
                                </button>
                                <a href="salary_report.php?employee_id=<?php echo $emp['id']; ?>" class="h-9 w-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center hover:bg-indigo-600 hover:text-white transition-all border border-indigo-100 shadow-sm">
                                    <i class="fas fa-file-invoice-dollar text-[10px]"></i>
                                </a>
                                <a href="edit_employee.php?id=<?php echo $emp['id']; ?>" class="h-9 w-9 rounded-xl bg-gray-50 text-gray-400 flex items-center justify-center hover:bg-blue-600 hover:text-white transition-all border border-gray-100 shadow-sm">
                                    <i class="fas fa-edit text-[10px]"></i>
                                </a>
                                <button onclick="if(confirm('Delete employee?')) window.location.href='delete_employee.php?id=<?php echo $emp['id']; ?>';" class="h-9 w-9 rounded-xl bg-gray-50 text-gray-400 flex items-center justify-center hover:bg-rose-600 hover:text-white transition-all border border-gray-100 shadow-sm">
                                    <i class="fas fa-trash-alt text-[10px]"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile View: Hybrid Cards -->
    <div class="md:hidden space-y-4 mb-10">
        <?php foreach ($employees as $emp): ?>
        <div class="bg-white rounded-3xl border-3 border-gray-100 shadow-sm p-6 group relative overflow-hidden" data-status="<?php echo $emp['status']; ?>">
            <!-- Status Accent -->
            <div class="absolute top-0 left-0 w-2 h-full <?php echo $emp['status'] == 'active' ? 'bg-emerald-500' : 'bg-gray-300'; ?>"></div>
            
            <div class="flex justify-between items-start mb-6">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-brand/10 text-brand flex items-center justify-center font-black text-lg border-2 border-brand/5 shadow-inner">
                        <?php echo substr(htmlspecialchars($emp['first_name']), 0, 1) . substr(htmlspecialchars($emp['last_name']), 0, 1); ?>
                    </div>
                    <div>
                        <div class="text-base font-black text-gray-900"><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></div>
                        <div class="text-[11px] font-bold text-brand uppercase tracking-widest"><?php echo htmlspecialchars($emp['employee_id']); ?></div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-base font-black text-gray-900 amount"><?php echo CURRENCY_SYMBOL . number_format($emp['monthly_salary'], 2); ?></div>
                    <div class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Monthly</div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="bg-gray-50/50 p-3 rounded-2xl border border-gray-100">
                    <div class="text-[9px] font-black text-gray-400 uppercase mb-1">Position</div>
                    <div class="text-[11px] font-bold text-indigo-600"><?php echo htmlspecialchars($emp['position']); ?></div>
                </div>
                <div class="bg-gray-50/50 p-3 rounded-2xl border border-gray-100">
                    <div class="text-[9px] font-black text-gray-400 uppercase mb-1">Status</div>
                    <div class="text-[11px] font-bold <?php echo $emp['status'] == 'active' ? 'text-emerald-600' : 'text-gray-500'; ?> capitalize"><?php echo $emp['status']; ?></div>
                </div>
            </div>

            <div class="flex gap-2">
                <button class="flex-1 h-11 bg-gray-900 text-white rounded-xl font-black text-[10px] uppercase tracking-widest flex items-center justify-center gap-2 btn-view-details" data-employee-id="<?php echo $emp['id']; ?>">
                    <i class="fas fa-eye"></i> Details
                </button>
                <a href="edit_employee.php?id=<?php echo $emp['id']; ?>" class="w-11 h-11 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center border border-blue-100">
                    <i class="fas fa-edit"></i>
                </a>
                <button onclick="if(confirm('Delete?')) window.location.href='delete_employee.php?id=<?php echo $emp['id']; ?>';" class="w-11 h-11 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center border border-rose-100">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Add Employee Modal (Tailwind version) -->
    <div id="addEmployee2025Modal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" id="addEmployeeModalBackdrop"></div>
        
        <!-- Modal Content -->
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto relative z-10 mx-4" id="addEmployeeModalContent">
            <div class="flex items-center justify-between p-6 border-b border-gray-100 bg-gray-50/50 sticky top-0 z-20">
                <h3 class="text-xl font-bold text-gray-800 flex items-center m-0">
                    <div class="w-10 h-10 rounded-full bg-brand/10 text-brand flex items-center justify-center mr-3">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    Add Employee
                </h3>
                <button type="button" class="text-gray-400 hover:text-gray-600 w-8 h-8 rounded-full hover:bg-gray-100 flex items-center justify-center transition-colors" id="closeAddEmployee2025Modal">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            
            <form method="POST" enctype="multipart/form-data" novalidate autocomplete="off" class="p-6 md:p-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-2">Employee ID</label>
                        <?php $modal_employee_id = 'MW-' . random_int(100000, 999999); ?>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-id-badge text-gray-400"></i>
                            </div>
                            <input type="text" class="input-premium w-full pl-10 bg-gray-50 text-gray-500 border-gray-200" id="employee_id_display" value="<?php echo $modal_employee_id; ?>" disabled>
                            <input type="hidden" name="employee_id" id="employee_id" value="<?php echo $modal_employee_id; ?>">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Employee ID is generated automatically.</p>
                    </div>

                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">First Name *</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                            <input type="text" class="input-premium w-full pl-10" id="first_name" name="first_name" required placeholder="Enter first name">
                        </div>
                    </div>

                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">Last Name *</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                            <input type="text" class="input-premium w-full pl-10" id="last_name" name="last_name" required placeholder="Enter last name">
                        </div>
                    </div>

                    <div>
                        <label for="position" class="block text-sm font-medium text-gray-700 mb-2">Position *</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-briefcase text-gray-400"></i>
                            </div>
                            <input type="text" class="input-premium w-full pl-10" id="position" name="position" required placeholder="e.g. Accountant, Manager">
                        </div>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </div>
                            <input type="email" class="input-premium w-full pl-10" id="email" name="email" required placeholder="example@email.com">
                        </div>
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-phone text-gray-400"></i>
                            </div>
                            <input type="text" class="input-premium w-full pl-10" id="phone" name="phone" placeholder="+1 234 567 8900">
                        </div>
                    </div>

                    <div>
                        <label for="monthly_salary" class="block text-sm font-medium text-gray-700 mb-2">Monthly Salary *</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-dollar-sign text-gray-400"></i>
                            </div>
                            <input type="number" step="0.01" class="input-premium w-full pl-10" id="monthly_salary" name="monthly_salary" required placeholder="5000">
                        </div>
                    </div>

                    <div>
                        <label for="hire_date" class="block text-sm font-medium text-gray-700 mb-2">Hire Date *</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-calendar-alt text-gray-400"></i>
                            </div>
                            <input type="date" class="input-premium w-full pl-10" id="hire_date" name="hire_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label for="attachment" class="block text-sm font-medium text-gray-700 mb-2">Attachment (PDF/DOC/DOCX)</label>
                        <div class="relative border-2 border-dashed border-gray-200 rounded-xl p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center justify-center mb-2">
                                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400"></i>
                            </div>
                            <input type="file" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-brand/10 file:text-brand hover:file:bg-brand/20 transition-colors cursor-pointer" id="attachment" name="attachment" accept=".pdf,.doc,.docx" onchange="validateFile(this)">
                        </div>
                        <p class="mt-2 text-xs text-gray-500 flex items-center">
                            <i class="fas fa-info-circle mr-1"></i>
                            Max size: 5MB. Supported: PDF, DOC, DOCX.
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100 mt-8">
                    <button type="button" class="px-6 py-2.5 bg-gray-100 text-gray-700 font-medium rounded-xl hover:bg-gray-200 transition-colors" id="closeAddEmployee2025ModalFooter">
                        Cancel
                    </button>
                    <button type="submit" name="add_employee" class="px-6 py-2.5 bg-brand text-white font-medium rounded-xl hover:bg-brand-dark transition-colors flex items-center shadow-md">
                        <i class="fas fa-save mr-2"></i>Save Employee
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Employee Details Modal -->
<div class="fixed inset-0 z-50 flex items-center justify-center hidden" id="employeeDetailsModal">
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" id="employeeDetailsModalBackdrop"></div>
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto relative z-10 mx-4">
        <div class="flex items-center justify-between p-6 border-b border-gray-100 bg-gray-50/50">
            <h5 class="text-xl font-bold text-gray-800 flex items-center m-0" id="employeeDetailsModalLabel">
                <i class="fas fa-id-card text-brand mr-3"></i>Employee Details
            </h5>
            <button type="button" class="text-gray-400 hover:text-gray-600 w-8 h-8 rounded-full hover:bg-gray-100 flex items-center justify-center transition-colors" id="closeEmployeeDetailsModal">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        <div class="p-6">
            <div id="employeeDetailsContent" class="space-y-4">
                <!-- Details will be loaded here via AJAX -->
                <div class="flex justify-center py-8">
                    <div class="spinner-border text-brand" role="status"></div>
                </div>
            </div>
        </div>
        <div class="p-6 border-t border-gray-100 bg-gray-50/50 flex justify-end">
            <button type="button" class="px-6 py-2.5 bg-gray-100 text-gray-700 font-medium rounded-xl hover:bg-gray-200 transition-colors" id="closeEmployeeDetailsModalFooter">Close</button>
        </div>
    </div>
</div>

<?php
$additional_scripts = '
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const searchInput = document.getElementById("searchInput");
        const statusFilter = document.getElementById("statusFilter");
        const exportBtn = document.getElementById("exportPdfBtn");

        function updateExportLink() {
            const search = searchInput.value;
            const status = statusFilter.value;
            exportBtn.href = `export_employees_pdf.php?search=${encodeURIComponent(search)}&status=${encodeURIComponent(status)}`;
        }

        if (searchInput && statusFilter && exportBtn) {
            searchInput.addEventListener("keyup", updateExportLink);
            statusFilter.addEventListener("change", updateExportLink);
            // Initialize link
            updateExportLink();
        }
    });

    function validateFile(input) {
        const file = input.files[0];
        const allowedTypes = ["application/pdf", "application/msword", "application/vnd.openxmlformats-officedocument.wordprocessingml.document"];
        const allowedExtensions = ["pdf", "doc", "docx"];
        const maxSize = 5 * 1024 * 1024; // 5MB

        if (file) {
            if (file.size > maxSize) {
                alert("File size must be less than 5MB.");
                input.value = "";
                return false;
            }
            const fileExtension = file.name.split(".").pop().toLowerCase();
            if (!allowedExtensions.includes(fileExtension) || !allowedTypes.includes(file.type)) {
                alert("Please select only PDF or DOC/DOCX files.");
                input.value = "";
                return false;
            }
        }
        return true;
    }
</script>
';
?>

<?php include 'includes/footer.php'; ?>