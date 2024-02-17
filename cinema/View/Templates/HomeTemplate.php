<?php /** @var array $movies */
include 'HeaderTemplate.php';
?>

<?php
foreach ($movies as $m): ?>
    <?= $m->title ?><br>
<?php endforeach; ?>

<?php
include 'FooterTemplate.php';
?>