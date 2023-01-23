<!DOCTYPE html>
<html lang="en">
    <?php
    $page_name = "Chat";
    include 'includes/header.php';
    ?>
    <head>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.0.1/socket.io.js" integrity="sha512-q/dWJ3kcmjBLU4Qc47E4A9kTB4m3wuTY7vkFJDTZKjTs8jhyGQnaUrxa0Ytd0ssMZhbNua9hE+E7Qv1j+DyZwA==" crossorigin="anonymous"></script>
        <script defer src="./static/JS/live_chat.js"></script>
    </head>

    <body>
        <?php

            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }

            $logged_in = $_SESSION["logged_in"] ?? false;
            $user = $_SESSION["user"] ?? null;

            if ($logged_in == true) {
                // Checks if the security cookie is set
                if (isset($_COOKIE["security"])) {
                    // Checks if the cookie is valid
                    if ($_COOKIE["security"] == $user["private_key"]) {
                        return;
                    } else {
                        // If the cookie is invalid, the user is logged out for security purposes
                        session_destroy();
                        header("Location: login.php");
                        exit("You have been logged out for security reasons. Please login and try again.");
                    }
                } else {
                    // Creates a new security cookie that lasts an hour
                    setcookie("security", $user["private_key"], time() + 3600, "/");
                }

            // TODO: Fix the chat not showing up when the user is logged in
        ?>

        <div id="chat" class="chat">
            <div class="chat-container">
                <p class="chat-title">
                    Chat
                </p>
                <div class="chat-message-container">
                </div>
                <div class="chat-input">
                    <input type="text" placeholder="Type a message...">
                </div>
            </div>
            <div class="chat-icon">
            </div>
        </div>
        <?php } ?>
    </body>
</html>