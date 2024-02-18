<?php
/** @var array $formats */
/** @var string $token */
?>
<div class='modal' id='addMovieModal'>
    <div class='modal-content'>
        <span class='close' onclick='closeModal()'>&times;</span>
        <div class='add-movie-form'>
            <h2>Add Movie</h2>
            <form action='/movie-add' method='post'>
                <input type='hidden' name='csrf_token' value='<?= @$token; ?>'>
                <label for='title'>Title:</label>
                <input type='text' id='title' name='title' required>

                <label for='format'>Format:</label>
                <select id='format' name='format' required>
                    <?php foreach ($formats as $f): ?>
                        <option value='<?=$f ?>'><?=$f ?></option>
                    <?php endforeach; ?>
                </select>

                <label for='year'>Year:</label>
                <input type='number' id='year' name='year' min="1900" max="2030" value="2000" required>

                <label for='actors'>Actors:</label>
                <input type='text' id='actors' name='actors'>

                <button type='submit'>Add</button>
            </form>
            <form action='/movie-import' method='post' enctype='multipart/form-data'>
                <label for='file'>Import from file:</label>
                <input type='file' id='file' name='file' accept='.txt' required>
                <button type='submit'>Import</button>
            </form>
        </div>
    </div>
</div>

<script>
    function openModal() {
        document.getElementById('addMovieModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('addMovieModal').style.display = 'none';
    }

    // Закрыть модальное окно при щелчке вне его области
    window.onclick = function (event) {
        var modal = document.getElementById('addMovieModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
</script>