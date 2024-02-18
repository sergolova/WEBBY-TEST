<?php /** @var array $movies */

/** @var Movie $movie */

use Model\Movie;

include 'HeaderTemplate.php';
?>

    <div class='movies-container'>
        <div class='search-bar'>
            <input type='text' placeholder='Search'>
            <button>Search by Title</button>
            <button>Search by Actor</button>
        </div>

        <button onclick="window.location.href='/add_movie'">Add Movie...</button>

        <?php if (isset($movies)): ?>
            <div class='movie-list'>
                <?php foreach ($movies as $movie): ?>
                    <div class='movie-item' id='movie-item-<?= $movie->id ?>'>
                        <a href='/movie_details?id=1'><?= $movie->title ?></a>
                        <p>Year: <span><?= $movie->release_year ?></span></p>
                        <p>Format: <span><?= $movie->format ?></span></p>
                        <p>Actors: <span><?= $movie->actors ?></span></p>
                        <form action="/movie-delete" method="post">
                            <button type="submit" name="del-<?= $movie->id ?>">Delete</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

<?php
include 'FooterTemplate.php';
?>