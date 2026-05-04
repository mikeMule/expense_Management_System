<?php
require_once 'config/database.php';
require_once 'classes/Database.php';

echo "<h2>Database Migration: User Access & Locations</h2>";

try {
    $db = new Database();
    
    // Check if columns exist
    $db->query("SHOW COLUMNS FROM users LIKE 'location'");
    $locationExists = $db->single();
    
    $db->query("SHOW COLUMNS FROM users LIKE 'role'");
    $roleExists = $db->single();

    if (!$locationExists || !$roleExists) {
        echo "<p>Adding missing columns to 'users' table...</p>";
        
        $sql = "ALTER TABLE users 
                ADD COLUMN role ENUM('admin', 'user') DEFAULT 'admin' AFTER full_name,
                ADD COLUMN location ENUM('Bahirdar', 'Addis Ababa') NOT NULL DEFAULT 'Addis Ababa' AFTER role";
        
        $db->query($sql);
        $db->execute();
        echo "<p style='color: green;'>Columns 'role' and 'location' added successfully.</p>";
    } else {
        echo "<p>Columns already exist. Skipping creation.</p>";
    }

    // Update admin password to 'admin'
    // Fresh hash for 'admin'
    $newHash = '$2y$10$0MKAnUYFaansVjTNhyv5EuJX9TdWj.JUQADb7q1BOCVJiNr.nYRtS';
    $db->query("UPDATE users SET password = :password, role = 'admin', location = 'Addis Ababa' WHERE username = 'admin'");
    $db->bind(':password', $newHash);
    $db->execute();
    
    echo "<p style='color: green;'>Default admin user updated (Password is now 'admin').</p>";
    echo "<hr><p><b>Migration complete!</b> You can now log in with:</p>";
    echo "<ul><li>Username: admin</li><li>Password: admin</li></ul>";
    echo "<p><a href='login.php'>Go to Login</a></p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
