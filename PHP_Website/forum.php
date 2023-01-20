<?php
require_once '../storage.php';


if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    if (isset($_GET["article"])) {
        $forum_id = $_GET["forum"];

        $forum_id = trim($forum_id);
        $forum_id = strip_tags($forum_id);
        $forum_id = mysqli_real_escape_string($connection, $forum_id);
        $forum_sql = "
            SELECT users.first_name, users.last_name, forums.title, forums.content, DATE_FORMAT(forums.last_updated, '%d/%m/%Y') AS last_updated, forums.price
            FROM forums
            INNER JOIN users ON forums.creator_id = users.ID
            WHERE forums.ID = $forum_id
        ";

        $forum = mysqli_query($connection, $forum_sql);
        $forum = mysqli_fetch_assoc($forum);

        $forum_price = $forum["price"];
        $forum_price = number_format($forum_price, 2);
        $include_purchase_button = false;
        if ($forum_price > 0) {
            $user = $_SESSION["user"];
            $user_id = $user["ID"] ?? null;
            $paid_sql = "
                SELECT ID
                FROM purchases
                WHERE forum_id = $forum_id AND user_id = {$user_id}
            ";

            $paid = mysqli_query($connection, $paid_sql);

            if (mysqli_num_rows($paid) == 0) {
                $forum["content"] = "You must pay £{$forum_price} to view this forum";
                $include_purchase_button = true;
            }
        }

        if ($forum) {
            DisplayForum($forum, $include_purchase_button);
        } else {
            header("Location: /forums.php");
        }
    } else {
        $page_number = $_GET["page"] ?? 1;
        try {
            $page_number = (int) intval($page_number);
        } catch (Exception $e) {
            $page_number = 1;
        }

        // Each page will have 10 forums
        $page_size = 10;
        $offset = ($page_number - 1) * $page_size;


        // Get the total number of forums
        $total_sql = "
            SELECT COUNT(*) AS total
            FROM forum_threads
        ";

        $forums_sql = "
            SELECT ID, title, created_on, last_updated, price
            FROM forum_threads
            ORDER BY created_on DESC
            LIMIT $offset, $page_size
        ";

        $forums = mysqli_query($connection, $forums_sql);
        $total_forums = mysqli_query($connection, $total_sql);
        $total_forums = mysqli_fetch_assoc($total_forums)["total"];

        $total_pages = ceil($total_forums / $page_size);

        DisplayForumView($forums, $page_number, $total_pages);
    }
}

function DisplayForumView($forums, $page_number, $total_pages) {
?>

<!DOCTYPE html>
<html lang="en">
    <?php
    $page_name = "Forums";
    include 'includes/header.php';
    ?>
    <body>
    <div class="page-title">
            <h1>Forums</h1>
            <p>Here you can view content created by our community</p>
        </div>
        <div class="list-container">
            <?php
            for ($i = 0; $i < mysqli_num_rows($forums); $i++) {
                $forum = mysqli_fetch_assoc($forums);
                $forum_id = $forum["ID"];
                $forum_title = $forum["title"];
                $forum_created_on = $forum["created_on"];
                $forum_last_updated = $forum["last_updated"];
                $forum_price = $forum["price"];

                $forum_price = floatval($forum_price);
                $formatted_price = (String) "£" . number_format($forum_price, 2);
            ?>

            <a class="list-option" href="?forum=<?= $forum_id ?>">
                <div class="title">
                    <p class="title"><?= $forum_title ?></p>
                    <div class="badges">
                    <?php
                        if ($forum_price == 0) {
                            echo "<p class='badge small free'>Free</p>";
                        } else {
                            echo "<p class='badge small paid'>$formatted_price</p>";
                        }
                    ?>
                    </div>
                </div>
                <p class="created">Written on <?= $forum_created_on ?></p>
                <p class="last-updated">Last Updated on <?= $forum_last_updated ?></p>
            </a>
            <?php } ?>
            <div class="page-counter">
                <?php
                    if ($page_number > 1) {
                        echo "<a class='page-action' href='?page=".($page_number - 1)."'><</a>'";
                    }

                    if ($total_pages == 0) {
                        $total_pages = 1;
                    }

                    echo "<p class='page-number'>Page $page_number of $total_pages</p>";

                    if ($page_number < $total_pages) {
                        echo "<a class='page-action' href='?page=".($page_number + 1)."'>></a>";
                    }
                ?>
            </div>
        </div>
    </body>
</html>
<?php

}

function DisplayForum($forum, $include_purchase_button) {
    $user_first_name = $forum["first_name"];
    $user_last_name = $forum["last_name"];
    $user_full_name = $user_first_name . " " . $user_last_name;

    $forum_title = $forum["title"];
    $forum_content = $forum["content"];
    $forum_last_updated = $forum["last_updated"];
    $forum_price = $forum["price"];

    $forum_price = floatval($forum_price);

?>
<!DOCTYPE html>
<html lang="en">
    <?php
    $page_name = "forums";
    include 'includes/header.php';
    ?>
    <body>
        <div class="page-title">
            <h1><?= $forum_title ?></h1>
            <p>Written by <?= $user_full_name ?></p>
        </div>
        <div class="text-container">
            <?= $forum_content ?>
            <a class='bottom-left btn faced-text' href='forums.php'>Back</a>
            <p class="faded-text bottom-right">Last Updated on <?= $forum_last_updated ?></p>
            <?php
                if ($include_purchase_button) {
                    echo "<a class='bottom-middle btn' href='purchase.php?forum={$forum_id}'>Purchase forum</a>";
                }
            ?>
        </div>
    </body>
</html>
<?php
}
?>