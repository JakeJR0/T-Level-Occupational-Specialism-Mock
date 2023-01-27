<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$page_name = $page_name ?? '';
$page_url = "https://localhost/PHP_Website/$page_name.php";
$main_image = "static/images/home_page_main_display.jpg";
?>

<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="static/CSS/main.css">
        <script deter src="static/JS/navbar.js"></script>
        <title>ToKa Fitness - <?= $page_name ?></title>

    </head>
    <?php
    include 'includes/navbar.php';
    ?>
</html>