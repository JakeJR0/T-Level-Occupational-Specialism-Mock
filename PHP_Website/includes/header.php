<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Used to set the page name in the title
$page_name = $page_name ?? '';
$page_url = "https://localhost/PHP_Website/$page_name.php";

// Used to set the main image on the home page
$main_image = "static/images/home_page_main_display.jpg";
?>

<html lang="en">

<head>
    <!-- Meta Tags -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="static/CSS/main.css">
    <!-- Navbar JS allows the mobile functionality -->
    <script defer src="static/JS/navbar.js"></script>
    <title>ToKa Fitness - <?= $page_name ?></title>
</head>
<!-- Navigation Bar -->
<?php
// Include the navbar into the header
include 'includes/navbar.php';
?>

</html>