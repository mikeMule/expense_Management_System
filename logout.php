<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';

session_start();

$auth = new Auth();
$auth->logout();

header('Location: login.php');
exit();
?>