<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Employee.php';

$page_title = 'Employees';
include 'includes/header.php';

$auth = new Auth();
$auth->requireLogin();

$employee = new Employee();

// Get all employees
$employees = $employee->getAllEmployees();
$employee_count = $employee->getEmployeeCount();

// Handle success/error messages
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

// Handle add employee form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_employee'])) {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $monthly_salary = floatval($_POST['monthly_salary'] ?? 0);
    $hire_date = trim($_POST['hire_date'] ?? date('Y-m-d'));
    $employee_id = $_POST['employee_id'] ?? ('MW-' . random_int(100000, 999999));
    if ($first_name && $last_name && $position && $monthly_salary > 0 && $hire_date && $email) {
        $max_attempts = 5;
        $attempt = 0;
        $success_add = false;
        while ($attempt < $max_attempts && !$success_add) {
            // Check for duplicate employee_id
            $duplicate = false;
            foreach ($employees as $emp) {
                if ($emp['employee_id'] === $employee_id) {
                    $duplicate = true;
                    break;
                }
            }
            if ($duplicate) {
                $employee_id = 'MW-' . random_int(100000, 999999);
                $attempt++;
                continue;
            }
            $result = $employee->addEmployee($employee_id, $first_name, $last_name, $email, $phone, $position, $monthly_salary, $hire_date);
            if ($result) {
                $success = 'Employee added successfully. Employee ID: ' . $employee_id;
                $success_add = true;
            } else {
                $error = 'Failed to add employee. Employee ID may already exist.';
                $attempt++;
                $employee_id = 'MW-' . random_int(100000, 999999);
            }
        }
        if (!$success_add) {
            $error = 'Failed to add employee after multiple attempts. Please try again.';
        }
    } else {
        $error = 'Please fill all required fields.';
    }
}

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

    /* Hide backdrop for Add Employee modal only */
    #addEmployeeModal+.modal-backdrop,
    #addEmployeeModal~.modal-backdrop {
        display: none !important;
        opacity: 0 !important;
    }
