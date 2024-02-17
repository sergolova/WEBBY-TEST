<?php

namespace Model;

use Error;

class UserManager
{
    const TABLE_NAME = 'users';

    public static ?UserManager $instance = null;
    private readonly DatabaseManager $db;

    public function __construct()
    {
        $this->db = DatabaseManager::getDatabaseManager();
        $this->install();
        $this->init();
    }

    public static function getUserManager(): UserManager
    {
        if (self::$instance === null) {
            self::$instance = new UserManager();
        }
        return self::$instance;
    }

    public function init(): void
    {
        session_start();
    }

    public function generateCsrfToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->setSessionData([
            'csrf_token' => $token,
        ]);
        return $token;
    }

    public function getCsrfToken(): string
    {
        return $this->getSessionData('csrf_token');
    }

    public function install(): void
    {
        $createTableQuery = "CREATE TABLE IF NOT EXISTS " . self::TABLE_NAME . " (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(256) NOT NULL,
        hash VARCHAR(256) NOT NULL,
        salt VARCHAR(256) NOT NULL
    )";

        if ($this->db->conn->query($createTableQuery) !== TRUE) {
            throw new Error('Error creating table:' . $this->db->conn->error);
        }
    }

    public function getCurrentUser(): ?User
    {
        $id = $this->getSessionData('user_id');
        if ($id > 0) {
            return $this->getUserById($id);
        }
        return null;
    }

    public function getUserById(int $id): ?User
    {
        $query = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE id = ?';
        $statement = $this->db->conn->prepare($query);
        $statement->bind_param('i', $id);
        $statement->execute();
        $result = $statement->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $user = User::FromRow($row);

            return $user;
        }

        return null;
    }

    public function getUserByName(string $username): ?User
    {
        $query = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE username = ?';
        $statement = $this->db->conn->prepare($query);
        $statement->bind_param('s', $username);
        $statement->execute();
        $result = $statement->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $user = User::FromRow($row);

            return $user;
        }

        return null;
    }

    public function login(string $username, string $password): bool
    {
        $user = $this->getUserByName($username);
        if ($user) {
            $hashedPassword = hash('sha256', $password . $user->salt);

            if ($hashedPassword === $user->hash) {
                $this->setSessionData([
                    'user_id' => $user->id,
                    'username' => $user->username,
                ]);
                return true;
            }
        }
        return false;

    }

    public function logout(): bool
    {
        return @session_destroy();
    }

    public function unregister(string $username): bool
    {
        $username = mysqli_real_escape_string($this->db->conn, $username);
        $sql = 'DELETE FROM ' . self::TABLE_NAME . ' WHERE username = ?';
        $res = $this->db->conn->prepare($sql);

        if ($res) {
            $res->bind_param('s', $username);
            $res->execute();
            return !$res->errno;
        } else {
            return false;
        }
    }

    public function isLoggedIn(): bool
    {
        return is_numeric($this->getSessionData('user_id'));
    }

    public function setSessionData(array $data): void
    {
        foreach ($data as $key => $value) {
            $_SESSION[$key] = $value;
        }
    }

    public function getSessionData(string $key): mixed
    {
        return @$_SESSION[$key];
    }

    public function register($username, $password): bool
    {
        if ($this->userExists($username)) {
            return false;
        }

        $salt = base64_encode(random_bytes(32));
        $hashedPassword = hash('sha256', $password . $salt);

        $username = mysqli_real_escape_string($this->db->conn, $username);
        $hashedPassword = mysqli_real_escape_string($this->db->conn, $hashedPassword);

        $query = "INSERT INTO " . self::TABLE_NAME . " (username, hash, salt) VALUES ('$username', '$hashedPassword', '$salt')";
        mysqli_query($this->db->conn, $query);

        // ..........
        return true;
    }

    private function userExists(string $username): bool
    {
        $user = $this->getUserByName($username);
        return $user !== null;
    }
}