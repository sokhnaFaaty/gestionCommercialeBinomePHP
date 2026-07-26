<?php
namespace App\Core;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        $config = require __DIR__ . '/../../src/config/database.php';
        try {
            // PostgreSQL : le charset ne se met pas dans le DSN (contrairement à MySQL),
            // on le définit après la connexion avec SET NAMES.
            $dsn = "pgsql:host=" . $config['host']
                 . ";port=" . $config['port']
                 . ";dbname=" . $config['db_name'];

            $this->conn = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            $this->conn->exec("SET NAMES '" . $config['charset'] . "'");
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }
}