</style>
<div class="page-animate">
    <div class="main-info-card">
        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="fas fa-users me-2"></i>Employee Management</h2>
                        <?php if ($employee_count < 10): ?>
                            <button type="button" class="btn btn-primary" id="openAddEmployee2025Modal" <?php if ($employee_count >= 10) echo 'disabled'; ?>>
                                <i class="fas fa-user-plus me-1"></i>Add Employee
                            </button>
                        <?php else: ?>
                            <span class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>Maximum 10 employees reached
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Employee Statistics -->
                    <div class="row mb-4">
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3 class="stat-value text-primary"><?php echo count($employees); ?></h3>
                                            <p class="stat-label">Total Employees</p>
                                        </div>
                                        <i class="fas fa-users fa-2x text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3 class="stat-value text-success"><?php echo $employee_count; ?></h3>
                                            <p class="stat-label">Active Employees</p>
                                        </div>
                                        <i class="fas fa-user-check fa-2x text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3 class="stat-value text-info">$<?php echo number_format($employee->getTotalMonthlySalaries(), 2); ?></h3>
                                            <p class="stat-label">Monthly Payroll</p>
                                        </div>
                                        <i class="fas fa-money-check-alt fa-2x text-info"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3 class="stat-value text-warning"><?php echo 10 - count($employees); ?></h3>
                                            <p class="stat-label">Available Slots</p>
                                        </div>
                                        <i class="fas fa-user-plus fa-2x text-warning"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filters</h5>
                        </div>
                        <div class="card-body">
                            <form class="row g-3" id="filterForm">
                                <div class="col-md-6">
                                    <input type="text" class="form-control" id="searchInput" placeholder="Search employees...">
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" id="statusFilter">
                                        <option value="">All Status</option>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <a href="salaries.php" class="btn btn-info w-100">
                                        <i class="fas fa-money-check-alt me-1"></i>Manage Salaries
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Employees Table -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Employee List</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($employees)): ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="fas fa-user-plus fa-4x mb-3"></i>
                                    <h4>No employees found</h4>
                                    <p>Add your first employee to get started.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table id="employeeTable" class="table table-hover table-bordered align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Employee ID</th>
                                                <th>Name</th>
                                                <th>Position</th>
                                                <th>Contact</th>
                                                <th>Monthly Salary</th>
                                                <th>Hire Date</th>
                                                <th>Status</th>
                                                <th class="no-print">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($employees as $emp): ?>
                                                <tr data-status="<?php echo $emp['status']; ?>">
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($emp['employee_id']); ?></strong>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <strong><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></strong>
                                                            <?php if ($emp['email']): ?>
                                                                <br><small class="text-muted"><?php echo htmlspecialchars($emp['email']); ?></small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary">
                                                            <?php echo htmlspecialchars($emp['position']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if ($emp['phone']): ?>
                                                            <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($emp['phone']); ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <strong class="text-success">$<?php echo number_format($emp['monthly_salary'], 2); ?></strong>
                                                    </td>
                                                    <td>
                                                        <?php echo $emp['hire_date'] ? date('M d, Y', strtotime($emp['hire_date'])) : '-'; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $emp['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                                            <i class="fas fa-<?php echo $emp['status'] == 'active' ? 'check' : 'times'; ?> me-1"></i>
                                                            <?php echo ucfirst($emp['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td class="no-print">
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="edit_employee.php?id=<?php echo $emp['id']; ?>"
                                                                class="btn btn-outline-primary" title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <a href="delete_employee.php?id=<?php echo $emp['id']; ?>"
                                                                class="btn btn-outline-danger btn-delete"
                                                                data-item="employee '<?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?>'" title="Delete">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Add Employee Modal (2025Modal) -->
                    <button type="button" class="btn btn-primary" id="openAddEmployee2025Modal" <?php if ($employee_count >= 10) echo 'disabled'; ?>>
                        <i class="fas fa-user-plus me-1"></i>Add Employee
                    </button>
                    <div class="modal2025-overlay hide" id="addEmployee2025Modal">
                        <div class="modal2025-content">
                            <div class="modal2025-header">
                                <span class="modal2025-title"><i class="fas fa-user-plus me-2"></i>Add Employee</span>
                                <button type="button" class="modal2025-close" id="closeAddEmployee2025Modal" aria-label="Close">&times;</button>
                            </div>
                            <form method="POST" class="needs-validation" novalidate autocomplete="off">
                                <div class="container-fluid">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="employee_id" class="form-label">Employee ID</label>
                                            <?php $modal_employee_id = 'MW-' . random_int(100000, 999999); ?>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-id-badge"></i></span>
                                                <input type="text" class="form-control" id="employee_id_display" value="<?php echo $modal_employee_id; ?>" disabled placeholder="Auto-generated">
                                                <input type="hidden" name="employee_id" id="employee_id" value="<?php echo $modal_employee_id; ?>">
                                            </div>
                                            <div class="form-text text-muted">Employee ID is generated automatically.</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="first_name" class="form-label">First Name</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                                <input type="text" class="form-control" id="first_name" name="first_name" required placeholder="Enter first name">
                                            </div>
                                            <div class="invalid-feedback">First name is required.</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="last_name" class="form-label">Last Name</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                                <input type="text" class="form-control" id="last_name" name="last_name" required placeholder="Enter last name">
                                            </div>
                                            <div class="invalid-feedback">Last name is required.</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="position" class="form-label">Position</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-briefcase"></i></span>
                                                <input type="text" class="form-control" id="position" name="position" required placeholder="e.g. Accountant, Manager">
                                            </div>
                                            <div class="invalid-feedback">Position is required.</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="email" class="form-label">Email</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                                <input type="email" class="form-control" id="email" name="email" required placeholder="example@email.com">
                                            </div>
                                            <div class="invalid-feedback">Email is required.</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="phone" class="form-label">Phone</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                                <input type="text" class="form-control" id="phone" name="phone" placeholder="Optional: +1 234 567 8900">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="monthly_salary" class="form-label">Monthly Salary</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                                <input type="number" step="0.01" class="form-control" id="monthly_salary" name="monthly_salary" required placeholder="e.g. 5000">
                                            </div>
                                            <div class="invalid-feedback">Monthly salary is required.</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="hire_date" class="form-label">Hire Date</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                                <input type="date" class="form-control" id="hire_date" name="hire_date" value="<?php echo date('Y-m-d'); ?>" required placeholder="YYYY-MM-DD">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal2025-footer">
                                    <button type="button" class="btn btn-secondary" id="closeAddEmployee2025ModalFooter">Cancel</button>
                                    <button type="submit" name="add_employee" class="btn btn-success"><i class="fas fa-plus me-1"></i>Add Employee</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- DataTables JS & CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        var employeeTable = $('#employeeTable').DataTable({
            responsive: true,
            pageLength: 10,
            language: {
                search: "<i class='fas fa-search'></i> Search:",
                searchPlaceholder: "Type to filter employees..."
            },
            columnDefs: [{
                    targets: [0],
                    className: 'fw-bold text-primary'
                },
                {
                    targets: '_all',
                    className: 'align-middle'
                }
            ]
        });
        // Search functionality
        $('#searchInput').on('keyup', function() {
            employeeTable.search(this.value).draw();
        });
        // Status filter
        $('#statusFilter').on('change', function() {
            employeeTable.column(6).search(this.value).draw();
        });
        // Fix for modal freeze: re-initialize modal on show
        var addEmployeeModal = document.getElementById('addEmployeeModal');
        if (addEmployeeModal) {
            addEmployeeModal.addEventListener('show.bs.modal', function() {
                document.body.classList.add('modal-open');
                setTimeout(function() {
                    // Add 2025Modal class to the backdrop
                    var backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) {
                        backdrop.classList.add('2025Modal');
                    }
                }, 10);
            });
            addEmployeeModal.addEventListener('hidden.bs.modal', function() {
                document.body.classList.remove('modal-open');
                // Remove 2025Modal class from the backdrop
                var backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.classList.remove('2025Modal');
                }
            });
        }
    });
</script>
<script>
    // Modal 2025 open/close logic
    const openBtn = document.getElementById('openAddEmployee2025Modal');
    const modal2025 = document.getElementById('addEmployee2025Modal');
    const closeBtn = document.getElementById('closeAddEmployee2025Modal');
    const closeBtnFooter = document.getElementById('closeAddEmployee2025ModalFooter');
    if (openBtn && modal2025) {
        openBtn.addEventListener('click', function() {
            modal2025.classList.remove('hide');
            modal2025.style.display = 'flex';
        });
    }

    function close2025Modal() {
        modal2025.classList.add('hide');
        setTimeout(() => {
            modal2025.style.display = 'none';
        }, 300);
    }
    if (closeBtn) closeBtn.addEventListener('click', close2025Modal);
    if (closeBtnFooter) closeBtnFooter.addEventListener('click', close2025Modal);
    // Close on overlay click
    modal2025.addEventListener('click', function(e) {
        if (e.target === modal2025) close2025Modal();
    });
</script>
<?php include 'includes/footer.php'; ?>