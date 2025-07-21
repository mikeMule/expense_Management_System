<?php
require_once __DIR__ . '/database.php';

function run_migrations()
{
    try {
        // Create a new PDO instance for migration
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';port=' . (defined('DB_PORT') ? DB_PORT : 3306) . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );

        // Create database if it doesn't exist
        $pdo->exec('CREATE DATABASE IF NOT EXISTS ' . DB_NAME);
        $pdo->exec('USE ' . DB_NAME);

        // Manually run the first migration to create the migrations table if it's not there
        $pdo->exec("CREATE TABLE IF NOT EXISTS `migrations` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `migration_name` VARCHAR(255) NOT NULL,
            `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        // Get all executed migration names from the database
        $stmt = $pdo->query('SELECT migration_name FROM migrations');
        $executed_migrations = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Get all migration files from the directory
        $migration_files = glob(__DIR__ . '/../database/migrations/*.sql');
        sort($migration_files);

        foreach ($migration_files as $file) {
            $migration_name = basename($file);

            if (!in_array($migration_name, $executed_migrations)) {
                // Execute the migration
                $sql = file_get_contents($file);
                $pdo->exec($sql);

                // Log the migration in the database
                $stmt = $pdo->prepare('INSERT INTO migrations (migration_name) VALUES (:migration_name)');
                $stmt->execute([':migration_name' => $migration_name]);

                // Optional: Log to a file or output for debugging
                // echo "Executed migration: $migration_name\n";
            }
        }
    } catch (PDOException $e) {
        // Log error to a file and terminate
        error_log("Migration failed: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/migration_errors.log');
        // A more user-friendly error page should be shown in a real application
        die("A critical database error occurred. Please check the logs.");
    }
}

// Execute migrations
run_migrations();
