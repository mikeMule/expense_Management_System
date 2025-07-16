<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Employee.php';

$page_title = 'Pay Salary';
include 'includes/header.php';

$auth = new Auth();
$auth->requireLogin();

$employee = new Employee();
$error = '';
$success = '';

// Get salary payment ID
$payment_id = $_GET['id'] ?? 0;

if (!$payment_id) {
    $_SESSION['error'] = 'Invalid payment ID.';
    header('Location: salaries.php');
    exit();
}

// Get salary payment details
$salary_payments = $employee->getSalaryPayments();
$payment_data = null;

foreach ($salary_payments as $payment) {
    if ($payment['id'] == $payment_id) {
        $payment_data = $payment;
        break;
    }
}

if (!$payment_data) {
    $_SESSION['error'] = 'Salary payment not found.';
    header('Location: salaries.php');
    exit();
}

if ($payment_data['status'] == 'paid') {
    $_SESSION['error'] = 'This salary has already been paid.';
    header('Location: salaries.php');
    exit();
}

// Handle form submission
if ($_POST) {
    $payment_date = trim($_POST['payment_date'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    // Validation
    if (empty($payment_date)) {
        $error = 'Please select a payment date.';
    } elseif (!strtotime($payment_date)) {
        $error = 'Please enter a valid payment date.';
    } elseif (strtotime($payment_date) > time()) {
        $error = 'Payment date cannot be in the future.';
    } else {
        // Mark salary as paid
        try {
            if ($employee->paySalary($payment_id, $payment_date)) {
                $_SESSION['success'] = 'Salary payment recorded successfully!';
                header('Location: salaries.php');
                exit();
            } else {
                $error = 'Failed to record salary payment. Please try again.';
            }
        } catch (Exception $e) {
            $error = 'An error occurred: ' . $e->getMessage();
        }
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
</style>

<div class="page-animate">
    <div class="main-info-card">
        <div class="container py-4">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h4 class="mb-0">
                                <i class="fas fa-dollar-sign me-2"></i>Pay Salary
                            </h4>
                        </div>
                        <div class="card-body">
                            <?php if ($error): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($success): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Employee and Salary Details -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">Salary Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-sm-4"><strong>Employee:</strong></div>
                                        <div class="col-sm-8">
                                            <?php echo htmlspecialchars($payment_data['first_name'] . ' ' . $payment_data['last_name']); ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($payment_data['employee_id']); ?></small>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-sm-4"><strong>Position:</strong></div>
                                        <div class="col-sm-8">
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($payment_data['position']); ?></span>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-sm-4"><strong>Period:</strong></div>
                                        <div class="col-sm-8">
                                            <?php echo date('F Y', mktime(0, 0, 0, $payment_data['month'], 1, $payment_data['year'])); ?>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-sm-4"><strong>Amount:</strong></div>
                                        <div class="col-sm-8">
                                            <span class="h5 text-success">$<?php echo number_format($payment_data['amount'], 2); ?></span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4"><strong>Status:</strong></div>
                                        <div class="col-sm-8">
                                            <span class="badge bg-warning">
                                                <i class="fas fa-clock me-1"></i>Pending
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <form method="POST" class="needs-validation" novalidate>
                                <div class="mb-3">
                                    <label for="payment_date" class="form-label">
                                        <i class="fas fa-calendar me-1"></i>Payment Date *
                                    </label>
                                    <input type="date" class="form-control" id="payment_date" name="payment_date"
                                        value="<?php echo htmlspecialchars($payment_date ?? date('Y-m-d')); ?>"
                                        max="<?php echo date('Y-m-d'); ?>" required>
                                    <div class="invalid-feedback">
                                        Please select a payment date.
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="notes" class="form-label">
                                        <i class="fas fa-sticky-note me-1"></i>Payment Notes
                                    </label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3"
                                        placeholder="Optional notes about this payment"><?php echo htmlspecialchars($notes ?? ''); ?></textarea>
                                </div>

                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Note:</strong> This action will mark the salary as paid and cannot be undone. Make sure you have actually processed the payment before confirming.
                                </div>

                                <div class="d-flex justify-content-between">
                                    <a href="salaries.php" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-1"></i>Back to Salaries
                                    </a>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-check me-1"></i>Confirm Payment
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>