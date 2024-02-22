<?php

namespace Model;

use Error;
use Exception;

class MovieManager
{
    private static ?MovieManager $instance = null;
    private readonly DatabaseManager $db;
    private const TABLE_NAME = 'movies';
    private const CHARSET = 'utf8mb4_unicode_ci';

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
        $enums = array_map(fn($el) => "'$el'", Movie::constraints()['format_enums']);
        $enums = implode(', ', $enums);

        $createTableQuery = "CREATE TABLE IF NOT EXISTS " . self::TABLE_NAME . " (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(" . Movie::constraints()['max_title_length'] . ") NOT NULL ,
        release_year INT(4) NOT NULL,
        format ENUM($enums) NOT NULL,
        actors VARCHAR(" . Movie::constraints()['max_actors_length'] . "),
        description TEXT) CHARACTER SET utf8mb4 COLLATE " . self::CHARSET;

        if (!$this->db->conn->query($createTableQuery)) {
            throw new Error('Error creating table:' . $this->db->conn->error);
        }
        
        // Change Collation of an existing table
        $mod = "ALTER TABLE " . self::TABLE_NAME . " CONVERT TO CHARACTER SET utf8mb4 COLLATE " . self::CHARSET;

        if (!$this->db->conn->query($mod)) {
            throw new Error('Error change table:' . $this->db->conn->error);
        }
    }

    public function getMovie(int $id): ?Movie
    {
        $query = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE id = ?';
        $statement = $this->db->conn->prepare($query);
        $row = null;

        if ($statement) {
            $statement->bind_param('i', $id);
            $statement->execute();
            $result = $statement->get_result();
            $statement->close();
            $row = $result->fetch_assoc();
        }
        return is_array($row) ? Movie::FromArray($row) : null;
    }

    public function getMovies(): array
    {
        $query = 'SELECT * FROM ' . self::TABLE_NAME . ' ORDER BY title ASC';
        $statement = $this->db->conn->prepare($query);

        if ($statement) {
            $statement->execute();
            $result = $statement->get_result();
            $rows = $result->fetch_all(MYSQLI_ASSOC);
            $statement->close();

            return array_map(fn($el) => Movie::FromArray($el), $rows);
        }
        return [];
    }

    /**
     * @throws Exception
     */
    public function queryMovies(string $queryKey, string $queryValue): array
    {
        if ($queryKey !== 'title' && $queryKey !== 'actors') {
            throw new Exception('Invalid query keys');
        }

        $query = 'SELECT * FROM ' . self::TABLE_NAME . " WHERE $queryKey LIKE ? ORDER BY title ASC";
        $queryValue = '%' . $queryValue . '%';
        $statement = $this->db->conn->prepare($query);
        if ($statement) {
            $statement->bind_param('s', $queryValue);
            $statement->execute();
            $result = $statement->get_result();
            $rows = $result->fetch_all(MYSQLI_ASSOC);
            $statement->close();

            return array_map(fn($el) => Movie::FromArray($el), $rows);
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
            $result = !$statement->errno;
            $statement->close();
            return $result;
        }

        return false;
    }

    public function addMovie(Movie $movie): bool
    {
        $query = 'INSERT INTO ' . self::TABLE_NAME . ' (title, release_year, format, actors, description) VALUES (?, ?, ?, ?, ?)';
        $statement = $this->db->conn->prepare($query);

        if ($statement) {
            $statement->bind_param('sisss', $movie->title, $movie->release_year,
                $movie->format, $movie->actors, $movie->description);
            $statement->execute();
            $result = !$statement->errno;
            $statement->close();
            return $result;
        }
        return false;
    }

    /**
     *  Converts multiline text separated by blank lines into an array of arrays.
     *  The elements of an array will be a collection of key-value pairs.
     *  Lines that do not contain a key-value pair are ignored.
     *
     * @param string $data
     * @return array
     */
    private function parseTextData(string $data): array
    {
        $result = [];

        $lines = explode("\n", $data);
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
                    // invalid data format in the line. Ignore
                    continue;
                }
            } elseif ($item) { // add to result
                $result[] = $item;
                $item = [];
            }
        }
        return $result;
    }

    /** At the beginning of the text data there may be an encoding signature.
     *  We recognize the encoding, remove the signature and convert the data to the required encoding
     * @param string $data - raw data
     * @return string
     */
    private function decodeTextData(string $data): string
    {
        $encoding = 'UTF-8'; // default

        $signatures = [
            'UTF-8' => "\xEF\xBB\xBF",
            'UTF-16LE' => "\xFF\xFE",
            'UTF-16BE' => "\xFE\xFF",
        ];

        // remove signature
        foreach ($signatures as $enc => $sign) {
            $dataSignature = substr($data, 0, strlen($sign));
            if ($dataSignature === $sign) {
                $data = substr($data, strlen($sign));
                $encoding = $enc;
                break;
            }
        }
        return mb_convert_encoding($data, 'UTF-8', $encoding);
    }

    /** Adds Movies from text data
     * @param string|null $data raw text data
     * @return array|int[] import status array. Keys:
     * - num_error - the number of structures from which it was not possible to create a valid Movie model;
     * - num_skip - the number of Movies that are already in the repository;
     * - num_add - number of Movies added to the repository
     */
    public function import(?string $data): array
    {
        if (!$data) {
            return [];
        }
        $data = $this->decodeTextData($data);
        $rawItems = $this->parseTextData($data);

        $result = [
            'num_error' => 0,
            'num_skip' => 0,
            'num_add' => 0,
        ];

        if ($rawItems) {
            // We extract from the array only keys that are useful to us
            $moviesData = array_map(fn($el) => [
                'title' => htmlspecialchars(@$el['Title']), // strip_tags ????
                'release_year' => htmlspecialchars(@$el['Release Year']),
                'format' => htmlspecialchars(@$el['Format']),
                'actors' => htmlspecialchars(@$el['Stars']),
            ], $rawItems);

            foreach ($moviesData as $movieData) {
                try { // safe read of each item
                    $movie = Movie::FromArray($movieData);

                    if ($this->movieExists($movie->title, $movie->release_year)) {
                        $result['num_skip']++;
                    } elseif ($movie->validate() && $this->addMovie($movie)) {
                        $result['num_add']++;
                    } else {
                        $result['num_error']++;
                    }
                } catch (\Error) {
                    $result['num_error']++;
                }
            }
        }

        return $result;
    }

    public function movieExists(string $title, int $releaseYear): bool
    {
        $query = 'SELECT * FROM ' . self::TABLE_NAME . " WHERE title = ? AND release_year = ?";
        $statement = $this->db->conn->prepare($query);
        $statement->bind_param('si', $title, $releaseYear);
        $statement->execute();
        $result = $statement->get_result();

        return $result->num_rows > 0;
    }

}