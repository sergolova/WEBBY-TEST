<?php
/** @var array $movies */
/** @var Movie $movie */
/** @var User $user */
/** @var string $token */
/** @var string $queryValue */
/** @var string $message */
/** @var string $messageType */
/** @var string $queryKey */

use Model\Movie;
use Model\User;

include 'HeaderTemplate.php';
?>
    <div class='content-container movies-container'>
        <?php
        if (@$message): ?>
            <div class='message-container <?= $messageType ?? 'info' ?>' onclick="this.style.display = 'none'"
                 title="Click for hide this message">
                <p class='message'><?= $message ?></p>
            </div>
        <?php endif; ?>

        <?php if ($user?->can('movie add')): // Only for registered users ?>
            <?php include "MovieAddTemplate.php" ?>
            <?php include "MovieImportTemplate.php" ?>
            <div class="add-movies-container">
                <button onclick='showAddMovieModal()'>Add Movie...</button>
                <button onclick='showImportMovieModal()'>Import Movies...</button>
            </div>
        <?php endif; ?>

        <div class='search-bar'>
            <form action='/' method='get'>
                <label> Search
                    <input type='text' name='query_value' value="<?= @$queryValue ?>"
                           placeholder='Example: Reeves'
                           required>
                </label>
                <button class='cancel' type='button' onclick="window.location.href='/'"
                        title="Cancel search">x
                </button>
                <button type='submit' name='query_key' value='title'>in Title</button>
                <button type='submit' name='query_key' value='actors'>in Actor</button>
            </form>
        </div>

        <?php if (isset($movies)): ?>
            <div class='movie-list'>
                <?php
                $cnt = count($movies);

                if (@$queryValue && @$queryKey) {
                    $msg = "Search '$queryValue' in $queryKey. ";
                    $msg .= $cnt ? "Found $cnt movies" : 'No movies found for your request';
                } else {
                    $msg = $cnt > 0 ? "All movies" : 'No movies in repository';
                }
                $msg .= $cnt ? ". Sorted by Title" : '';
                ?>
                <div class='movie-list-info'><?= $msg ?></div>

                <?php foreach ($movies as $movie): ?>
                    <div class='movie-item' id='movie-item-<?= $movie->id ?>'>
                        <a href='/movie-details?id=<?= $movie->id ?>'><?= $movie->title ?></a>
                        <p><span class='movie-attr'>Year:</span><span class="movie-value"><?= $movie->release_year ?></span></p>
                        <p><span class='movie-attr'>Format:</span><span class="movie-value"><?= $movie->format ?></span></p>
                        <?php if (@$movie->actors): ?>
                            <p><span class='movie-attr'>Actors:</span>
                                <span class="movie-value"><?= $movie->actors ?></span>
                            </p>
                        <?php endif; ?>
                        <?php if ($user?->can('movie del')): ?>
                            <form action="/movie-delete" method="post">
                                <input type='hidden' name='csrf_token' value='<?= $token; ?>'>
                                <input type='hidden' name='movie_id' value="<?= $movie->id ?>">
                                <button type="submit">Delete</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
<?php
include 'FooterTemplate.php';
?>