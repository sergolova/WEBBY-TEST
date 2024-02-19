<?php

namespace Model;

use Error;

class UserManager
{
    private const TABLE_NAME = 'users';
    private const TOKEN_LEN = 8;
    private const SALT_LEN = 32;

    public static ?UserManager $instance = null;
    private readonly DatabaseManager $db;

    public function __construct()
    {
        $this->db = DatabaseManager::getDatabaseManager();
        $this->install();
        $this->init();
    }

    public static function getInstance(): UserManager
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
        $token = bin2hex(random_bytes(self::TOKEN_LEN));
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
        salt VARCHAR(256) NOT NULL)";

        if ($this->db->conn->query($createTableQuery) !== TRUE) {
            throw new Error('Error creating table: ' . $this->db->conn->error);
        }
    }

    /** Checking the access rights of a specific user...
     * @param string|array $code
     * @return bool
     */
    public function userCan(string|array $code = ''): bool
    {
        $user = $this->getCurrentUser();
        return $user && ($user->can($code));
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
        $row = null;

        if ($statement) {
            $statement->bind_param('i', $id);
            $statement->execute();
            $result = $statement->get_result();
            $row = $result->fetch_assoc();
            $statement->close();
        }
        return is_array($row) ? User::FromArray($row) : null;
    }

    public function getUserByName(string $username): ?User
    {
        $query = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE username = ?';
        $statement = $this->db->conn->prepare($query);
        $row = null;

        if ($statement) {
            $statement->bind_param('s', $username);
            $statement->execute();
            $result = $statement->get_result();
            $statement->close();
            $row = $result->fetch_assoc();
        }
        return is_array($row) ? User::FromArray($row) : null;
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

    public function unregister(int $id): bool
    {
        $query = 'DELETE FROM ' . self::TABLE_NAME . ' WHERE id = ?';
        $statement = $this->db->conn->prepare($query);

        if ($statement) {
            $statement->bind_param('i', $id);
            $statement->execute();
            $result = !$statement->errno;
            $statement->close();
            return $result;
        }

        return false;
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

        $salt = base64_encode(random_bytes(self::SALT_LEN));
        $hashedPassword = hash('sha256', $password . $salt);

        $query = 'INSERT INTO ' . self::TABLE_NAME . ' (username, hash, salt) VALUES (?, ?, ?)';
        $statement = $this->db->conn->prepare($query);

        if ($statement) {
            $statement->bind_param('sss', $username, $hashedPassword, $salt);
            $statement->execute();
            $result = !$statement->errno;
            $statement->close();
            return $result;
        }
        
        return false;
    }

    private function userExists(string $username): bool
    {
        return $this->getUserByName($username) !== null;
    }
}