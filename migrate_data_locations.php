<?php
require_once 'config/database.php';
require_once 'classes/Database.php';

echo "<h2>Database Migration: Location-Based Data Segmentation</h2>";

try {
    $db = new Database();
    
    $tables = ['transactions', 'employees', 'salary_payments'];
    
    foreach ($tables as $table) {
        $db->query("SHOW COLUMNS FROM $table LIKE 'location'");
        if (!$db->single()) {
            echo "<p>Adding 'location' column to '$table' table...</p>";
            $sql = "ALTER TABLE $table ADD COLUMN location ENUM('Bahirdar', 'Addis Ababa') NOT NULL DEFAULT 'Addis Ababa'";
            $db->query($sql);
            $db->execute();
            echo "<p style='color: green;'>Column 'location' added to '$table' successfully.</p>";
        } else {
            echo "<p>Column 'location' already exists in '$table'.</p>";
        }
    }
    
    echo "<hr><p style='color: blue;'><b>Note:</b> All existing records have been assigned to 'Addis Ababa' by default.</p>";
    echo "<p><b>Migration complete!</b></p>";
    echo "<p><a href='dashboard.php'>Go to Dashboard</a></p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
