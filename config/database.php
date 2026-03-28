<?php
// config/database.php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'skladiste_db');
define('APP_NAME', 'SkladišteApp');
define('APP_VERSION', '1.0.0');

class Database {
    private static ?Database $instance = null;
    private ?mysqli $connection = null;

    private function __construct() {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->connection->connect_error) {
            throw new Exception("DB greška: " . $this->connection->connect_error);
        }
        $this->connection->set_charset('utf8mb4');
    }

    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection(): mysqli {
        return $this->connection;
    }

    public function escape(string $value): string {
        return $this->connection->real_escape_string($value);
    }

    public function query(string $sql): mysqli_result|bool {
        return $this->connection->query($sql);
    }

    public function getLastId(): int {
        return $this->connection->insert_id;
    }

    public function getAffectedRows(): int {
        return $this->connection->affected_rows;
    }
}
