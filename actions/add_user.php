<?php
require_once '../config/database.php';
require_once '../classes/Auth.php';

session_start();

$auth = new Auth();
$auth->requireLogin();

// Only admins can add users
if ($_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'Access denied. Only administrators can add users.';
    header('Location: ../users.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $location = $_POST['location'] ?? 'Addis Ababa';
    $role = $_POST['role'] ?? 'user';

    if (empty($username) || empty($password) || empty($full_name)) {
        $_SESSION['error'] = 'Please fill in all required fields.';
        header('Location: ../users.php');
        exit();
    }

    $db = new Database();

    // Check if username already exists
    $db->query('SELECT id FROM users WHERE username = :username');
    $db->bind(':username', $username);
    if ($db->single()) {
        $_SESSION['error'] = 'Username already exists.';
        header('Location: ../users.php');
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        $db->query('INSERT INTO users (username, password, email, full_name, location, role) VALUES (:username, :password, :email, :full_name, :location, :role)');
        $db->bind(':username', $username);
        $db->bind(':password', $hashed_password);
        $db->bind(':email', $email);
        $db->bind(':full_name', $full_name);
        $db->bind(':location', $location);
        $db->bind(':role', $role);

        if ($db->execute()) {
            $_SESSION['success'] = 'User added successfully.';
        } else {
            $_SESSION['error'] = 'Failed to add user.';
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }

    header('Location: ../users.php');
    exit();
}
