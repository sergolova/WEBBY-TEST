<?php
/** @var Movie $movie */

use Model\Movie;

include 'HeaderTemplate.php';
?>
<?php if (isset($movie)): ?>
    <div class='content-container'>
        <div class='movie-details'>
            <h1><?= $movie->title ?></h1>
            <p><span class='movie-attr'>Release Year:</span><?= $movie->release_year ?></p>
            <p><span class="movie-attr">Format:</span><?= $movie->format ?></p>
            <?php if (@$movie->actors): ?>
                <p><span class='movie-attr'>Actors:</span><?= $movie->actors ?></p>
            <?php endif; ?>
            <?php if (@$movie->description): ?>
                <p><span class='movie-attr'>Description:</span><?= $movie->description ?></p>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
<?php
include 'FooterTemplate.php';
?>