<?php
/** @var string $token */
?>
<div class='modal' id='importMovieModal'>
    <div class='add-movie-form'>
        <span class='close' onclick="document.getElementById('importMovieModal').style.display = 'none'">&times;</span>
        <h2>Import Movies</h2>
        <form action='/movie-import' method='post' enctype='multipart/form-data'>
            <input type='hidden' name='csrf_token' value='<?= @$token; ?>'>
            <label for='file'>Select file:</label>
            <input type='file' id='file' name='file' accept='.txt' required>
            <button type='submit'>Import</button>
        </form>
    </div>
</div>

<script>
    window.addEventListener('click', (event) => {
        const modal = document.getElementById('importMovieModal');
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });

    function showImportMovieModal() {
        const modal = document.getElementById('importMovieModal');
        modal.style.display = 'block';
        modal.focus();
    }
</script>