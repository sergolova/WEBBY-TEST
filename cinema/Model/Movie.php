<?php

namespace Model;
class Movie
{
    public int $id;
    public string $title;
    public int $release_year;
    public string $format;
    public string $actors;

    public static function FromRow(array $row): Movie
    {
        $movie = new Movie();
        $movie->id = @$row['id'];
        $movie->title = @$row['title'];
        $movie->release_year = @$row['release_year'];
        $movie->format = @$row['format'];
        $movie->actors = @$row['actors'];

        return $movie;
    }
}