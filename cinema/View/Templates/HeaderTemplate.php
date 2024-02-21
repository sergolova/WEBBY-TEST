<?php
/** @var string $title */
/** @var User $user */
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel='icon' href='/View/Images/favicon.png' type='image/x-icon'>
    <link rel='preload' href='/View/Images/bg.webp' as="image">
    <link rel='prerender' href='/View/Images/bg.webp'>
    <title><?= $title ?? 'Cinema' ?></title>

    <?php if (isset($styles)) : ?>
        <?php foreach ($styles as $style): ?>
            <link rel='stylesheet' href='<?= STYLES_URL . '/' . $style . '.css' ?>'>
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
<header>
    <div class='logo'>
        <a href="/">CINEMA</a>
    </div>
    <?php if (!isset($noAuthInHeader)): ?>
        <div class='user-info'>
            <?php if (isset($user)): ?>
                <span>Welcome, <?= $user->username ?></span>
                <a href="/logout" class="auth-btn">Logout</a>
                <a href='/unregister' class='auth-btn'>Unregister</a>
            <?php else : ?>
                <span>Please login or register</span>
                <a href="/login" class='auth-btn'>Login</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</header>
<main>