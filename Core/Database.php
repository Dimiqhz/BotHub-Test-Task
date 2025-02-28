<?php

namespace Core;

use PDO;
use PDOException;

class Database {

    private static $instance = null;
    private $pdo;

    private function __construct(array $config) {
        if ($config['driver'] === 'pgsql') {
            $dsn = "pgsql:host={$config['host']};dbname={$config['dbname']}";
        } else {
            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4";
        }
        try {
            $this->pdo = new PDO($dsn, $config['user'], $config['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            exit;
        }
    }

    public static function getInstance(array $config): self {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }
    
    public function getConnection(): PDO {
        return $this->pdo;
    }
}
