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