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

// Handle success/error messages from session
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

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
                        <h2><i class="fas fa-users me-2"></i>Employee List (<span class="employee-count-blinker"><?php echo count($employees); ?></span>)</h2>
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
                                                        <?php $status_class = $emp['status'] == 'active' ? 'status-select-active' : 'status-select-inactive'; ?>
                                                        <select class="form-select form-select-sm employee-status-select <?php echo $status_class; ?>" data-employee-id="<?php echo $emp['id']; ?>" data-original-status="<?php echo $emp['status']; ?>">
                                                            <option value="active" <?php echo $emp['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                                            <option value="inactive" <?php echo $emp['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                        </select>
                                                        <div class="spinner-border spinner-border-sm text-primary ms-2 d-none" role="status">
                                                            <span class="visually-hidden">Loading...</span>
                                                        </div>
                                                    </td>
                                                    <td class="no-print">
                                                        <div class="btn-group btn-group-sm">
                                                            <button type="button" class="btn btn-outline-info btn-view-details" data-employee-id="<?php echo $emp['id']; ?>" title="View Details">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
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
                                                <input type="text" class="form-control" id="hire_date" name="hire_date" value="<?php echo date('Y-m-d'); ?>" required placeholder="YYYY-MM-DD">
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

<!-- Employee Details Modal -->
<div class="modal fade" id="employeeDetailsModal" tabindex="-1" aria-labelledby="employeeDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="employeeDetailsModalLabel">Employee Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="employeeDetailsContent">
                    <!-- Details will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>