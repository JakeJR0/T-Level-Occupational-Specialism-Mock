<!DOCTYPE html>
<html lang="en">
    <?php
    $page_name = "Chat";
    include 'includes/header.php';

    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    ?>
    <head>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.0.1/socket.io.js" integrity="sha512-q/dWJ3kcmjBLU4Qc47E4A9kTB4m3wuTY7vkFJDTZKjTs8jhyGQnaUrxa0Ytd0ssMZhbNua9hE+E7Qv1j+DyZwA==" crossorigin="anonymous"></script>
        <script defer src="./static/JS/live_chat.js"></script>
    </head>
    <body>
        <?php


            $logged_in = $_SESSION["logged_in"] ?? false;
            $user = $_SESSION["user"] ?? null;

            if ($logged_in == true) {
                // Checks if the security cookie is set
                if (isset($_COOKIE["security"])) {
                    // Checks if the cookie is valid
                    if ($_COOKIE["security"] != $user["private_key"]) {
                        // If the cookie is invalid, the user is logged out for security purposes
                        session_destroy();
                        header("Location: login.php");
                        exit("You have been logged out for security reasons. Please login and try again.");
                    }
                } else {
                    // Creates a new security cookie that lasts an hour
                    setcookie("security", $user["private_key"], time() + 3600, "/");
                }

            require_once '../storage.php';

            // Checks if the user has an active chat session
            $user_id = $user["ID"];

            $chat_sql = "
                SELECT
                    ID,
                    status
                FROM support_chat
                WHERE user_id = '$user_id'
                ORDER BY ID DESC
            ";

            $chat_result = mysqli_query($connection, $chat_sql);

            $chat_result = mysqli_fetch_assoc($chat_result);

            if (!$chat_result) {
                $chat_id = null;
            } else {
                if ($chat_result["status"] == "open") {
                    $chat_id = $chat_result["ID"];
                } else {
                    $chat_id = null;
                }
            }


            if ($chat_id != null) {
                $chat_messages = "
                    SELECT
                        users.ID,
                        users.first_name,
                        support_chat_messages.message
                    FROM
                        support_chat_messages
                    INNER JOIN
                        users
                    ON
                        support_chat_messages.user_id = users.ID
                    WHERE
                        chat_id = '$chat_id'
                    ORDER BY
                        support_chat_messages.sent_on ASC
                ";

                $chat_messages = mysqli_query($connection, $chat_messages);

            }

        ?>

        <div id="chat" class="chat" data-chat-id="<?= $chat_id ?>">
            <div class="chat-container">
                <p class="chat-title">
                    Chat
                </p>
                <div class="chat-message-container">
                    <?php
                        // Loops through all the messages in the chat
                        // and displays them

                        if ($chat_id != null) {
                            while ($chat_message = mysqli_fetch_assoc($chat_messages)) {
                                $first_name = $chat_message["first_name"];
                                $message = $chat_message["message"];

                                if ($chat_message["ID"] == $user["ID"]) {
                                    $first_name = "You:";
                                    $message_class = "chat-message";
                                } else {
                                    $message_class = "chat-message";
                                }

                                echo "
                                    <div class='$message_class'>
                                        <p class='author'>
                                            $first_name
                                        </p>
                                        <p class='message'>
                                            $message
                                        </p>
                                    </div>
                                ";
                            }
                        }
                    ?>
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