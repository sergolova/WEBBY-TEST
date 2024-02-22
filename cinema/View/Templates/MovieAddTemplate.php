<?php
/** @var array $constraints */
/** @var string $token */
/** @var array $releaseRange */
?>
<div class='modal' id='addMovieModal'>
    <div class='add-movie-form'>
        <span class='close' onclick="document.getElementById('addMovieModal').style.display = 'none'">&times;</span>
        <h2>Add Movie</h2>

        <form action='/movie-add' method='post'>
            <input type='hidden' name='csrf_token' value='<?= @$token; ?>'>
            <label>Title:
                <input type='text' name='title' maxlength="<?=$constraints['max_title_length'] ?>" required>
            </label>
            <label>Year:
                <input type='number' name='year' min='<?= $constraints['min_release_year'] ?>' max='<?= $constraints['max_release_year'] ?>' required>
            </label>
            <label>Format:
                <select name='format' required>
                    <?php foreach ($constraints['format_enums'] as $f): ?>
                        <option value='<?= $f ?>'><?= $f ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Actors:
                <input type='text' name='actors' maxlength="<?=$constraints['max_actors_length'] ?>">
            </label>
            <label>Description:
                <textarea name='description' maxlength="<?=$constraints['max_description_length'] ?>"></textarea>
            </label>
            <button type='submit'>Add</button>
        </form>
    </div>
</div>

<script>
    window.addEventListener('mousedown', (event) => {
        const modal = document.getElementById('addMovieModal');
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });

    function showAddMovieModal() {
        const modal = document.getElementById('addMovieModal');
        modal.style.display = 'block';
        modal.focus();
    }
</script>