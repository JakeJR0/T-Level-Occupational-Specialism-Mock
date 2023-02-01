<?php

// Checks if the server request is a GET request

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $forum = $_GET["forum"] ?? null;
    $article = $_GET["article"] ?? null;

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
        $cancelled_url = "./index.php";
?>

        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Confirm Purchase</title>
            <link rel="stylesheet" href="./static/CSS/main.css">
            <script type="text/javascript">
                const purchaseType = "<?= $purchase_type ?>"
                const endUrl = "<?= $end_url ?>"
                const cancelUrl = "<?= $cancelled_url ?>"
            </script>
            <script src="./static/JS/confirmPurchase.js"></script>
        </head>
        <?php
        include 'includes/header.php'; ?>

        <body>
        </body>

        </html>
    <?php
        exit();
    }

    if (isset($_GET["article"])) {
        HandleArticlePurchase($article);
    } else if (isset($_GET["forum"])) {
        HandleForumPurchase($forum);
    } else {
        header("Location: /index.php");
    }
}

function HandleForumPurchase($forum_id)
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
            <div class="page-title space">
                <h1><?= ucfirst($type) ?> Purchase Confirmation</h1>
            </div>
            <div class="text-container">
                <h2 class="center-text">You have successfully purchased this <?= $type ?>!</h2>
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