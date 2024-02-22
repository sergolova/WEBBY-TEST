<?php

namespace Model;

class Movie
{
    public int $id;
    public string $title;
    public int $release_year;
    public string $format;
    public ?string $actors;
    public ?string $description;

    public static function constraints(): array
    {
        return [
            'format_enums' => ['VHS', 'DVD', 'Blu-ray'],
            'max_title_length' => 256,
            'max_actors_length' => 256,
            'max_description_length' => 8192,
            'min_release_year' => 1900,
            'max_release_year' => (int)date('Y') + 10,
        ];
    }

    public function validate(): bool
    {
        $c = self::constraints();

        return trim($this->title) !== ''
            && $this->release_year >= $c['min_release_year']
            && $this->release_year <= $c['max_release_year']
            && mb_strlen($this->title) <= $c['max_title_length']
            && mb_strlen($this->actors) <= $c['max_actors_length']
            && mb_strlen($this->description) <= $c['max_description_length']
            && in_array(strtolower($this->format), array_map('mb_strtolower', Movie::constraints()['format_enums']));
    }

    public static function FromArray(array $row): Movie
    {
        $movie = new Movie();
        $movie->id = $row['id'] ?? 0;
        $movie->title = $row['title'];
        $movie->release_year = $row['release_year'];
        $movie->format = $row['format'];
        $movie->actors = @$row['actors'];
        $movie->description = @$row['description'];

        return $movie;
    }
}