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
    <script defer src="./static/JS/chat_controller.js"></script>
</head>

<body>
    <?php

    $logged_in = $_SESSION["logged_in"] ?? false;
    $user = $_SESSION["user"] ?? null;

    if ($logged_in == true) {
        // Checks if the security cookie is set
        $created_cookie = false;
        if (isset($_COOKIE["security"])) {
            // Checks if the cookie is valid
            if ($_COOKIE["security"] != $user["private_key"]) {
                // If the cookie is invalid, the user is logged out for security purposes

                session_destroy();
                header("Location: login.php");
                exit("You have been logged out for security reasons. Please login and try again.");
            }
        } else {
            // If the cookie is not set, it is created
            // Create cookie

            $cookie_name = "security";
            $cookie_value = $user["private_key"];
            // Cookie expires in 1 day
            $cookie_expiration = time() + (86400 * 1);
            setcookie($cookie_name, $cookie_value, $cookie_expiration, "/");
            
            $created_cookie = true;
        }

        $account_type = $user["user_type"];

        switch ($account_type) {
            case "user":
                echo "<script defer src='./static/JS/user_live_chat.js'></script>";
                DisplayUserChat($user);
                break;
            case "admin":
                echo "<script defer src='./static/JS/staff_live_chat.js'></script>";
                DisplayStaffChat($user);
                break;
            default:
                echo "<div class='page-title'><h1>Error: Invalid account type</h1></div>";
                break;
        }

        if ($created_cookie) {
            $cookie_security = $user["private_key"];
        } else {
            $cookie_security = $_COOKIE["security"];
        }

        echo "<script>
        function getSecurityAuth() {
            return '" . $cookie_security . "';
        }
        </script>";
    }
    ?>
</body>

</html>
<?php function DisplayStaffChat($user)
{ ?>
    <div id="chat" class="chat">
        <div class="chat-container">
            <div class="chat-title">
                <button class="chat-close" onclick="exitChat()">⟶</button>
                <p>Available Chats</p>
                <button class="chat-exit" onclick="closeChat()">X</button>
            </div>
            <div class="chat-message-container">
                <button>
                    <p class="chat-title">100000</p>
                    <p class="chat-username">John</p>
                    <p class="chat-status">Open</p>
                </button>
            </div>
            <div class="chat-input" style="display:none;">
                <input type="text" placeholder="Type a message...">
            </div>
        </div>
        <div class="chat-icon">
        </div>
    </div>
<?php
}

function DisplayUserChat($user)
{
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
            <div class="chat-title">
                <button class="chat-close">
                    <!-- This is a placeholder (This centers the chat) -->
                </button>
                <p>Available Chats</p>
                <button class="chat-exit" onclick="closeChat()">X</button>
            </div>
            <div class="chat-message-container">
                <!-- This container holds all of the chat -->
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