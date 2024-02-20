<?php
/** @var array $formats */
/** @var string $token */
?>
<div class='modal' id='addMovieModal'>
    <div class='add-movie-form'>
        <span class='close' onclick="document.getElementById('addMovieModal').style.display = 'none'">&times;</span>
        <h2>Add Movie</h2>

        <form action='/movie-add' method='post'>
            <input type='hidden' name='csrf_token' value='<?= @$token; ?>'>
            <label>Title:
                <input type='text' name='title' required>
            </label>
            <label>Year:
                <input type='number' name='year' min='1900' max='2030' required>
            </label>
            <label>Format:
                <select name='format' required>
                    <?php foreach ($formats as $f): ?>
                        <option value='<?= $f ?>'><?= $f ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Actors:
                <input type='text' name='actors'>
            </label>
            <label>Description:
                <textarea name='description'></textarea>
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