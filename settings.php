<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';

$auth = new Auth();
$auth->requireLogin();
$page_title = 'Settings';
include 'includes/header.php';
?>

<div class="page-animate w-full">
    <!-- Header Section -->
    <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div class="relative">
            <div class="flex items-center gap-4 mb-2">
                <h1 class="text-4xl font-black text-gray-900 tracking-tight m-0 flex items-center gap-3">
                    System
                </h1>
                <span class="bg-gray-900 text-white text-[10px] font-black px-2.5 py-1 rounded-md shadow-lg shadow-black/20 uppercase tracking-tighter">
                    Configuration
                </span>
            </div>
            <p class="text-gray-500 font-medium text-sm m-0">
                Manage platform primitives, categories, and administrative protocols.
            </p>
        </div>
    </div>

    <div class="grid gap-10">
        <!-- Category Management -->
        <div class="bg-white rounded-3xl border-3 border-gray-100 shadow-sm overflow-hidden group hover:border-brand transition-all">
            <div class="p-8 border-b border-gray-100 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-brand/10 text-brand flex items-center justify-center">
                        <i class="fas fa-tags text-xs"></i>
                    </div>
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest m-0">Directory Categories</h3>
                </div>
            </div>
            
            <div class="p-8">
                <form method="POST" action="add_category.php" class="bg-gray-50 p-6 rounded-2xl border-2 border-gray-100 mb-8 flex flex-col lg:flex-row gap-4 items-end">
                    <div class="flex-grow w-full lg:w-auto">
                        <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1.5 block ml-1">Category Label</label>
                        <input type="text" name="category_name" placeholder="Enter name..." required
                               class="w-full h-11 px-4 bg-white border-2 border-gray-200 rounded-xl text-sm font-bold text-gray-800 focus:border-brand outline-none transition-all">
                    </div>
                    <div class="w-full lg:w-48">
                        <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1.5 block ml-1">Type Class</label>
                        <select name="category_type" required
                                class="w-full h-11 px-4 bg-white border-2 border-gray-200 rounded-xl text-sm font-bold text-gray-800 focus:border-brand outline-none transition-all">
                            <option value="">Select Type</option>
                            <option value="income">Income</option>
                            <option value="expense">Expense</option>
                        </select>
                    </div>
                    <button type="submit" class="h-11 px-6 bg-brand text-white rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-black transition-all shadow-lg shadow-brand/20 w-full lg:w-auto">
                        <i class="fas fa-plus"></i> Append Category
                    </button>
                </form>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 border-b border-gray-100">
                                <th class="px-6 py-4 text-[9px] font-black text-gray-400 uppercase tracking-widest">Label</th>
                                <th class="px-6 py-4 text-[9px] font-black text-gray-400 uppercase tracking-widest">Classification</th>
                                <th class="px-6 py-4 text-[9px] font-black text-gray-400 uppercase tracking-widest">Established</th>
                                <th class="px-6 py-4 text-[9px] font-black text-gray-400 uppercase tracking-widest text-right">Control</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php
                            try {
                                $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';port=' . DB_PORT . ';charset=utf8mb4';
                                $pdo = new PDO($dsn, DB_USER, DB_PASS);
                                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                $stmt = $pdo->query('SELECT * FROM categories ORDER BY created_at DESC');
                                $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                if (empty($categories)) {
                                    echo '<tr><td colspan="4" class="px-6 py-10 text-center text-gray-400 font-bold text-xs">No categories established.</td></tr>';
                                } else {
                                    foreach ($categories as $cat) {
                                        $typeBadgeClass = $cat['type'] === 'income' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-rose-50 text-rose-700 border-rose-100';
                                        echo '<tr class="hover:bg-gray-50/30 transition-all border-l-4 border-l-transparent hover:border-l-brand">';
                                        echo '<td class="px-6 py-4 font-black text-gray-800 text-sm">' . htmlspecialchars($cat['name']) . '</td>';
                                        echo '<td class="px-6 py-4"><span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-tighter border ' . $typeBadgeClass . '">' . htmlspecialchars($cat['type']) . '</span></td>';
                                        echo '<td class="px-6 py-4 text-gray-400 text-[10px] font-bold">' . date('M d, Y', strtotime($cat['created_at'])) . '</td>';
                                        echo '<td class="px-6 py-4 text-right">';
                                        echo '<button type="button" class="btn-delete-category w-8 h-8 rounded-xl bg-gray-50 text-gray-400 hover:bg-rose-600 hover:text-white inline-flex items-center justify-center transition-all border border-gray-100" data-category-id="' . $cat['id'] . '" data-category-name="' . htmlspecialchars($cat['name']) . '"><i class="fas fa-trash-alt text-[10px]"></i></button>';
                                        echo '</td>';
                                        echo '</tr>';
                                    }
                                }
                            } catch (Exception $e) {
                                echo '<tr><td colspan="4" class="px-6 py-4 text-center text-rose-500 font-bold">Error retrieving registry.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Backup Database -->
        <div class="bg-white rounded-3xl border-3 border-gray-100 shadow-sm overflow-hidden group hover:border-brand transition-all">
            <div class="p-8 border-b border-gray-100 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <i class="fas fa-database text-xs"></i>
                    </div>
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest m-0">Repository Backups</h3>
                </div>
                <form method="POST" action="backup_db.php">
                    <button type="submit" class="h-9 px-4 bg-emerald-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-black transition-all flex items-center gap-2 shadow-lg shadow-emerald-600/10">
                        <i class="fas fa-download"></i> New Snapshot
                    </button>
                </form>
            </div>
            
            <div class="p-8">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 border-b border-gray-100">
                                <th class="px-6 py-4 text-[9px] font-black text-gray-400 uppercase tracking-widest">Snapshot File</th>
                                <th class="px-6 py-4 text-[9px] font-black text-gray-400 uppercase tracking-widest">Timestamp</th>
                                <th class="px-6 py-4 text-[9px] font-black text-gray-400 uppercase tracking-widest text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php
                            $backupDir = __DIR__ . '/BackupDB/';
                            if (is_dir($backupDir)) {
                                $files = array_diff(scandir($backupDir, SCANDIR_SORT_DESCENDING), array('.', '..'));
                                if (empty($files)) {
                                    echo '<tr><td colspan="3" class="px-6 py-12 text-center text-gray-400 font-bold text-xs">No repository snapshots found.</td></tr>';
                                } else {
                                    foreach ($files as $file) {
                                        $filePath = 'BackupDB/' . $file;
                                        $date = date('M d, Y H:i', filemtime($backupDir . $file));
                                        echo '<tr class="hover:bg-gray-50/30 transition-all border-l-4 border-l-transparent hover:border-l-indigo-600">';
                                        echo '<td class="px-6 py-4 font-black text-gray-800 text-sm">' . htmlspecialchars($file) . '</td>';
                                        echo '<td class="px-6 py-4 text-gray-400 text-[10px] font-bold">' . $date . '</td>';
                                        echo '<td class="px-6 py-4 text-right flex justify-end gap-2">';
                                        echo '<a href="' . $filePath . '" class="h-8 px-3 bg-blue-50 text-blue-600 border border-blue-100 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-blue-600 hover:text-white transition-all flex items-center gap-1" download><i class="fas fa-download"></i> Get</a>';
                                        echo '<a href="delete_backup.php?file=' . urlencode($file) . '" class="h-8 px-3 bg-rose-50 text-rose-600 border border-rose-100 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-rose-600 hover:text-white transition-all flex items-center gap-1" onclick="return confirm(\'Purge this snapshot?\')"><i class="fas fa-trash"></i> Purge</a>';
                                        echo '</td>';
                                        echo '</tr>';
                                    }
                                }
                            } else {
                                echo '<tr><td colspan="3" class="px-6 py-12 text-center text-gray-400 font-bold text-xs">Repository storage not detected.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Email Configuration -->
        <div class="bg-white rounded-3xl border-3 border-gray-100 shadow-sm overflow-hidden group hover:border-brand transition-all">
            <div class="p-8 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                        <i class="fas fa-envelope text-xs"></i>
                    </div>
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest m-0">Communication Protocols</h3>
                </div>
            </div>
            
            <form method="POST" action="update_email_config.php" class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block ml-1">Outbound Email</label>
                        <input type="email" name="email_from" placeholder="noreply@domain.com" required
                               class="w-full h-12 px-4 bg-gray-50 border-2 border-gray-100 rounded-2xl text-sm font-bold text-gray-800 focus:bg-white focus:border-brand outline-none transition-all">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block ml-1">SMTP Gateway</label>
                        <input type="text" name="smtp_host" placeholder="smtp.provider.com" required
                               class="w-full h-12 px-4 bg-gray-50 border-2 border-gray-100 rounded-2xl text-sm font-bold text-gray-800 focus:bg-white focus:border-brand outline-none transition-all">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block ml-1">Access Identity</label>
                        <input type="text" name="smtp_user" placeholder="identity@domain.com" required
                               class="w-full h-12 px-4 bg-gray-50 border-2 border-gray-100 rounded-2xl text-sm font-bold text-gray-800 focus:bg-white focus:border-brand outline-none transition-all">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block ml-1">Security Credential</label>
                        <input type="password" name="smtp_pass" placeholder="••••••••" required
                               class="w-full h-12 px-4 bg-gray-50 border-2 border-gray-100 rounded-2xl text-sm font-bold text-gray-800 focus:bg-white focus:border-brand outline-none transition-all">
                    </div>
                </div>
                <div class="mt-8 flex justify-end">
                    <button type="submit" class="h-11 px-8 bg-brand text-white rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-black transition-all shadow-lg shadow-brand/20">
                        <i class="fas fa-save"></i> Commit Protocols
                    </button>
                </div>
            </form>
        </div>

        <!-- System Info -->
        <div class="bg-white rounded-3xl border-3 border-gray-100 shadow-sm overflow-hidden group hover:border-brand transition-all">
            <div class="p-8 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-gray-100 text-gray-600 flex items-center justify-center">
                        <i class="fas fa-server text-xs"></i>
                    </div>
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest m-0">Core Specifications</h3>
                </div>
            </div>
            
            <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gray-50/50 rounded-2xl p-5 border-2 border-gray-100 flex items-center justify-between">
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Interface Layer</span>
                    <span class="font-black text-gray-900 text-xs px-3 py-1 bg-white rounded-lg border border-gray-200">Tailwind Engine v3.4</span>
                </div>
                <div class="bg-gray-50/50 rounded-2xl p-5 border-2 border-gray-100 flex items-center justify-between">
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Runtime Version</span>
                    <span class="font-black text-gray-900 text-xs px-3 py-1 bg-white rounded-lg border border-gray-200">PHP <?php echo phpversion(); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Category Modal -->
<div id="deleteCategoryModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity opacity-0" onclick="closeModal('deleteCategoryModal')"></div>
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm relative z-10 mx-4 scale-95 opacity-0 transition-all duration-300">
        <div class="p-6 text-center">
            <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-exclamation-triangle text-3xl text-red-500"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Confirm Deletion</h3>
            <p class="text-gray-500 mb-2">Are you sure you want to delete category</p>
            <p class="font-bold text-gray-800 bg-gray-50 p-2 rounded-lg" id="modalCategoryName"></p>
        </div>
        <div class="flex border-t border-gray-100">
            <button type="button" class="flex-1 py-3 text-gray-600 font-semibold hover:bg-gray-50 rounded-bl-2xl transition-colors" onclick="closeModal('deleteCategoryModal')">Cancel</button>
            <form id="deleteCategoryForm" method="get" action="delete_category.php" class="flex-1">
                <input type="hidden" name="id" id="modalCategoryId" value="">
                <button type="submit" class="w-full h-full py-3 text-white bg-red-500 font-semibold hover:bg-red-600 rounded-br-2xl transition-colors text-center">Delete</button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.btn-delete-category').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.getAttribute('data-category-id');
                const name = this.getAttribute('data-category-name');
                
                document.getElementById('modalCategoryId').value = id;
                document.getElementById('modalCategoryName').textContent = name;
                
                openModal('deleteCategoryModal');
            });
        });
    });
</script>

<?php include 'includes/footer.php'; ?>