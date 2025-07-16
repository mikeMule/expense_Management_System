<?php
require_once 'Database.php';

class Auth
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    public function login($username, $password)
    {
        // Plain login: only allow admin:admin
        if ($username === 'admin' && $password === 'admin') {
            $_SESSION['user_id'] = 1;
            $_SESSION['username'] = 'admin';
            $_SESSION['full_name'] = 'Administrator';
            $_SESSION['last_activity'] = time();
            return true;
        }
        return false;
    }

    public function logout()
    {
        session_unset();
        session_destroy();
        return true;
    }

    public function isLoggedIn()
    {
        // Save session data in a secure cookie for persistent login
        if (isset($_SESSION['user_id']) && isset($_SESSION['last_activity'])) {
            // 5 minutes timeout
            $timeout = 300;
            if (time() - $_SESSION['last_activity'] > $timeout) {
                $this->logout();
                return false;
            }
            $_SESSION['last_activity'] = time();
            // Save session to cookie
            setcookie('EMS_SESSION', session_id(), time() + $timeout, '/', '', false, true);
            return true;
        }
        // Try to restore session from cookie
        if (isset($_COOKIE['EMS_SESSION'])) {
            session_id($_COOKIE['EMS_SESSION']);
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            if (isset($_SESSION['user_id'])) {
                $_SESSION['last_activity'] = time();
                return true;
            }
        }
        return false;
    }

    public function getCurrentUser()
    {
        if ($this->isLoggedIn()) {
            $this->db->query('SELECT * FROM users WHERE id = :id');
            $this->db->bind(':id', $_SESSION['user_id']);
            return $this->db->single();
        }
        return false;
    }

    public function requireLogin()
    {
        if (!$this->isLoggedIn()) {
            header('Location: login.php');
            exit();
        }
    }

    public function changePassword($currentPassword, $newPassword)
    {
        $user = $this->getCurrentUser();
        if ($user && password_verify($currentPassword, $user['password'])) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $this->db->query('UPDATE users SET password = :password WHERE id = :id');
            $this->db->bind(':password', $hashedPassword);
            $this->db->bind(':id', $user['id']);
            return $this->db->execute();
        }
        return false;
    }
}
