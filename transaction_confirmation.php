<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Transaction.php';

$page_title = 'Transaction Confirmation';
include 'includes/header.php';

$auth = new Auth();
$auth->requireLogin();

$transaction_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$is_update = isset($_GET['updated']) && $_GET['updated'] === 'true';

if ($transaction_id === 0) {
    header('Location: add_transaction.php');
    exit();
}

$transaction = new Transaction();
$tx = $transaction->getTransactionById($transaction_id);

if (!$tx) {
    // Handle transaction not found
    $_SESSION['error'] = 'Transaction not found.';
    header('Location: transactions.php');
    exit();
}

include 'includes/navbar.php';
?>

<div class="container py-4 page-animate">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm main-info-card">
                <div class="card-header <?php echo $is_update ? 'bg-warning text-dark' : 'bg-success text-white'; ?>">
                    <h4 class="mb-0">
                        <?php if ($is_update): ?>
                            <i class="fas fa-check-circle me-2"></i>Transaction Updated Successfully
                        <?php else: ?>
                            <i class="fas fa-check-circle me-2"></i>Transaction Added Successfully
                        <?php endif; ?>
                    </h4>
                </div>
                <div class="card-body">
                    <?php if ($is_update): ?>
                        <div class="alert alert-warning">
                            The transaction has been updated with the new values below.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success">
                            The following transaction has been recorded.
                        </div>
                    <?php endif; ?>

                    <h5 class="card-title mt-4">Transaction Details</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <tbody>
                                <tr>
                                    <th style="width: 30%;">Transaction ID</th>
                                    <td>#<?php echo htmlspecialchars($tx['id']); ?></td>
                                </tr>
                                <tr>
                                    <th>Type</th>
                                    <td>
                                        <span class="badge bg-<?php echo $tx['type'] === 'income' ? 'success' : 'danger'; ?>">
                                            <?php echo ucfirst(htmlspecialchars($tx['type'])); ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Category</th>
                                    <td><?php echo htmlspecialchars($tx['category_name'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Amount</th>
                                    <td>$<?php echo number_format($tx['amount'], 2); ?></td>
                                </tr>
                                <tr>
                                    <th>Date</th>
                                    <td><?php echo date('F j, Y', strtotime($tx['transaction_date'])); ?></td>
                                </tr>
                                <tr>
                                    <th>Description</th>
                                    <td><?php echo htmlspecialchars($tx['description']); ?></td>
                                </tr>
                                <tr>
                                    <th>Notes</th>
                                    <td><?php echo nl2br(htmlspecialchars($tx['notes'] ?? 'N/A')); ?></td>
                                </tr>
                                <tr>
                                    <th>Attachment</th>
                                    <td>
                                        <?php if (!empty($tx['attachment_path'])): ?>
                                            <a href="<?php echo htmlspecialchars($tx['attachment_path']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye me-1"></i> View Attachment
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">No attachment</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 d-flex justify-content-between">
                        <a href="add_transaction.php" class="btn btn-primary">
                            <i class="fas fa-plus-circle me-1"></i>Add Another Transaction
                        </a>
                        <a href="edit_transaction.php?id=<?php echo $tx['id']; ?>" class="btn btn-warning">
                            <i class="fas fa-edit me-1"></i>Edit This Transaction
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>