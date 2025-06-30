<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'expense_management');

// Application configuration
define('APP_NAME', 'Expense Management System');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost/expense-management');

// Security settings
define('SESSION_TIMEOUT', 3600); // 1 hour
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 for HTTPS

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('UTC');
?>