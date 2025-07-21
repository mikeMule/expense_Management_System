<?php
// This check ensures that the script is not called directly, but rather included.
if (count(get_included_files()) === 1) {
    // If the script is called directly, you might want to show an error or redirect.
    // For simplicity, we'll just exit.
    exit('This script cannot be accessed directly.');
}

function run_migrations()
{
    try {
        $pdo = new PDO('mysql:host=' . DB_HOST . ';port=' . (defined('DB_PORT') ? DB_PORT : 3306), DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '`');
        $pdo->exec('USE `' . DB_NAME . '`');

        // Check if migrations table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'migrations'");
        if ($stmt->rowCount() == 0) {
            // If not, create it by executing the first migration file
            $migration_file = __DIR__ . '/../database/migrations/001_create_migrations_table.sql';
            if (file_exists($migration_file)) {
                $sql = file_get_contents($migration_file);
                $pdo->exec($sql);
                // Log this first migration
                $log_stmt = $pdo->prepare('INSERT INTO migrations (migration_name) VALUES (?)');
                $log_stmt->execute([basename($migration_file)]);
            }
        }

        // Check if a 'Salaries' category exists, if not, create it.
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE name = 'Salaries' AND type = 'expense'");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $pdo->exec("INSERT INTO categories (name, type) VALUES ('Salaries', 'expense')");
        }

        $stmt = $pdo->query('SELECT migration_name FROM migrations');
        $executed_migrations = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $migration_files = glob(__DIR__ . '/../database/migrations/*.sql');
        sort($migration_files);

        foreach ($migration_files as $file) {
            $migration_name = basename($file);

            if (!in_array($migration_name, $executed_migrations)) {
                try {
                    $sql = file_get_contents($file);

                    if (substr($sql, 0, 3) === "\xEF\xBB\xBF") {
                        $sql = substr($sql, 3);
                    }

                    if (!empty(trim($sql))) {
                        $pdo->exec($sql);
                        $log_stmt = $pdo->prepare('INSERT INTO migrations (migration_name) VALUES (?)');
                        $log_stmt->execute([$migration_name]);
                    }
                } catch (PDOException $e) {
                    $log_message = "[" . date('Y-m-d H:i:s') . "] Migration of file '{$migration_name}' failed: " . $e->getMessage() . "\n";
                    error_log($log_message, 3, __DIR__ . '/../logs/migration_errors.log');
                    die("A critical database error occurred while processing '{$migration_name}'. Please check the logs.");
                }
            }
        }
    } catch (PDOException $e) {
        $log_message = "[" . date('Y-m-d H:i:s') . "] Migration setup failed: " . $e->getMessage() . "\n";
        error_log($log_message, 3, __DIR__ . '/../logs/migration_errors.log');
        die("A critical database error occurred during setup. Please check the logs.");
    }
}

run_migrations();
