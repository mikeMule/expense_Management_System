<?php
require_once 'Database.php';

class Auth
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function login($username, $password)
    {
        $this->db->query('SELECT * FROM users WHERE username = :username');
        $this->db->bind(':username', $username);
        $user = $this->db->single();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['location'] = $user['location'];
            $_SESSION['last_activity'] = time();
            return true;
        }
        return false;
    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
        return true;
    }

    public function isLoggedIn()
    {
        if (isset($_SESSION['user_id']) && isset($_SESSION['last_activity'])) {
            // 5 minutes timeout
            $timeout = 300;
            if (time() - $_SESSION['last_activity'] > $timeout) {
                $this->logout();
                return false;
            }
            $_SESSION['last_activity'] = time();
            
            // Only set cookie if headers haven't been sent yet
            if (!headers_sent()) {
                setcookie('EMS_SESSION', session_id(), time() + $timeout, '/', '', false, true);
            }
            return true;
        }
        
        // Try to restore session from cookie
        if (isset($_COOKIE['EMS_SESSION']) && !headers_sent()) {
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
