<?php
require_once 'Database.php';
require_once 'Security.php';
require_once 'Csrf.php';

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

    public function login($username, $password, $csrf_token = null)
    {
        // 1. CSRF Validation
        if ($csrf_token && !Csrf::validateToken($csrf_token)) {
            return ['success' => false, 'error' => 'Security token mismatch. Please refresh.'];
        }

        // 2. Rate Limiting check
        $ip = $_SERVER['REMOTE_ADDR'];
        if (!Security::checkRateLimit($ip, $this->db)) {
            return ['success' => false, 'error' => 'Too many failed attempts. Please try again in 15 minutes.'];
        }

        $this->db->query('SELECT * FROM users WHERE username = :username');
        $this->db->bind(':username', $username);
        $user = $this->db->single();

        if ($user && password_verify($password, $user['password'])) {
            // Success: Clear failed attempts
            Security::clearFailedAttempts($ip, $this->db);

            // Harden session
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['location'] = $user['location'];
            $_SESSION['last_activity'] = time();
            return ['success' => true];
        }

        // Fail: Log attempt
        Security::logFailedAttempt($ip, $this->db);
        return ['success' => false, 'error' => 'Invalid credentials. Please try again.'];
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

        // Only try to restore session from cookie if no session is currently active
        if (isset($_COOKIE['EMS_SESSION']) && session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_id($_COOKIE['EMS_SESSION']);
            session_start();
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
