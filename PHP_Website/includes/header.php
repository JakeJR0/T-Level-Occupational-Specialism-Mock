<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$page_name = $page_name ?? '';

?>

<html lang="en">
    <head>
        <title>ToKa Fitness - <?= $page_name ?></title>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="static/CSS/main.css">
        <script deter src="static/JS/navbar.js"></script>
    </head>
    <?php
    include 'includes/navbar.php';
    ?>
</html>