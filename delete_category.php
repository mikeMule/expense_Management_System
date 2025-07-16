<?php
require_once 'config/database.php';
session_start();

if (!isset($_GET['id'])) {
    $_SESSION['error'] = 'No category specified.';
    header('Location: settings.php');
    exit;
}
$category_id = intval($_GET['id']);
$pdo = null;
try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';port=' . DB_PORT . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->prepare('DELETE FROM categories WHERE id = :id');
    $stmt->execute([':id' => $category_id]);
    $_SESSION['success'] = 'Category deleted.';
} catch (PDOException $e) {
    $_SESSION['error'] = 'Error deleting category: ' . $e->getMessage();
}
header('Location: settings.php');
exit;
