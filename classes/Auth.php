<?php
require_once 'Database.php';

class Auth {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function login($username, $password) {
        $this->db->query('SELECT * FROM users WHERE username = :username');
        $this->db->bind(':username', $username);
        
        $user = $this->db->single();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['last_activity'] = time();
            return true;
        }
        
        return false;
    }

    public function logout() {
        session_unset();
        session_destroy();
        return true;
    }

    public function isLoggedIn() {
        if (isset($_SESSION['user_id']) && isset($_SESSION['last_activity'])) {
            if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
                $this->logout();
                return false;
            }
            $_SESSION['last_activity'] = time();
            return true;
        }
        return false;
    }

    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            $this->db->query('SELECT * FROM users WHERE id = :id');
            $this->db->bind(':id', $_SESSION['user_id']);
            return $this->db->single();
        }
        return false;
    }

    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: login.php');
            exit();
        }
    }

    public function changePassword($currentPassword, $newPassword) {
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
?>