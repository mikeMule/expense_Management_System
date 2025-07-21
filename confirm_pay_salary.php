<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Employee.php';
require_once 'classes/Transaction.php'; // Ensure Transaction class is included

session_start();
$auth = new Auth();
$auth->requireLogin();

$employee = new Employee();
$transaction = new Transaction(); // Create a new transaction object

$payment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$payment_id) {
    $_SESSION['error'] = 'Invalid salary payment ID.';
    header('Location: salaries.php');
    exit();
}

$salary_payment = $employee->getSalaryPaymentById($payment_id);

if (!$salary_payment) {
    $_SESSION['error'] = 'Salary payment not found.';
    header('Location: salaries.php');
    exit();
}

if ($salary_payment['status'] === 'paid') {
    $_SESSION['error'] = 'This salary payment has already been marked as paid.';
    header('Location: salaries.php');
    exit();
}

// Handle payment confirmation
if ($_POST && isset($_POST['confirm_payment'])) {
    try {
        $result = $employee->markSalaryAsPaid($payment_id);

        if ($result) {
            $updated_salary = $employee->getSalaryPaymentById($payment_id);
            if ($updated_salary) {
                // Ensure there is a 'Salaries' category
                $salaries_category_id = $transaction->getOrCreateCategory('Salaries', 'expense');

                // Create a corresponding transaction
                $description = "Salary: " . $updated_salary['first_name'] . " " . $updated_salary['last_name'];
                $notes = "Payment for " . date('F Y', mktime(0, 0, 0, $updated_salary['month'], 1, $updated_salary['year'])) . "\n[salary_payment_id:{$payment_id}]";
                $transaction_date = date('Y-m-d', strtotime($updated_salary['payment_date']));

                $transaction->addTransaction(
                    'expense',
                    $salaries_category_id,
                    $updated_salary['amount'],
                    $description,
                    $transaction_date,
                    $notes
                );

                $_SESSION['success'] = 'Salary payment confirmed and transaction recorded successfully.';
            } else {
                $_SESSION['error'] = 'Could not retrieve salary details to create transaction.';
            }
        } else {
            $_SESSION['error'] = 'Failed to mark salary as paid.';
        }
    } catch (Exception $e) {
        error_log("Salary payment confirmation error: " . $e->getMessage());
        $_SESSION['error'] = 'An unexpected error occurred during salary payment confirmation.';
    }

    header('Location: salaries.php');
    exit();
}

$page_title = 'Confirm Salary Payment';
include 'includes/header.php';
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
        <div class="container-fluid py-4">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-warning">
                        <div class="card-header bg-warning text-dark">
                            <h4 class="mb-0">
                                <i class="fas fa-dollar-sign me-2"></i>Confirm Salary Payment
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Please review the salary payment details below before confirming.</strong>
                                <br>This action will mark the salary as paid and create a corresponding transaction record.
                            </div>

                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Employee Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Employee Name:</strong><br>
                                            <span class="text-primary"><?php echo htmlspecialchars($salary_payment['first_name'] . ' ' . $salary_payment['last_name']); ?></span>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Employee ID:</strong><br>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($salary_payment['employee_id']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Payment Details</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Payment Period:</strong><br>
                                            <span class="text-info"><?php echo date('F Y', mktime(0, 0, 0, $salary_payment['month'], 1, $salary_payment['year'])); ?></span>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Amount:</strong><br>
                                            <span class="text-success fw-bold fs-5">$<?php echo number_format($salary_payment['amount'], 2); ?></span>
                                        </div>
                                    </div>
                                    <?php if (!empty($salary_payment['notes'])): ?>
                                        <hr>
                                        <div class="row">
                                            <div class="col-12">
                                                <strong>Notes:</strong><br>
                                                <span class="text-muted"><?php echo htmlspecialchars($salary_payment['notes']); ?></span>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>What will happen?</h5>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li><i class="fas fa-check text-success me-2"></i>Salary status will be changed to "Paid"</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Payment date will be set to current date/time</li>
                                        <li><i class="fas fa-check text-success me-2"></i>A new transaction record will be created in the expense category "Salaries"</li>
                                        <li><i class="fas fa-check text-success me-2"></i>The transaction will appear in your transaction list</li>
                                    </ul>
                                </div>
                            </div>

                            <form method="POST">
                                <div class="d-flex justify-content-between">
                                    <a href="salaries.php" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-1"></i>Cancel
                                    </a>
                                    <button type="submit" name="confirm_payment" class="btn btn-success">
                                        <i class="fas fa-check-circle me-1"></i>Confirm Payment
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