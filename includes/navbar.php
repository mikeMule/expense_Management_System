<?php if (isset($_SESSION['user_id'])): ?>
    <!-- Sidebar -->
    <aside class="sidebar fixed top-0 left-[-16rem] lg:left-0 w-64 h-screen bg-brand-dark text-white flex flex-col z-[100] transition-all duration-300 shadow-xl overflow-y-auto scrollbar-thin">
        
        <!-- Brand/Logo -->
        <div class="text-center py-8 border-b border-white/10">
            <a href="dashboard.php" class="inline-block hover:scale-105 transition-transform">
                <div class="w-16 h-16 mx-auto bg-white/10 rounded-2xl flex items-center justify-center mb-4 shadow-inner">
                    <i class="fas fa-wallet text-3xl text-primary-100"></i>
                </div>
                <div class="font-bold text-xl tracking-wide"><?php echo APP_NAME; ?></div>
            </a>
            <div class="mt-4 px-4">
                <div class="text-xs text-white/50 uppercase tracking-widest mb-2">Location</div>
                <div class="inline-flex items-center bg-white/10 px-4 py-1.5 rounded-full text-sm font-medium border border-white/5 shadow-sm">
                    <i class="fas fa-map-marker-alt text-red-400 mr-2"></i>
                    <?php echo $_SESSION['location'] ?? 'Addis Ababa'; ?>
                </div>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-grow px-4 py-6">
            <ul class="space-y-2">
                <li>
                    <a href="dashboard.php" class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'bg-primary-600 shadow-md text-white font-semibold' : 'text-white/70 hover:bg-white/10 hover:text-white'; ?>">
                        <i class="fas fa-chart-pie w-6 text-center mr-3"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="transactions.php" class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 <?php echo basename($_SERVER['PHP_SELF']) == 'transactions.php' ? 'bg-primary-600 shadow-md text-white font-semibold' : 'text-white/70 hover:bg-white/10 hover:text-white'; ?>">
                        <i class="fas fa-exchange-alt w-6 text-center mr-3"></i>
                        <span>Transactions</span>
                    </a>
                </li>
                <li>
                    <a href="employees.php" class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 <?php echo basename($_SERVER['PHP_SELF']) == 'employees.php' ? 'bg-primary-600 shadow-md text-white font-semibold' : 'text-white/70 hover:bg-white/10 hover:text-white'; ?>">
                        <i class="fas fa-users w-6 text-center mr-3"></i>
                        <span>Employees</span>
                    </a>
                </li>
                <li>
                    <a href="salaries.php" class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 <?php echo in_array(basename($_SERVER['PHP_SELF']), ['salaries.php', 'pending_salaries.php']) ? 'bg-primary-600 shadow-md text-white font-semibold' : 'text-white/70 hover:bg-white/10 hover:text-white'; ?>">
                        <i class="fas fa-money-check-alt w-6 text-center mr-3"></i>
                        <span>Salaries</span>
                    </a>
                </li>
                <li>
                    <a href="reports.php" class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'bg-primary-600 shadow-md text-white font-semibold' : 'text-white/70 hover:bg-white/10 hover:text-white'; ?>">
                        <i class="fas fa-chart-line w-6 text-center mr-3"></i>
                        <span>Reports</span>
                    </a>
                </li>
                <li>
                    <a href="salary_report.php" class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 <?php echo basename($_SERVER['PHP_SELF']) == 'salary_report.php' ? 'bg-primary-600 shadow-md text-white font-semibold' : 'text-white/70 hover:bg-white/10 hover:text-white'; ?>">
                        <i class="fas fa-file-invoice-dollar w-6 text-center mr-3"></i>
                        <span>Salary Report</span>
                    </a>
                </li>
                <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                <li>
                    <a href="users.php" class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'bg-primary-600 shadow-md text-white font-semibold' : 'text-white/70 hover:bg-white/10 hover:text-white'; ?>">
                        <i class="fas fa-users-cog w-6 text-center mr-3"></i>
                        <span>Users</span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>

        <!-- Footer Links -->
        <div class="p-4 border-t border-white/10 mt-auto">
            <a href="settings.php" class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'bg-primary-600 shadow-md text-white font-semibold' : 'text-white/70 hover:bg-white/10 hover:text-white'; ?>">
                <i class="fas fa-cog w-6 text-center mr-3"></i>
                <span>Settings</span>
            </a>
            <a href="logout.php" class="flex items-center px-4 py-3 mt-2 rounded-xl text-red-400 hover:bg-red-500/10 hover:text-red-300 transition-all duration-200 font-medium">
                <i class="fas fa-sign-out-alt w-6 text-center mr-3"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>
<?php endif; ?>