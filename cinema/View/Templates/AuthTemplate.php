<?php
/** @var string $authError */
/** @var int $token */
include "HeaderTemplate.php";
?>
    <div class='auth-container'>
        <form action='/login' method='post'>
            <input type='hidden' name='csrf_token' value='<?= $token; ?>'>

            <label for='username'>User name:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" minlength="3" required>

            <button type="submit" name="login">Sign in</button>
            <button type="submit" name='register'>Register</button>

            <?php if (isset($authError)): ?>
                <span class="auth-error"><?= $authError ?></span>
            <?php endif; ?>
        </form>
    </div>

<?php
include 'FooterTemplate.php';
?>