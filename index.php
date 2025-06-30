<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';

session_start();

$auth = new Auth();

// Redirect to dashboard if already logged in
if ($auth->isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

// Redirect to login page
header('Location: login.php');
exit();
?>