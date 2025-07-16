<?php
if (isset($_SESSION['user_id'])): ?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-calculator me-2"></i>
                <?php echo APP_NAME; ?>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'transactions.php' ? 'active' : ''; ?>" href="transactions.php">
                            <i class="fas fa-exchange-alt me-1"></i>Transactions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'employees.php' ? 'active' : ''; ?>" href="employees.php">
                            <i class="fas fa-users me-1"></i>Employees
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo in_array(basename($_SERVER['PHP_SELF']), ['salaries.php', 'pending_salaries.php']) ? 'active' : ''; ?>" href="#" id="salariesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-money-check-alt me-1"></i>Salaries
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="salariesDropdown">
                            <li><a class="dropdown-item <?php echo basename($_SERVER['PHP_SELF']) == 'salaries.php' ? 'active' : ''; ?>" href="salaries.php"><i class="fas fa-list me-1"></i>All Salaries</a></li>
                            <li><a class="dropdown-item <?php echo basename($_SERVER['PHP_SELF']) == 'pending_salaries.php' ? 'active' : ''; ?>" href="pending_salaries.php"><i class="fas fa-clock me-1"></i>Pending Salaries</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>" href="reports.php">
                            <i class="fas fa-chart-bar me-1"></i>Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>" href="settings.php">
                            <i class="fas fa-cog me-1"></i>Settings
                        </a>
                    </li>
                </ul>

                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" href="#" id="userProfileModalBtn">
                            <span class="user-avatar-anim d-inline-flex align-items-center justify-content-center bg-white text-primary shadow" style="width: 38px; height: 38px; border-radius: 50%; font-size: 1.3rem; animation: user-bounce 1.2s infinite alternate;">
                                <i class="fas fa-user-circle"></i>
                            </span>
                            <span class="fw-semibold d-none d-md-inline"> <?php echo $_SESSION['full_name'] ?? $_SESSION['username']; ?> </span>
                        </a>
                    </li>
                </ul>
                <!-- Profile Modal -->
                <!-- Custom Profile Modal -->
                <div id="userProfileModalCustom" class="BG-Modal_style" style="display:none;">
                    <div class="custom-modal-dialog">
                        <div class="custom-modal-content">
                            <div class="custom-modal-header">
                                <h5 class="custom-modal-title"><i class="fas fa-user-circle me-2 text-primary"></i>Profile</h5>
                                <button type="button" class="btn-close" id="closeProfileModalCustom" aria-label="Close"></button>
                            </div>
                            <div class="custom-modal-body text-center">
                                <div class="mb-3">
                                    <span class="user-avatar-anim d-inline-flex align-items-center justify-content-center bg-white text-primary shadow mb-2" style="width: 60px; height: 60px; border-radius: 50%; font-size: 2.2rem; animation: user-bounce 1.2s infinite alternate;">
                                        <i class="fas fa-user-circle"></i>
                                    </span>
                                    <div class="fw-bold fs-5 mt-2"><?php echo $_SESSION['full_name'] ?? $_SESSION['username']; ?></div>
                                </div>
                                <a href="settings.php" class="btn btn-outline-primary w-100 mb-2"><i class="fas fa-cog me-1"></i>Settings</a>
                                <form action="logout.php" method="post" class="m-0 p-0">
                                    <button type="submit" class="btn btn-outline-danger w-100"><i class="fas fa-sign-out-alt me-1"></i>Logout</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <style>
                    /* Custom Modal Styles */
                    .BG-Modal_style {
                        background: rgba(25, 118, 210, 0.12) !important;
                        opacity: 1 !important;
                        position: fixed !important;
                        z-index: 1040 !important;
                        /* Lower than Bootstrap modal (z-index: 1050) */
                        top: 0;
                        left: 0;
                        width: 100vw;
                        height: 100vh;
                        pointer-events: auto !important;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        transition: background 0.3s;
                    }

                    .custom-modal-dialog {
                        max-width: 400px;
                        width: 100%;
                        margin: 0 auto;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }

                    .custom-modal-content {
                        background: #fff;
                        border-radius: 1.5rem;
                        box-shadow: 0 8px 32px rgba(25, 118, 210, 0.18);
                        padding: 2rem 1.5rem 1.5rem 1.5rem;
                        width: 100%;
                        position: relative;
                        z-index: 1220;
                    }

                    .custom-modal-header {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        border-bottom: 1px solid #e3e6f0;
                        padding-bottom: 0.5rem;
                        margin-bottom: 1rem;
                        background: #fff;
                        border-radius: 1.5rem 1.5rem 0 0;
                    }

                    .custom-modal-title {
                        font-size: 1.25rem;
                        font-weight: 600;
                        color: #1976d2;
                        margin: 0;
                    }

                    .custom-modal-body {
                        background: #fff;
                        border-radius: 0 0 1.5rem 1.5rem;
                        padding: 0;
                    }

                    /*
                    .modal-backdrop {
                        --bs-backdrop-zindex: 1050;
                        --bs-backdrop-bg: #000;
                        --bs-backdrop-opacity: 0.5;
                        position: fixed;
                        top: 0;
                        left: 0;
                        z-index: var(--bs-backdrop-zindex);
                        width: 100vw;
                        height: 100vh;
                        background-color: var(--bs-backdrop-bg);
                    }
                    */

                    /* Custom modal backdrop for profile modal */
                    .BG-Modal_style {
                        background: rgba(25, 118, 210, 0.12) !important;
                        opacity: 1 !important;
                        position: fixed !important;
                        z-index: 1100 !important;
                        top: 0;
                        left: 0;
                        width: 100vw;
                        height: 100vh;
                        pointer-events: auto !important;
                        transition: background 0.3s;
                    }

                    #userProfileModal {
                        z-index: 1200 !important;
                    }

                    #userProfileModal .modal-dialog {
                        z-index: 1210 !important;
                    }

                    #userProfileModal .modal-content {
                        background: #fff !important;
                        z-index: 1220 !important;
                    }
                </style>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var profileBtn = document.getElementById('userProfileModalBtn');
                        var customModal = document.getElementById('userProfileModalCustom');
                        var closeBtn = document.getElementById('closeProfileModalCustom');
                        if (profileBtn && customModal) {
                            profileBtn.addEventListener('click', function(e) {
                                e.preventDefault();
                                customModal.style.display = 'flex';
                                document.body.classList.add('modal-open');
                            });
                        }
                        if (closeBtn && customModal) {
                            closeBtn.addEventListener('click', function() {
                                customModal.style.display = 'none';
                                document.body.classList.remove('modal-open');
                            });
                        }
                        // Close modal on outside click
                        if (customModal) {
                            customModal.addEventListener('click', function(e) {
                                if (e.target === customModal) {
                                    customModal.style.display = 'none';
                                    document.body.classList.remove('modal-open');
                                }
                            });
                        }
                    });
                </script>
                <style>
                    @keyframes user-bounce {
                        0% {
                            transform: translateY(0);
                        }

                        100% {
                            transform: translateY(-7px);
                        }
                    }

                    .user-avatar-anim {
                        transition: box-shadow 0.2s;
                        box-shadow: 0 2px 8px rgba(25, 118, 210, 0.12) !important;
                    }

                    .user-avatar-anim:hover {
                        box-shadow: 0 4px 16px rgba(25, 118, 210, 0.22) !important;
                    }

                    .dropdown-menu.animate__animated {
                        --animate-duration: 0.25s;
                    }
                </style>
            </div>
        </div>
    </nav>
<?php endif; ?>