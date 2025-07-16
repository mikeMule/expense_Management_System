<?php
require_once 'config/database.php';
session_start();

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    !empty($_POST['category_name']) &&
    !empty($_POST['category_type']) &&
    in_array($_POST['category_type'], ['income', 'expense'])
) {
    $category_name = trim($_POST['category_name']);
    $category_type = $_POST['category_type'];
    if ($category_name !== '') {
        // Insert into database
        $pdo = null;
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';port=' . DB_PORT . ';charset=utf8mb4';
            $pdo = new PDO($dsn, DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $pdo->prepare('INSERT INTO categories (name, type) VALUES (:name, :type)');
            $stmt->execute([':name' => $category_name, ':type' => $category_type]);
            $_SESSION['success'] = 'Category added successfully!';
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Error adding category: ' . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = 'Category name cannot be empty!';
    }
} else {
    $_SESSION['error'] = 'Invalid request.';
}
header('Location: settings.php');
exit;
