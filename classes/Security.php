<?php
class Security {
    /**
     * Sanitize user input for XSS prevention
     * @param string $data
     * @return string
     */
    public static function sanitize($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitize'], $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate email format
     * @param string $email
     * @return bool
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Simple Rate Limiting for Login
     * Stores failed attempts in the database
     */
    public static function checkRateLimit($ip, $db) {
        $db->query("SELECT COUNT(*) as attempts FROM login_attempts WHERE ip_address = :ip AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
        $db->bind(':ip', $ip);
        $result = $db->single();
        return ($result['attempts'] < 5); // Max 5 attempts in 15 mins
    }

    public static function logFailedAttempt($ip, $db) {
        $db->query("INSERT INTO login_attempts (ip_address, attempt_time) VALUES (:ip, NOW())");
        $db->bind(':ip', $ip);
        $db->execute();
    }

    public static function clearFailedAttempts($ip, $db) {
        $db->query("DELETE FROM login_attempts WHERE ip_address = :ip");
        $db->bind(':ip', $ip);
        $db->execute();
    }
}
