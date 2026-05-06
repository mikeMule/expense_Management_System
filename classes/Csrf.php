<?php
class Csrf {
    /**
     * Generate a CSRF token and store it in the session
     * @return string
     */
    public static function generateToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate a CSRF token
     * @param string $token
     * @return bool
     */
    public static function validateToken($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
            return true;
        }
        return false;
    }

    /**
     * Output a hidden CSRF input field
     */
    public static function insertTokenField() {
        $token = self::generateToken();
        echo '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }
}
