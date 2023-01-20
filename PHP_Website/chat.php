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
        <div class="chat">
            <div class="chat-container">
                <p class="chat-title">
                    Chat
                </p>
                <div class="chat-message-container">
                    <div class="chat-message">
                        <p class="author">
                            Author
                        </p>
                        <p class="message">
                            Hello, World!
                        </p>
                    </div>
                </div>
                <div class="chat-input">
                    <input type="text" placeholder="Type a message...">
                </div>
            </div>
            <div class="chat-icon">
            </div>
        </div>
    </body>
</html>