<?php
/**
 * Nexo – Database Configuration
 *
 * For InfinityFree (free hosting):
 *  1. Log in to your InfinityFree control panel.
 *  2. Go to MySQL Databases → create a database and note the credentials.
 *  3. Your DB_HOST is something like sql200.infinityfree.com (shown in cPanel).
 *  4. DB_PORT is always 3306 on InfinityFree.
 *  5. DB_USER and DB_NAME both start with your username prefix, e.g. epiz_12345678_nexo.
 *  6. Import sql/nexo_app.sql → sql/navbar_features.sql → sql/forgot_password.sql
 *     through InfinityFree's phpMyAdmin.
 *
 * For XAMPP (local development):
 *  - DB_HOST = 'localhost', DB_PORT = '3306' (or '3307' for XAMPP on non-standard port)
 *  - DB_USER = 'root', DB_PASS = ''
 */

define('DB_HOST', 'localhost');           // ← InfinityFree: sql200.infinityfree.com (check cPanel)
define('DB_NAME', 'nexo');               // ← InfinityFree: epiz_XXXXXXXX_nexo
define('DB_PORT', '3307');               // ← InfinityFree: always 3306 | XAMPP default: 3306 (use 3307 only if changed)
define('DB_USER', 'root');               // ← InfinityFree: epiz_XXXXXXXX
define('DB_PASS', '');                   // ← InfinityFree: your cPanel password

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        try {
            $this->pdo = new PDO(
                'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            die('Database connection failed. Check config/database.php');
        }
    }

    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        return $this->pdo;
    }
}