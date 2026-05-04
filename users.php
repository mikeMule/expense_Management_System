<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';

$auth = new Auth();
$auth->requireLogin();

$page_title = 'User Management';
include 'includes/header.php';

// Fetch users
$db = new Database();
$db->query('SELECT * FROM users ORDER BY created_at DESC');
$users = $db->resultset();

$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';

?>

<div class="page-animate w-full">
    <!-- Header Section -->
    <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div class="relative">
            <div class="flex items-center gap-4 mb-2">
                <h1 class="text-4xl font-black text-gray-900 tracking-tight m-0 flex items-center gap-3">
                    Operators
                </h1>
                <span class="bg-brand text-white text-[10px] font-black px-2.5 py-1 rounded-md shadow-lg shadow-brand/20 uppercase tracking-tighter">
                    Access Control
                </span>
            </div>
            <p class="text-gray-500 font-medium text-sm m-0">
                Manage system administrators and operator privileges across the platform.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="openModal('addUserModal')" class="h-11 px-6 bg-brand text-white rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-black transition-all shadow-lg shadow-brand/20 flex items-center gap-2">
                <i class="fas fa-user-plus"></i> Register Operator
            </button>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert-auto-dismiss bg-rose-50 text-rose-700 p-5 rounded-2xl border-2 border-rose-100 mb-8 flex items-center shadow-sm">
            <i class="fas fa-exclamation-circle text-rose-500 text-xl mr-4"></i>
            <span class="font-black text-xs uppercase tracking-tight"><?php echo htmlspecialchars($error); ?></span>
        </div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert-auto-dismiss bg-emerald-50 text-emerald-700 p-5 rounded-2xl border-2 border-emerald-100 mb-8 flex items-center shadow-sm">
            <i class="fas fa-check-circle text-emerald-500 text-xl mr-4"></i>
            <span class="font-black text-xs uppercase tracking-tight"><?php echo htmlspecialchars($success); ?></span>
        </div>
    <?php endif; ?>

    <!-- Filter Console -->
    <div class="bg-white rounded-3xl border-3 border-gray-100 shadow-sm p-8 mb-10 group hover:border-brand transition-all">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-8 h-8 rounded-lg bg-brand/10 text-brand flex items-center justify-center">
                <i class="fas fa-search text-xs"></i>
            </div>
            <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest">Operator Registry Filter</h3>
        </div>

        <div class="relative">
            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
            <input type="text" id="userSearchInput" placeholder="Search by name, username or electronic mail..." 
                   class="w-full h-12 pl-10 pr-4 bg-gray-50 border-2 border-gray-100 rounded-2xl text-sm font-bold text-gray-800 focus:bg-white focus:border-brand outline-none transition-all">
        </div>
    </div>

    <!-- User Registry Table -->
    <div class="bg-white rounded-3xl border-3 border-gray-100 shadow-sm overflow-hidden mb-10">
        <div class="p-8 border-b border-gray-100">
            <h2 class="text-sm font-black text-gray-900 uppercase tracking-widest">Active System Operators</h2>
        </div>
        
        <?php if (empty($users)): ?>
            <div class="text-center py-20 text-gray-400 font-bold text-sm">No operators detected in the registry.</div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table id="userTable" class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-gray-100">
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Operator Identity</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Location</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Privilege Level</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Established</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Control</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php foreach ($users as $user): ?>
                            <tr class="hover:bg-gray-50/50 transition-all border-l-4 border-l-transparent hover:border-l-brand">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-brand/5 text-brand flex items-center justify-center flex-shrink-0 font-black text-xs border border-brand/10">
                                            <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <div class="text-sm font-black text-gray-900"><?php echo htmlspecialchars($user['full_name']); ?></div>
                                            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-tight">@<?php echo htmlspecialchars($user['username']); ?> • <?php echo htmlspecialchars($user['email'] ?: 'No Email'); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-tighter bg-blue-50 text-blue-700 border border-blue-100">
                                        <i class="fas fa-map-marker-alt mr-1"></i> <?php echo htmlspecialchars($user['location']); ?>
                                    </span>
                                </td>
                                <td class="px-8 py-6">
                                    <?php if ($user['role'] == 'admin'): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-tighter bg-purple-50 text-purple-700 border border-purple-100 shadow-sm">
                                            Administrator
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-tighter bg-gray-50 text-gray-600 border border-gray-100">
                                            Standard User
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-8 py-6 text-gray-400 text-[10px] font-bold"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td class="px-8 py-6 text-right">
                                    <?php if ($user['username'] !== 'admin'): ?>
                                        <button type="button" class="h-8 px-3 bg-rose-50 text-rose-600 border border-rose-100 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-rose-600 hover:text-white transition-all flex items-center gap-1 btn-delete-user ml-auto"
                                                data-id="<?php echo $user['id']; ?>"
                                                data-username="<?php echo htmlspecialchars($user['username']); ?>">
                                            <i class="fas fa-trash-alt"></i> Revoke
                                        </button>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-gray-900 text-white text-[8px] font-black uppercase tracking-widest">
                                            <i class="fas fa-shield-alt mr-1.5"></i> Immutable
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add User Modal -->
<div id="addUserModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity opacity-0" onclick="closeModal('addUserModal')"></div>
    
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto relative z-10 mx-4 scale-95 opacity-0 transition-all duration-300">
        <div class="flex items-center justify-between p-6 border-b border-gray-100 bg-gray-50/50 sticky top-0 z-20">
            <h3 class="text-xl font-bold text-gray-800 flex items-center m-0">
                <div class="w-10 h-10 rounded-full bg-brand/10 text-brand flex items-center justify-center mr-3">
                    <i class="fas fa-user-plus"></i>
                </div>
                Add New User
            </h3>
            <button type="button" class="text-gray-400 hover:text-gray-600 w-8 h-8 rounded-full hover:bg-gray-100 flex items-center justify-center transition-colors" onclick="closeModal('addUserModal')">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        
        <form action="actions/add_user.php" method="POST" class="p-6 md:p-8 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-at text-gray-400"></i>
                        </div>
                        <input type="text" name="username" class="input-premium w-full pl-10" required placeholder="john_doe">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input type="password" name="password" class="input-premium w-full pl-10" required placeholder="••••••••">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-400"></i>
                        </div>
                        <input type="text" name="full_name" class="input-premium w-full pl-10" required placeholder="John Doe">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>
                        <input type="email" name="email" class="input-premium w-full pl-10" placeholder="john@example.com">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-map-marker-alt text-gray-400"></i>
                        </div>
                        <select name="location" class="input-premium w-full pl-10" required>
                            <option value="Addis Ababa">Addis Ababa</option>
                            <option value="Bahirdar">Bahirdar</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-user-shield text-gray-400"></i>
                        </div>
                        <select name="role" class="input-premium w-full pl-10" required>
                            <option value="admin">Administrator</option>
                            <option value="user">User</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-6 mt-6 border-t border-gray-100">
                <button type="button" class="px-5 py-2.5 rounded-xl font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors" onclick="closeModal('addUserModal')">Cancel</button>
                <button type="submit" class="btn-primary flex items-center gap-2"><i class="fas fa-save"></i> Save User</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteUserModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity opacity-0" onclick="closeModal('deleteUserModal')"></div>
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm relative z-10 mx-4 scale-95 opacity-0 transition-all duration-300">
        <div class="p-6 text-center">
            <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-exclamation-triangle text-3xl text-red-500"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Confirm Deletion</h3>
            <p class="text-gray-500 mb-2">Are you sure you want to delete user</p>
            <p class="font-bold text-gray-800 bg-gray-50 p-2 rounded-lg" id="deleteUsername"></p>
        </div>
        <div class="flex border-t border-gray-100">
            <button type="button" class="flex-1 py-3 text-gray-600 font-semibold hover:bg-gray-50 rounded-bl-2xl transition-colors" onclick="closeModal('deleteUserModal')">Cancel</button>
            <a href="#" id="confirmDelete" class="flex-1 py-3 text-white bg-red-500 font-semibold hover:bg-red-600 rounded-br-2xl transition-colors text-center">Delete</a>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.btn-delete-user').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.getAttribute('data-id');
                const username = this.getAttribute('data-username');
                
                document.getElementById('deleteUsername').textContent = '@' + username;
                document.getElementById('confirmDelete').href = 'actions/delete_user.php?id=' + id;
                
                openModal('deleteUserModal');
            });
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
