<?php

require_once 'Movie.php';

class DatabaseManager
{
    public static ?mysqli $conn = null;

    public static function openConnection()
    {
        if (self::$conn === null) {
            self::$conn = self::initConnection();
        }
    }

    public static function initConnection()
    {
        $servername = 'localhost';
        $username = 'root';
        $password = 'root';
        $dbname = 'cinema';
        $moviesTable = 'movies';

        $conn = new mysqli($servername, $username, $password);

        if ($conn->connect_error) {
            throw new Error('Database connection error:' . $conn->connect_error);
        }

        if ($conn->query("CREATE DATABASE IF NOT EXISTS `$dbname`") !== TRUE) {
            throw new Error('Error creating database:' . $conn->error);
        }

        $conn->select_db($dbname);

        $createTableQuery = "CREATE TABLE IF NOT EXISTS $moviesTable (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(256) NOT NULL,
        release_year INT(4) NOT NULL,
        format ENUM('VHS', 'DVD', 'Blu-ray') NOT NULL,
        actors VARCHAR(256) NOT NULL
    )";

        if ($conn->query($createTableQuery) !== TRUE) {
            throw new Error('Error creating table:' . $conn->error);
        }

        return $conn;
    }

    public static function closeConnection()
    {
        self::$conn?->close();
    }

    public static function getMovie(int $id): ?Movie {
        $query = 'SELECT * FROM movies WHERE id = ?';
        $statement = self::$conn->prepare($query);
        $statement->bind_param('i', $id);
        $statement->execute();

        $result = $statement->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            $movie = new Movie();
            $movie->id = $row['id'];
            $movie->title = $row['title'];
            $movie->release_year = $row['release_year'];
            $movie->format = $row['format'];
            $movie->actors = $row['actors'];

            return $movie;
        }

        return null;
    }
}