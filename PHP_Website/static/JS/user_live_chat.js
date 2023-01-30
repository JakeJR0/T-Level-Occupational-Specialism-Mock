function sendMessage(chat, message="") {
    valid = true;

    if (message.length <= 0) {
        valid = false;
    } else if (message.length > 255) {
        valid = false;
    }

    if (valid != true) {
        return false;
    }


    var token = getSecurityAuth();

    // Sends message via socket
    socket.emit('message', {
        'message': message,
        'token': token
    });

}


function handleChatClosing() {
    var chat = document.getElementById('chat');
    if (!chat) {
        return false;
    }

    var chatInput = chat.querySelector('.chat-input');

    // Makes input box visible
    chatInput.style.display = "none";

    // Gets chat ID
    var chatID = chat.getAttribute('data-chat-id');

    // Leaves chat
    var token = getSecurityAuth();
    socket.emit('leave_chat', {
        'token': token,
        'chat_id': chatID
    });

    // Sets chat ID
    chat.setAttribute('data-chat-id', null);
}

function chatOpenEvent() {
    var chat = document.getElementById('chat');

    if (!chat) {
        return false;
    }

    var chatID = chat.getAttribute('data-chat-id');

    if (chatID == null || chatID == "null" || chatID == "" || chatID == "undefined") {
        // Removes all chat messages

        deleteChats();

        // Creates a new chat

        var token = getSecurityAuth();
        socket.emit('create_chat', {
            'token': token
        });

    }

}

function closeChat() {
    var token = getSecurityAuth();
    socket.emit('close_chat', {
        'token': token,
        'chat_id': chat.getAttribute('data-chat-id')
    });

    handleChatClosing();
}

function setupChat() {
    var chat = document.getElementById('chat');

    if (!chat) {
        console.log("Chat HTML was not found, disabling chat...");
        return false;
    }

    if (setup) {
        socket.on("connect", function() {
            socket.on("join", function(data) {
                console.log("Joined chat");
            })


            socket.emit('join_chat', {
                'token': getSecurityAuth()
            });

            socket.on("message", function(data) {
                try {
                    if (data["error_code"] == 1) {
                        console.log("Authentication error");

                        token = getSecurityAuth();
                        if (token == null || token == "null" || token == "" || token == "undefined" || token == undefined) {
                            // window.location.href = "./login.php";
                            console.warn("HERE");
                            return;

                        };
                        // alert("Error: Authentication failed, please login into the site.");

                        // var xml = new XMLHttpRequest();

                        // xml.open("POST", "./logout.php", true);

                        // xml.send();

                        function directToLogin(ms) {
                            setTimeout(function() {
                                window.location.href = "./login.php";
                            }, ms);
                        }

                        // xml.onload = function() {
                        //     if (xml.status == 200) {
                        //         directToLogin(1000);
                        //     } else {
                        //         window.location.reload();
                        //     }
                        // }

                        

                        return;
                    }
                    

                    if (data["type"] == "chat_history") {
                        return;
                    } else if (data["type"] == "close_chat") {
                        handleChatClosing();
                    } else if (data["type"] == "created_chat") {
                        chat.setAttribute('data-chat-id', data["chat_id"]);

                        var chatInput = chat.querySelector('.chat-input');
                        chatInput.style.display = "flex";
                    } else if (data["type"] == "server_message") {
                        console.log("Server message: " + data["message"]);
                        return;
                    }
                } catch (e) {
                    console.warn(e);
                }

                addMessageToChat(chat, data['username'], data['message']);
                
            })
        })
    }
}

document.addEventListener("DOMContentLoaded", function(event) {
    setupChat();
    events.chat_open = chatOpenEvent;
});
