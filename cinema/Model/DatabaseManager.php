<?php

namespace Model;

use Error;
use mysqli;

class DatabaseManager
{
    private const CONFIG_NAME = 'db.json';
    private static ?DatabaseManager $instance = null;

    public mysqli $conn;
    private array $config = [];

    public function __construct()
    {
        if (!$this->load()) {
            throw new Error('Invalid database config');
        }

        $this->conn = $this->initConnection();
    }

    public function __destruct()
    {
        $this->closeConnection();
    }

    public static function getDatabaseManager(): DatabaseManager
    {
        if (self::$instance === null) {
            self::$instance = new DatabaseManager();
        }
        return self::$instance;
    }

    protected function load(): bool
    {
        $jsonString = file_get_contents(CONFIG_DIR . DIRECTORY_SEPARATOR . self::CONFIG_NAME);
        $data = json_decode($jsonString, true);

        if (is_array($data)) {
            $this->config = $data;
            return true;
        }
        return false;
    }

    protected function initConnection(): mysqli
    {
        $conn = new mysqli($this->config['server'], $this->config['user'], $this->config['password']);

        if ($conn->connect_error) {
            throw new Error('Database connection error:' . $conn->connect_error);
        }

        if ($conn->query("CREATE DATABASE IF NOT EXISTS `{$this->config['db']}`") !== TRUE) {
            throw new Error('Error creating database:' . $conn->error);
        }

        $conn->select_db($this->config['db']);

        return $conn;
    }

    protected function closeConnection(): void
    {
        $this->conn->close();
    }

}