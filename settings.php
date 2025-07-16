<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';

session_start();

$auth = new Auth();
$auth->requireLogin();

$page_title = 'Settings';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title . ' - ' . APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>

<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="container py-5">
        <div class="row justify-content-center" style="margin-top: 2.5rem; margin-bottom: 2.5rem;">
            <div class="col-lg-7">
                <div class="card" style="padding-top: 2.5rem; padding-bottom: 2.5rem;">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-cog me-2"></i>Settings</h4>
                    </div>
                    <div class="card-body">
                        <h5 class="mb-4">General Settings</h5>
                        <!-- Category Management -->
                        <div class="mb-5 p-4 bg-light rounded-4 shadow-sm">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0"><i class="fas fa-folder-plus me-2 text-primary"></i>Manage Categories</h5>
                                <button class="btn btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#addCategoryCollapse" aria-expanded="false" aria-controls="addCategoryCollapse">
                                    <i class="fas fa-plus"></i> Add Category
                                </button>
                            </div>
                            <div class="collapse mb-4" id="addCategoryCollapse">
                                <form class="row g-2" method="POST" action="add_category.php">
                                    <div class="col-6">
                                        <input type="text" class="form-control" name="category_name" placeholder="Category Name" required>
                                    </div>
                                    <div class="col-4">
                                        <select class="form-select" name="category_type" required>
                                            <option value="">Select Type</option>
                                            <option value="income">Income</option>
                                            <option value="expense">Expense</option>
                                        </select>
                                    </div>
                                    <div class="col-2">
                                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-plus"></i> Add</button>
                                    </div>
                                </form>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Type</th>
                                            <th>Created</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        try {
                                            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';port=' . DB_PORT . ';charset=utf8mb4';
                                            $pdo = new PDO($dsn, DB_USER, DB_PASS);
                                            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                            $stmt = $pdo->query('SELECT * FROM categories ORDER BY created_at DESC');
                                            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                            foreach ($categories as $cat) {
                                                echo '<tr>';
                                                echo '<td>' . htmlspecialchars($cat['name']) . '</td>';
                                                echo '<td><span class="badge bg-' . ($cat['type'] === 'income' ? 'success' : 'danger') . ' text-capitalize">' . htmlspecialchars($cat['type']) . '</span></td>';
                                                echo '<td>' . date('M d, Y', strtotime($cat['created_at'])) . '</td>';
                                                echo '<td>';
                                                echo '<button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteCategoryModal" data-category-id="' . $cat['id'] . '" data-category-name="' . htmlspecialchars($cat['name']) . '"><i class="fas fa-trash"></i></button>';
                                                echo '</td>';
                                                echo '</tr>';
                                            }
                                        } catch (Exception $e) {
                                            echo '<tr><td colspan="4" class="text-center text-danger">Error loading categories.</td></tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Backup Database -->
                        <div class="mb-5 p-4 bg-light rounded-4 shadow-sm">
                            <h6><i class="fas fa-database me-2 text-success"></i>Backup Database</h6>
                            <form method="POST" action="backup_db.php" class="mb-3">
                                <button type="submit" class="btn btn-success"><i class="fas fa-download me-1"></i>Backup Now</button>
                            </form>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Backup File</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $backupDir = __DIR__ . '/BackupDB/';
                                        if (is_dir($backupDir)) {
                                            $files = array_diff(scandir($backupDir, SCANDIR_SORT_DESCENDING), array('.', '..'));
                                            foreach ($files as $file) {
                                                $filePath = 'BackupDB/' . $file;
                                                $date = date('M d, Y H:i', filemtime($backupDir . $file));
                                                echo '<tr>';
                                                echo '<td>' . htmlspecialchars($file) . '</td>';
                                                echo '<td>' . $date . '</td>';
                                                echo '<td class="d-flex gap-2">';
                                                echo '<a href="' . $filePath . '" class="btn btn-sm btn-primary" download><i class="fas fa-download"></i> Download</a>';
                                                echo '<a href="delete_backup.php?file=' . urlencode($file) . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Delete this backup file?\')"><i class="fas fa-trash"></i> Delete</a>';
                                                echo '</td>';
                                                echo '</tr>';
                                            }
                                        } else {
                                            echo '<tr><td colspan="3" class="text-center text-muted">No backups found.</td></tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Manage Email Config -->
                        <div class="mb-5 p-4 bg-light rounded-4 shadow-sm">
                            <h6><i class="fas fa-envelope me-2 text-info"></i>Manage Email Config</h6>
                            <form class="row g-2" method="POST" action="update_email_config.php">
                                <div class="col-6">
                                    <input type="email" class="form-control" name="email_from" placeholder="From Email" required>
                                </div>
                                <div class="col-6">
                                    <input type="text" class="form-control" name="smtp_host" placeholder="SMTP Host" required>
                                </div>
                                <div class="col-6">
                                    <input type="text" class="form-control" name="smtp_user" placeholder="SMTP Username" required>
                                </div>
                                <div class="col-6">
                                    <input type="password" class="form-control" name="smtp_pass" placeholder="SMTP Password" required>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-info w-100"><i class="fas fa-save me-1"></i>Update Email Config</button>
                                </div>
                            </form>
                        </div>

                        <!-- Update CSS and PHP Version -->
                        <div class="mb-5 p-4 bg-light rounded-4 shadow-sm">
                            <h6><i class="fas fa-code-branch me-2 text-warning"></i>Update CSS & PHP Version</h6>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="form-control bg-light">CSS Version: <span class="fw-bold">v1.0</span></div>
                                </div>
                                <div class="col-6">
                                    <div class="form-control bg-light">PHP Version: <span class="fw-bold"><?php echo phpversion(); ?></span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Delete Category Modal -->
    <div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-labelledby="deleteCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content custom-modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteCategoryModalLabel"><i class="fas fa-trash me-2"></i>Delete Category</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the category <span id="modalCategoryName" class="fw-bold text-danger"></span>?</p>
                </div>
                <div class="modal-footer">
                    <form id="deleteCategoryForm" method="get" action="delete_category.php">
                        <input type="hidden" name="id" id="modalCategoryId" value="">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        var deleteCategoryModal = document.getElementById('deleteCategoryModal');
        if (deleteCategoryModal) {
            deleteCategoryModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var categoryId = button.getAttribute('data-category-id');
                var categoryName = button.getAttribute('data-category-name');
                document.getElementById('modalCategoryId').value = categoryId;
                document.getElementById('modalCategoryName').textContent = categoryName;
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    <?php include 'includes/footer.php'; ?>
</body>

</html>