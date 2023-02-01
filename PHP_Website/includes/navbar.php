<?php

// Start the session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Used to store the pages for the navbar
$navbar_pages = array();

// Gets the users information
$client = $_SESSION["user"] ?? null;
$logged_in = $_SESSION["logged_in"] ?? false;

// Checks if the user is logged in
if (!$logged_in) {
    $navbar_pages[] = array('name' => 'Home', 'url' => 'index.php');
    $navbar_pages[] = array('name' => 'Articles', 'url' => 'articles.php');
    $navbar_pages[] = array('name' => 'Login', 'url' => 'login.php');
    // Removing the forum page due to lack of time
    // $navbar_pages[] = array('name' => 'Forum', 'url' => 'forum.php');
    $navbar_pages[] = array('name' => 'About', 'url' => 'about.php');
    $navbar_pages[] = array('name' => 'Contact', 'url' => 'contact.php');
} else {
    $navbar_pages[] = array('name' => 'Home', 'url' => 'index.php');
    $navbar_pages[] = array('name' => 'Articles', 'url' => 'articles.php');
    // Removing the forum page due to lack of time
    // $navbar_pages[] = array('name' => 'Forum', 'url' => 'forum.php');
    $navbar_pages[] = array('name' => 'About', 'url' => 'about.php');
    $navbar_pages[] = array('name' => 'Contact', 'url' => 'contact.php');
    $navbar_pages[] = array('name' => 'Logout', 'url' => 'logout.php');
}


?>

<!DOCTYPE html>
<html lang="en">

<body>
    <nav class="navbar">
        <div class="brand">
            <!-- Website Name -->
            <a href="index.php">ToKa Fitness</a>
        </div>
        <div class="content">
            <ul>
                <?php
                foreach ($navbar_pages as $page) {
                    if (strtolower($page['name']) == strtolower($page_name)) {
                ?>
                        <li class="active">
                            <!-- Hover Underline (Allows the animation to function) -->
                            <a href="<?= $page['url'] ?>"><?= $page['name'] ?></a>
                            <div class="hover-underline">
                                <!-- Container for the underline -->
                                <div class="underline">
                                    <div>
                                    </div>
                        </li>
                    <?php
                        continue;
                    }
                    ?>
                    <li>
                        <a href="<?= $page['url'] ?>"><?= $page['name'] ?></a>
                        <!-- Hover Underline (Allows the animation to function) -->
                        <div class="hover-underline">
                            <!-- Container for the underline -->
                            <div class="underline">
                                <div>
                                </div>
                    </li>
                <?php
                }
                ?>
            </ul>
        </div>
        <div class="toggle-button">
            <div class="line"></div>
            <div class="line"></div>
            <div class="line"></div>
        </div>
    </nav>
</body>

</html>