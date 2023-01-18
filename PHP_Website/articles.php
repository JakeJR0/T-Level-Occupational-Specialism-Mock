<?php
require_once '../storage.php';

if ($_SERVER["REQUEST_METHOD"] == "GET") {

    if (isset($_GET["article"])) {
        $article_id = $_GET["article"];

        $article_id = trim($article_id);
        $article_id = strip_tags($article_id);
        $article_id = mysqli_real_escape_string($connection, $article_id);

        $article_sql = "
            SELECT title, content, created_on, last_updated, price
            FROM articles
            WHERE ID = $article_id
        ";

        $article = mysqli_query($connection, $article_sql);
        $article = mysqli_fetch_assoc($article);

        if ($article) {
            DisplayArticle($article["title"], $article["content"], $article["created_on"], $article["last_updated"], $article["price"]);
        } else {
            header("Location: /articles.php");
        }
    } else {
        $page_number = $_GET["page"] ?? 1;
        try {
            $page_number = (int) intval($page_number);
        } catch (Exception $e) {
            $page_number = 1;
        }

        // Each page will have 10 articles
        $page_size = 10;
        $offset = ($page_number - 1) * $page_size;


        // Get the total number of articles
        $total_sql = "
            SELECT COUNT(*) AS total
            FROM articles
        ";

        $articles_sql = "
            SELECT ID, title, created_on, last_updated, price
            FROM articles
            ORDER BY created_on DESC
            LIMIT $offset, $page_size
        ";

        $articles = mysqli_query($connection, $articles_sql);
        $total_articles = mysqli_query($connection, $total_sql);
        $total_articles = mysqli_fetch_assoc($total_articles)["total"];

        $total_pages = ceil($total_articles / $page_size);

        DisplayArticleView($articles, $page_number, $total_pages);
    }
}

function DisplayArticleView($articles, $page_number, $total_pages) {
?>

<!DOCTYPE html>
<html lang="en">
    <?php
    $page_name = "Articles";
    include 'includes/header.php';
    ?>
    <body>
    <div class="page-title">
            <h1>Articles</h1>
            <p>Here you can view content created by our staff members/p>
        </div>
        <div class="list-container">
            <?php
            for ($i = 0; $i < mysqli_num_rows($articles); $i++) {
                $article = mysqli_fetch_assoc($articles);
                $article_id = $article["ID"];
                $article_title = $article["title"];
                $article_created_on = $article["created_on"];
                $article_last_updated = $article["last_updated"];
                $article_price = $article["price"];

                $article_price = floatval($article_price);
                $formatted_price = (String) "£" . number_format($article_price, 2);
            ?>

            <a class="list-option" href="?=article=<?= $article_id ?>">
                <div class="title">
                    <p class="title"><?= $article_title ?></p>
                    <div class="badges">
                    <?php
                        if ($article_price == 0) {
                            echo "<p class='badge small free'>Free</p>";
                        } else {
                            echo "<p class='badge small paid'>$formatted_price</p>";
                        }
                    ?>
                    </div>
                </div>
                <p class="created">Written on <?= $article_created_on ?></p>
                <p class="last-updated">Last Updated on <?= $article_last_updated ?></p>
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

function DisplayArticle($article_title, $article_content, $article_created_on, $article_last_updated) {
?>
<!DOCTYPE html>
<html lang="en">
    <?php
    $page_name = "Articles";
    include 'includes/header.php';
    ?>
    <body>
        <div class="page-title">
            <h1><?= $article_title ?></h1>
            <p>Written on <?= $article_created_on ?></p>
            <p>Last Updated on <?= $article_last_updated ?></p>
        </div>

        <div class="article-content">
            <?= $article_content ?>
        </div>
    </body>
</html>
<?php } ?>