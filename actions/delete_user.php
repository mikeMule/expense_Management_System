<?php
require_once '../config/database.php';
require_once '../classes/Auth.php';

session_start();

$auth = new Auth();
$auth->requireLogin();

// Only admins can delete users
if ($_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'Access denied. Only administrators can delete users.';
    header('Location: ../users.php');
    exit();
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $db = new Database();

    // Prevent deleting the main admin
    $db->query('SELECT username FROM users WHERE id = :id');
    $db->bind(':id', $id);
    $user = $db->single();

    if ($user && $user['username'] === 'admin') {
        $_SESSION['error'] = 'Cannot delete the system administrator.';
        header('Location: ../users.php');
        exit();
    }

    // Prevent deleting yourself
    if ($id === $_SESSION['user_id']) {
        $_SESSION['error'] = 'You cannot delete your own account.';
        header('Location: ../users.php');
        exit();
    }

    $db->query('DELETE FROM users WHERE id = :id');
    $db->bind(':id', $id);

    if ($db->execute()) {
        $_SESSION['success'] = 'User deleted successfully.';
    } else {
        $_SESSION['error'] = 'Failed to delete user.';
    }
}

header('Location: ../users.php');
exit();
