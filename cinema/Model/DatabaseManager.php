<?php

namespace Model;

use Error;
use mysqli;

class DatabaseManager
{
    public mysqli $conn;
    public static ?DatabaseManager $instance = null;

    public function __construct()
    {
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

    private function initConnection(): mysqli
    {
        $servername = 'localhost';
        $username = 'root';
        $password = 'root';
        $dbname = 'cinema';

        $conn = new mysqli($servername, $username, $password);

        if ($conn->connect_error) {
            throw new Error('Database connection error:' . $conn->connect_error);
        }

        if ($conn->query("CREATE DATABASE IF NOT EXISTS `$dbname`") !== TRUE) {
            throw new Error('Error creating database:' . $conn->error);
        }

        $conn->select_db($dbname);

        return $conn;
    }

    public function closeConnection(): void
    {
        $this->conn->close();
    }

}