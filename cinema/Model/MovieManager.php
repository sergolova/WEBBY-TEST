<?php

namespace Model;

use Error;

class MovieManager
{
    private const TABLE_NAME = 'movies';
    public const FORMAT_ENUMS = ['VHS', 'DVD', 'Blu-ray'];

    public static ?MovieManager $instance = null;
    private readonly DatabaseManager $db;

    public function __construct()
    {
        $this->db = DatabaseManager::getDatabaseManager();
        $this->install();
    }

    public static function getInstance(): MovieManager
    {
        if (self::$instance === null) {
            self::$instance = new MovieManager();
        }
        return self::$instance;
    }

    public function install(): void
    {
        $enums = array_map(fn($el) => "'$el'", self::FORMAT_ENUMS);
        $enums = implode(', ', $enums);

        $createTableQuery = "CREATE TABLE IF NOT EXISTS " . self::TABLE_NAME . " (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(256) NOT NULL ,
        release_year INT(4) NOT NULL,
        format ENUM($enums) NOT NULL,
        actors VARCHAR(256),
        description TEXT)";

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
            return Movie::FromArray($row);
        }

        return null;
    }

    public function getMovies(): array
    {

        $query = 'SELECT * FROM ' . self::TABLE_NAME . ' ORDER BY title ASC';
        $statement = $this->db->conn->prepare($query);
        if ($statement) {
            $statement->execute();

            $queryResult = $statement->get_result();
            $result = $queryResult->fetch_all(MYSQLI_ASSOC);
            return array_map(fn($el) => Movie::FromArray($el), $result);
        }
        return [];
    }

    public function queryMovies(string $queryKey, string $queryValue): array
    {
        if ($queryKey !== 'title' && $queryKey !== 'actors') {
            return [];
        }
        $queryKey = mysqli_real_escape_string($this->db->conn, $queryKey);
        $queryValue = '%' . mysqli_real_escape_string($this->db->conn, $queryValue) . '%';

        $query = 'SELECT * FROM ' . self::TABLE_NAME . " WHERE $queryKey LIKE ? ORDER BY title ASC";
        $statement = $this->db->conn->prepare($query);
        if ($statement) {
            $statement->bind_param('s', $queryValue);
            $statement->execute();
            $queryResult = $statement->get_result();
            $result = $queryResult->fetch_all(MYSQLI_ASSOC);
            return array_map(fn($el) => Movie::FromArray($el), $result);
        }
        return [];
    }

    public function removeMovie(int $id): bool
    {
        $sql = 'DELETE FROM ' . self::TABLE_NAME . ' WHERE id = ?';
        $statement = $this->db->conn->prepare($sql);

        if ($statement) {
            $statement->bind_param('i', $id);
            $statement->execute();
            return !$statement->errno;
        } else {
            return false;
        }
    }

    public function addMovie(Movie $movie): bool
    {
        try {
            $query = 'INSERT INTO ' . self::TABLE_NAME . ' (title, release_year, format, actors, description) VALUES (?, ?, ?, ?, ?)';
            $statement = $this->db->conn->prepare($query);

            if ($statement) {
                $statement->bind_param('sisss', $movie->title, $movie->release_year, $movie->format, $movie->actors, $movie->description);
                $result = $statement->execute();
                $statement->close();
                if ($result) {
                    return true;
                }
            }
        } catch (\Exception) {
        }
        return false;
    }

    /**
     *  Converts multiline text separated by blank lines into an array of arrays.
     *  The elements of an array will be a collection of key-value pairs.
     *
     * @param string $data
     * @return array
     */
    private function parseTextData(string $data): array
    {
        $result = [];

        $lines = explode("\n", ltrim($data, "\xEF\xBB\xBF"));
        $lines[] = '';

        $item = [];
        foreach ($lines as $line) {
            $line = trim($line);

            if ($line) {
                $pair = explode(':', $line, 2);
                if (count($pair) == 2) {

                    $key = trim($pair[0]);
                    $value = trim($pair[1]);
                    $item[$key] = $value;
                } else {
                    // invalid data format in the line. Skip
                    continue;
                }
            } elseif ($item) {
                $result[] = $item;
                $item = [];
            }
        }
        return $result;
    }

    public function import(?string $data): array
    {
        if (!$data) {
            return [];
        }
        $rawItems = $this->parseTextData($data);

        $result = [
            'num_error' => 0,
            'num_skip' => 0,
            'num_add' => 0,
        ];

        if ($rawItems) {
            // We extract from the array only keys that are useful to us
            $moviesData = array_map(fn($el) => [
                'title' => @$el['Title'],
                'release_year' => @$el['Release Year'],
                'format' => @$el['Format'],
                'actors' => @$el['Stars'],
            ], $rawItems);

            foreach ($moviesData as $movieData) {
                try { // safe read of each item
                    $movie = Movie::FromArray($movieData);
                    if ($this->movieExists($movie->title, $movie->release_year)) {
                        $result['num_skip']++;
                    } elseif ($this->addMovie($movie)) {
                        $result['num_add']++;
                    } else {
                        $result['num_error']++;
                    }
                } catch (\Exception $e) {
                    $result['num_error']++;
                }
            }
        }

        return $result;
    }

    public function movieExists(string $title, int $releaseYear): bool
    {
        $title = mysqli_real_escape_string($this->db->conn, $title);

        $query = 'SELECT * FROM ' . self::TABLE_NAME . " WHERE title = ? AND release_year = ?";
        $statement = $this->db->conn->prepare($query);
        if ($statement) {
            $statement->bind_param('si', $title, $releaseYear);
            $statement->execute();
            $queryResult = $statement->get_result();

            return $queryResult->num_rows > 0;
        }
        return false;
    }

}