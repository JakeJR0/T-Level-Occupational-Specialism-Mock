<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$navbar_pages = array();

$client = $_SESSION["user"] ?? null;
$logged_in = $_SESSION["logged_in"] ?? false;

if (!$logged_in) {
    $navbar_pages[] = array('name' => 'Home', 'url' => 'index.php');
    $navbar_pages[] = array('name' => 'Articles', 'url' => 'articles.php');
    $navbar_pages[] = array('name' => 'Login', 'url' => 'login.php');
    $navbar_pages[] = array('name' => 'Forum', 'url' => 'forum.php');
    $navbar_pages[] = array('name' => 'About', 'url' => 'about.php');
    $navbar_pages[] = array('name' => 'Contact', 'url' => 'contact.php');
} else {
    $navbar_pages[] = array('name' => 'Home', 'url' => 'index.php');
    $navbar_pages[] = array('name' => 'Articles', 'url' => 'articles.php');
    $navbar_pages[] = array('name' => 'Forum', 'url' => 'forum.php');
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
                <a href="index.php">ToKa Fitness</a>
            </div>
            <div class="content">
                <ul>
                    <?php
                    foreach ($navbar_pages as $page) {
                        if (strtolower($page['name']) == strtolower($page_name)) {
                            ?>
                            <li class="active">
                                <a href="<?= $page['url'] ?>"><?= $page['name'] ?></a>
                                <div class="hover-underline">
                                    <div class="underline"><div>
                                </div>
                            </li>
                            <?php
                            continue;
                        }
                        ?>
                        <li>
                            <a href="<?= $page['url'] ?>"><?= $page['name'] ?></a>
                            <div class="hover-underline">
                                <div class="underline"><div>
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