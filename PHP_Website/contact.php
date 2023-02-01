<?php
$errors = array();
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once '../storage.php';

    // Sanitise data input

    $email = $_POST["email"];
    $first_name = $_POST["first_name"];
    $last_name = $_POST["last_name"];
    $message = $_POST["message"];

    $email = trim($email);
    $first_name = trim($first_name);
    $last_name = trim($last_name);
    $message = trim($message);

    $email = strip_tags($email);
    $first_name = strip_tags($first_name);
    $last_name = strip_tags($last_name);
    $message = strip_tags($message);

    $email = mysqli_real_escape_string($connection, $email);
    $first_name = mysqli_real_escape_string($connection, $first_name);
    $last_name = mysqli_real_escape_string($connection, $last_name);
    $message = mysqli_real_escape_string($connection, $message);

    // Validate data input
    $valid = true;

    if (strlen($email) < 12 || strlen($email) > 50) {
        $valid = false;
        $errors[] = "Email must be between 12 and 50 characters";
    }

    if (strlen($first_name) < 3 || strlen($first_name) > 20) {
        $valid = false;
        $errors[] = "First name must be between 3 and 20 characters";
    }

    if (strlen($last_name) < 4 || strlen($last_name) > 30) {
        $valid = false;
        $errors[] = "Last name must be between 4 and 30 characters";
    }

    if (strlen($message) < 50 || strlen($message) > 255) {
        $valid = false;
        $errors[] = "Message must be between 50 and 255 characters";
    }

    if ($valid) {
        // Create SQL query
        $sql = "
            INSERT INTO contact (email, first_name, last_name, message)
            VALUES ('$email', '$first_name', '$last_name', '$message')
        ";

        // Execute SQL query
        if (mysqli_query($connection, $sql)) {
            $success = true;
        } else {
            error_log($connection->error);
            $errors[] = "Failed to submit contact form";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<?php
$page_name = "Contact";
include 'includes/header.php';
?>

<body>
    <div class="centred-form-container">
        <div class="basic-form">
            <h1 aria-label="ToKa Fitness Contact Form">ToKa Fitness Contact Form</h1>
            <?php
            if ($success) {
                echo "<p aria-label='Thank you for your message' class='success'>Thank you for your message, we will get back to you as soon as possible.</p>";
            } else {
                echo '<div class="errors">';

                foreach ($errors as $error) {
                    echo "<p aria-label='$error'>$error</p>";
                }

                echo '</div>';
            }
            ?>
            <!-- Contact Form -->
            <form action="" method="POST">
                <input aria-label="Email" type="email" name="email" minlength="12" maxlength="50" id="email" placeholder="Email" required>
                <input type="text" aria-label="First Name" name="first_name" minlength="3" maxlength="20" id="first_name" placeholder="First Name" required>
                <input type="text" aria-label="Last Name" name="last_name" minlength="4" maxlength="30" id="last_name" placeholder="Last Name" required>
                <textarea type="text" aria-label="Message" name="message" minlength="50" maxlength="255" id="message" placeholder="Message" required></textarea>
                <button type="submit" name="submit" aria-label="Submit Button">Submit</button>
            </form>
        </div>
    </div>
    <?php
    $user = $_SESSION['user'] ?? null;
    $logged_in = $_SESSION['logged_in'] ?? false;
    if ($user && $logged_in == true) {

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

        if ($created_cookie) {
            $cookie_security = $user["private_key"];
        } else {
            $cookie_security = $_COOKIE["security"];
        }

        echo "<script>function getSecurityAuth() {
          return '" . $cookie_security . "';   
      }</script>";

        echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.0.1/socket.io.js" integrity="sha512-q/dWJ3kcmjBLU4Qc47E4A9kTB4m3wuTY7vkFJDTZKjTs8jhyGQnaUrxa0Ytd0ssMZhbNua9hE+E7Qv1j+DyZwA==" crossorigin="anonymous"></script>';
        echo "<script src='./static/JS/chat_controller.js'></script>";
        if ($user["user_type"] == "admin") {
    ?>
            <script src="./static/JS/staff_live_chat.js"></script>
            <div id="chat" class="chat">
                <div class="chat-container">
                    <div class="chat-title">
                        <button class="chat-close" onclick="exitChat()">⟶</button>
                        <p>Available Chats</p>
                        <button class="chat-exit" onclick="closeChat()">X</button>
                    </div>
                    <div class="chat-message-container">
                    </div>
                    <div class="chat-input" style="display:none;">
                        <input type="text" placeholder="Type a message...">
                    </div>
                </div>
                <div class="chat-icon">
                </div>
            </div>
        <?php
        } else {
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
            <script src="./static/JS/user_live_chat.js"></script>
            <div id="chat" class="chat" data-chat-id="<?= $chat_id ?>">
                <div class="chat-container">
                    <div class="chat-title">
                        <button class="chat-close">
                            <!-- This is a placeholder (This centers the chat) -->
                        </button>
                        <p>Support Chat</p>
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
    <?php
        }
    } ?>
</body>

</html>