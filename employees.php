<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Employee.php';

$page_title = 'Employees';
include 'includes/header.php';

$auth = new Auth();
$auth->requireLogin();

$employee = new Employee();

// Handle success/error messages
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

// Get all employees
$employees = $employee->getAllEmployees();
$employee_count = $employee->getEmployeeCount();

include 'includes/navbar.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-users me-2"></i>Employee Management</h2>
                <?php if ($employee_count < 10): ?>
                <a href="add_employee.php" class="btn btn-primary">
                    <i class="fas fa-user-plus me-1"></i>Add Employee
                </a>
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

            <!-- Search and Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
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
                    </div>
                </div>
            </div>

            <!-- Employees Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Employee List</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($employees)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-user-plus fa-4x mb-3"></i>
                            <h4>No employees found</h4>
                            <p>Add your first employee to get started.</p>
                            <a href="add_employee.php" class="btn btn-primary">
                                <i class="fas fa-user-plus me-1"></i>Add First Employee
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
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
        </div>
    </div>
</div>

<script>
// Search functionality
document.getElementById('searchInput').addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const tableRows = document.querySelectorAll('tbody tr');
    
    tableRows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Status filter
document.getElementById('statusFilter').addEventListener('change', function() {
    const selectedStatus = this.value;
    const tableRows = document.querySelectorAll('tbody tr');
    
    tableRows.forEach(row => {
        const status = row.getAttribute('data-status');
        if (selectedStatus === '' || status === selectedStatus) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>