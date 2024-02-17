<?php

namespace Model;

use Error;

class MovieManager
{
    const TABLE_NAME = 'movies';

    public static ?MovieManager $instance = null;
    private readonly DatabaseManager $db;

    public function __construct()
    {
        $this->db = DatabaseManager::getDatabaseManager();
        $this->install();
    }

    public static function getMovieManager(): MovieManager
    {
        if (self::$instance === null) {
            self::$instance = new MovieManager();
        }
        return self::$instance;
    }

    public function install(): void
    {
        $createTableQuery = "CREATE TABLE IF NOT EXISTS " . self::TABLE_NAME . " (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(256) NOT NULL,
        release_year INT(4) NOT NULL,
        format ENUM('VHS', 'DVD', 'Blu-ray') NOT NULL,
        actors VARCHAR(256) NOT NULL
    )";

        if ($this->db->conn->query($createTableQuery) !== TRUE) {
            throw new Error('Error creating table:' . $this->db->conn->error);
        }
    }

    public function getMovie(int $id): ?Movie
    {
        $query = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE id = ?';
        $statement = $this->db->conn->prepare($query);
        $statement->bind_param('i', $id);
        $statement->execute();

        $result = $statement->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $movie = Movie::FromRow($row);

            return $movie;
        }

        return null;
    }
}