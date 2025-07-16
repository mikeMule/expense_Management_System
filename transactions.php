<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Transaction.php';

$page_title = 'Transactions';
include 'includes/header.php';

$auth = new Auth();
$auth->requireLogin();

$transaction = new Transaction();

// Handle filters
$filter_type = $_GET['type'] ?? '';
$filter_category = $_GET['category'] ?? '';
$filter_start_date = $_GET['start_date'] ?? '';
$filter_end_date = $_GET['end_date'] ?? '';
$search = $_GET['search'] ?? '';

// Get transactions based on filters
if ($filter_start_date && $filter_end_date) {
    $transactions = $transaction->getTransactionsByDateRange($filter_start_date, $filter_end_date, $filter_type ?: null);
} elseif ($filter_category) {
    $transactions = $transaction->getTransactionsByCategory($filter_category);
} else {
    $transactions = $transaction->getAllTransactions();
}

// Filter by search term if provided
if ($search) {
    $transactions = array_filter($transactions, function ($t) use ($search) {
        return stripos($t['description'], $search) !== false ||
            stripos($t['category_name'], $search) !== false ||
            stripos($t['notes'], $search) !== false;
    });
}

// Get categories for filter dropdown
$categories = $transaction->getCategories();

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
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="fas fa-exchange-alt me-2"></i>Transactions</h2>
                        <a href="add_transaction.php" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Add Transaction
                        </a>
                    </div>

                    <!-- Filters -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filters</h5>
                        </div>
                        <div class="card-body">
                            <form method="GET" class="row g-3" id="filterForm">
                                <div class="col-md-3">
                                    <label for="type" class="form-label">Type</label>
                                    <select class="form-select" id="type" name="type">
                                        <option value="">All Types</option>
                                        <option value="income" <?php echo $filter_type == 'income' ? 'selected' : ''; ?>>Income</option>
                                        <option value="expense" <?php echo $filter_type == 'expense' ? 'selected' : ''; ?>>Expense</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="category" class="form-label">Category</label>
                                    <select class="form-select" id="category" name="category">
                                        <option value="">All Categories</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo $cat['id']; ?>" <?php echo $filter_category == $cat['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($cat['name']); ?> (<?php echo ucfirst($cat['type']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date"
                                        value="<?php echo htmlspecialchars($filter_start_date); ?>">
                                </div>

                                <div class="col-md-2">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date"
                                        value="<?php echo htmlspecialchars($filter_end_date); ?>">
                                </div>

                                <div class="col-md-2">
                                    <label for="search" class="form-label">Search</label>
                                    <input type="text" class="form-control" id="search" name="search"
                                        placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i>Apply Filters
                                    </button>
                                    <a href="transactions.php" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i>Clear Filters
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Transactions Table -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                Transaction List (<?php echo count($transactions); ?> records)
                            </h5>
                            <div class="btn-group btn-group-sm">
                                <a href="export_csv.php?<?php echo http_build_query($_GET); ?>" class="btn btn-outline-success">
                                    <i class="fas fa-file-csv me-1"></i>Export CSV
                                </a>
                                <a href="export_pdf.php?<?php echo http_build_query($_GET); ?>" class="btn btn-outline-danger">
                                    <i class="fas fa-file-pdf me-1"></i>Export PDF
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (empty($transactions)): ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-4x mb-3"></i>
                                    <h4>No transactions found</h4>
                                    <p>No transactions match your current filters.</p>
                                    <a href="add_transaction.php" class="btn btn-primary">
                                        <i class="fas fa-plus me-1"></i>Add First Transaction
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Date</th>
                                                <th>Type</th>
                                                <th>Description</th>
                                                <th>Category</th>
                                                <th class="text-end">Amount</th>
                                                <th>Notes</th>
                                                <th class="no-print">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $total_income = 0;
                                            $total_expenses = 0;
                                            foreach ($transactions as $t):
                                                if ($t['type'] == 'income') {
                                                    $total_income += $t['amount'];
                                                } else {
                                                    $total_expenses += $t['amount'];
                                                }
                                            ?>
                                                <tr>
                                                    <td><?php echo date('M d, Y', strtotime($t['transaction_date'])); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $t['type'] == 'income' ? 'success' : 'danger'; ?>">
                                                            <i class="fas fa-<?php echo $t['type'] == 'income' ? 'arrow-up' : 'arrow-down'; ?> me-1"></i>
                                                            <?php echo ucfirst($t['type']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($t['description']); ?></strong>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary">
                                                            <?php echo htmlspecialchars($t['category_name'] ?? 'N/A'); ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-end">
                                                        <span class="fw-bold <?php echo $t['type'] == 'income' ? 'text-success' : 'text-danger'; ?>">
                                                            <?php echo $t['type'] == 'income' ? '+' : '-'; ?>$<?php echo number_format($t['amount'], 2); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if ($t['notes']): ?>
                                                            <span class="text-muted" title="<?php echo htmlspecialchars($t['notes']); ?>">
                                                                <?php echo strlen($t['notes']) > 30 ? substr(htmlspecialchars($t['notes']), 0, 30) . '...' : htmlspecialchars($t['notes']); ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="no-print">
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="edit_transaction.php?id=<?php echo $t['id']; ?>"
                                                                class="btn btn-outline-primary" title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <a href="delete_transaction.php?id=<?php echo $t['id']; ?>"
                                                                class="btn btn-outline-danger btn-delete"
                                                                data-item="transaction '<?php echo htmlspecialchars($t['description']); ?>'" title="Delete">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <th colspan="4">Totals:</th>
                                                <th class="text-end">
                                                    <div class="text-success">Income: +$<?php echo number_format($total_income, 2); ?></div>
                                                    <div class="text-danger">Expenses: -$<?php echo number_format($total_expenses, 2); ?></div>
                                                    <div class="fw-bold <?php echo ($total_income - $total_expenses) >= 0 ? 'text-success' : 'text-danger'; ?>">
                                                        Net: $<?php echo number_format($total_income - $total_expenses, 2); ?>
                                                    </div>
                                                </th>
                                                <th colspan="2"></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>