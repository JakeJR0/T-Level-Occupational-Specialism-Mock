<?php

// Checks if the server request is a GET request

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Gets the forum ID from the GET request
    $forum = $_GET["forum"] ?? null;
    // Gets the article ID from the GET request
    $article = $_GET["article"] ?? null;

    // Starts the session
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Checks if the user is logged in
    $logged_in = $_SESSION["logged_in"] ?? false;
    $user = $_SESSION["user"] ?? null;

    if ($logged_in == false || $user == null) {
        // header("Location: login.php");
        echo "You must be logged in to purchase a forum or article.";
        return;
    }

    // Checks if the user has confirmed the purchase
    if (!isset($_GET["confirmed"])) {
        $purchase_type = "";


        if ($forum != null) {
            $purchase_type = "forum";
        } else if ($article != null) {
            $purchase_type = "article";
        } else {
            header("Location: /index.php");
        }

        $end_url = "./purchase.php?confirmed=true&" . $purchase_type . "=" . $_GET[$purchase_type];
        // Cancelled URL redirects to the either the forum or article page with the ID of the forum or article

        $cancelled_url = "";

        if ($purchase_type == "forum") {
            $cancelled_url = "./forum.php?forum=" . $_GET[$purchase_type];
        } else if ($purchase_type == "article") {
            $cancelled_url = "./articles.php?article=" . $_GET[$purchase_type];
        }

?>

        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Confirm Purchase</title>
            <link rel="stylesheet" href="./static/CSS/main.css">
            <script type="text/javascript">
                // Passes the purchase type, end URL and cancelled URL to the JS file from the PHP file
                const purchaseType = "<?= $purchase_type ?>"
                const endUrl = "<?= $end_url ?>"
                const cancelledUrl = "<?= $cancelled_url ?>"
            </script>
            <script src="./static/JS/confirmPurchase.js"></script>
        </head>
        <?php
        // Include the header.php file
        include 'includes/header.php'; ?>

        <body>
        </body>

        </html>
    <?php
    } else {
        if (isset($_GET["article"])) {
            HandleArticlePurchase($article);
        } else if (isset($_GET["forum"])) {
            HandleForumPurchase($forum);
        } else {
            header("Location: /index.php");
        }
    }
}

function HandleForumPurchase($forum_id)
{
    // Starts the session
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    $logged_in = $_SESSION["logged_in"] ?? false;
    $user = $_SESSION["user"] ?? null;

    if ($logged_in == false || $user == null) {
        // header("Location: login.php");
        echo "You must be logged in to purchase a forum or article.";
        return;
    }

    require_once "../storage.php";

    $user_id = $user["ID"];

    // Sanitise the forum ID and user ID
    $forum_id = trim($forum_id);
    $user_id = trim($user_id);

    $forum_id = mysqli_real_escape_string($connection, $forum_id);
    $user_id = mysqli_real_escape_string($connection, $user_id);


    $forum_check_sql = "
        SELECT ID
        FROM forum_purchase_history
        WHERE item_id = '" . $forum_id . "' AND user_id = '" . $user_id . "'
    ";

    $forum_check_result = mysqli_query($connection, $forum_check_sql);

    if (mysqli_num_rows($forum_check_result) > 0) {
        header("Location: forum.php?forum=" . $forum_id);
    } else {
        $forum_purchase_sql = "
            INSERT INTO forum_purchase_history (item_id, user_id)
            VALUES ('" . $forum_id . "', '" . $user_id . "')
        ";

        $forum_purchase_result = mysqli_query($connection, $forum_purchase_sql);

        // Displays

        DisplayPurchaseConfirmation("forum", $forum_id);
    }
}

function HandleArticlePurchase($article_id)
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    $logged_in = $_SESSION["logged_in"] ?? false;
    $user = $_SESSION["user"] ?? null;

    if ($logged_in == false || $user == null) {
        // header("Location: login.php");
        echo "You must be logged in to purchase a forum or article.";
        return;
    }

    require_once "../storage.php";

    $user_id = $user["ID"];

    // Sanitise the article ID and user ID

    $article_id = trim($article_id);
    $user_id = trim($user_id);

    $article_id = mysqli_real_escape_string($connection, $article_id);
    $user_id = mysqli_real_escape_string($connection, $user_id);

    $article_check_sql = "
        SELECT ID
        FROM article_purchase_history
        WHERE item_id = '" . $article_id . "' AND user_id = '" . $user_id . "'
    ";

    $article_check_result = mysqli_query($connection, $article_check_sql);

    if (mysqli_num_rows($article_check_result) > 0) {
        header("Location: articles.php?article=" . $article_id);
    } else {
        $article_purchase_sql = "
            INSERT INTO article_purchase_history (item_id, user_id)
            VALUES ('" . $article_id . "', '" . $user_id . "')
        ";

        $article_purchase_result = mysqli_query($connection, $article_purchase_sql);

        if ($article_purchase_result == false) {
            echo "Error: " . $article_purchase_sql . "<br>" . mysqli_error($connection);
        }

        // Displays purchase confirmation
        DisplayPurchaseConfirmation("article", $article_id);
    }
}

function DisplayPurchaseConfirmation($type = "", $item_id = "")
{
    $page_name = "Purchase Confirmation";
    ?>

    <DOCTYPE html>
        <html>
        <?php include 'includes/header.php'; ?>

        <body>
            <!-- Page Title -->
            <div class="page-title space">
                <h1 aria-label="Purchase Confirmation"><?= ucfirst($type) ?> Purchase Confirmation</h1>
            </div>
            <div class="text-container">
                <h2 aria-label="You have purchased an <?= $type ?>" class="center-text">You have successfully purchased this <?= $type ?>!</h2>
                <?php
                $url = "";
                if ($type == "article") {
                    $url = "articles.php?article=" . $item_id;
                } else if ($type == "forum") {
                    $url = "forum.php?forum=" . $item_id;
                }
                ?>
                <a class="main-link center-text center-middle" href="<?= $url ?>">Click here to view your <?= $type ?></a>
            </div>
        </body>

        </html>

    <?php } ?>