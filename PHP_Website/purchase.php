<?php

// Sets

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
        header("Location: login.php");
        return;
    }

    $user_id = $user["ID"];

    if ($forum == null || $article == null) {
        header("Location: index.php");
    } elseif ($forum != null) {
        $forum_check_sql = "
            SELECT ID
            FROM forum_purchase_history
            WHERE item_id = '".$forum."' AND user_id = '".$user_id."'
        ";

        $forum_check_result = mysqli_query($conn, $forum_check_sql);

        if (mysqli_num_rows($forum_check_result) > 0) {
            header("Location: forum.php?forum=".$forum);
        } else {
            $forum_purchase_sql = "
                INSERT INTO forum_purchase_history (item_id, user_id)
                VALUES ('".$forum."', '".$user_id."')
            ";

            $forum_purchase_result = mysqli_query($conn, $forum_purchase_sql);

            // Displays

            DisplayPurchaseConfirmation("forum", $forum);
        }
    } elseif ($article) {
        $article_check_sql = "
            SELECT ID
            FROM article_purchase_history
            WHERE item_id = '".$article."' AND user_id = '".$user_id."'
        ";

        $article_check_result = mysqli_query($conn, $article_check_sql);

        if (mysqli_num_rows($article_check_result) > 0) {
            header("Location: article.php?article=".$article);
        } else {
            $article_purchase_sql = "
                INSERT INTO article_purchase_history (item_id, user_id)
                VALUES ('".$article."', '".$user_id."')
            ";

            $article_purchase_result = mysqli_query($conn, $article_purchase_sql);

            // Displays

            DisplayPurchaseConfirmation("article", $article);
        }
    }
}

function DisplayPurchaseConfirmation($type="", $item_id="") {
?>

<DOCTYPE html>
<html>
    <head>
        <title>Forum Purchase Confirmation</title>
    </head>
    <body>
        <div class="page-title">
            <h1>Forum Purchase Confirmation</h1>
            <p>Thank you for purchasing this forum!</p>
        </div>
        <div class="page-content">
            <p>You have successfully purchased this forum!</p>
            <p><a href="forum.php?<?= $type."=".$item_id ?>">Click here to view your forum</a></p>
        </div>
    </body>
</html>

<?php } ?>
