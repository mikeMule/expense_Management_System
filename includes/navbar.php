<?php if (isset($_SESSION['user_id'])): ?>
    <div class="sidebar">
        <div class="sidebar-header">
            <a href="dashboard.php" class="sidebar-brand">
                <i class="fas fa-calculator me-2"></i>
                <span><?php echo APP_NAME; ?></span>
            </a>
        </div>
        <ul class="sidebar-nav">
            <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <a href="dashboard.php" class="sidebar-link">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                        </a>
                    </li>
            <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'transactions.php' ? 'active' : ''; ?>">
                <a href="transactions.php" class="sidebar-link">
                    <i class="fas fa-exchange-alt"></i>
                    <span>Transactions</span>
                        </a>
                    </li>
            <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'employees.php' ? 'active' : ''; ?>">
                <a href="employees.php" class="sidebar-link">
                    <i class="fas fa-users"></i>
                    <span>Employees</span>
                        </a>
                    </li>
            <li class="sidebar-item <?php echo in_array(basename($_SERVER['PHP_SELF']), ['salaries.php', 'pending_salaries.php']) ? 'active' : ''; ?>">
                <a href="salaries.php" class="sidebar-link">
                    <i class="fas fa-money-check-alt"></i>
                    <span>Salaries</span>
                        </a>
                    </li>
            <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                <a href="reports.php" class="sidebar-link">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reports</span>
                        </a>
                    </li>
                </ul>
        <div class="sidebar-footer">
            <div class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                <a href="settings.php" class="sidebar-link">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
                            </div>
            <div class="sidebar-item">
                <a href="logout.php" class="sidebar-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>