const socket = io('http://172.22.19.76:5000');

function getSecurityAuth() {
    var cookie = document.cookie;
    // Gets the security part of the cookie
    var security = cookie.split(";")
    var token = "";
    for (var i = 0; i < security.length; i++) {
        if (security[i].startsWith(" security=")) {
            token = security[i];
            break;
        }
    }

    token = token.split("=")[1].trim();
    return token;
}

function addMessageToChat(chat, username, message) {
    var messageContainer = chat.querySelector(".chat-message-container");
    var messageElement = document.createElement("div");
    messageElement.classList.add("chat-message");

    var messageUsername = document.createElement("p");
    messageUsername.classList.add("author");
    messageUsername.innerHTML = username + ":";

    var messageText = document.createElement("p");
    messageText.classList.add("message");
    messageText.innerHTML = message;


    messageElement.appendChild(messageUsername);
    messageElement.appendChild(messageText);
    messageContainer.appendChild(messageElement);

    messageContainer.scrollTop = messageContainer.scrollHeight;
}

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

function setupChatBox(chat) {
    setup = true;
    if (chat) {
        var chatIcon = document.querySelector('.chat-icon');
        var chatWindow = document.querySelector('.chat-container');
        var messageContainer = chat.querySelector(".chat-message-container");
        messageContainer.scrollTop = messageContainer.scrollHeight;

        chatIcon.addEventListener('click', function() {
            // If open, close
            if (chat.classList.contains('open')) {
                chat.classList.remove('open');
                chat.classList.add('closed');
            } else {
                try {
                    chat.classList.remove('closed');
                } catch (e) {};

                chat.classList.add('open');
            }
        });

        const ChatInput = document.querySelector('.chat-input input');

        ChatInput.addEventListener('keypress', function(e) {
            if (e.keyCode == 13) {
                valid = true;
                if (ChatInput.value.length <= 0) {
                    valid = false;
                } else if (ChatInput.value.length > 255) {
                    valid = false;
                }

                if (valid == false) {

                    if (!ChatInput.classList.contains('invalid')) {
                        ChatInput.classList.add('invalid');

                        setTimeout(function() {
                            ChatInput.classList.remove('invalid');
                        }, 1000);
                    }

                    return;
                }

                var message = ChatInput.value;
                ChatInput.value = '';
                sendMessage(chat, message);
            }
        });

    } else {
        console.warn('Chat not found!');
        setup = false;
    }

    return setup;
}

function setupChat() {
    var chat = document.getElementById('chat');

    if (!chat) {
        console.log("Chat HTML was not found, disabling chat...");
        return false;
    }

    var chatID = chat.getAttribute('data-chat-id');
    if (!chatID || chatID == null) {
        socket.emit('create_chat', {
            'token': getSecurityAuth()
        });

        console.log(socket.emit());

        console.log("Chat ID was not found, creating new chat...")
    } else {
        console.log("Chat ID found, connecting to chat...");
    }

    setup = setupChatBox(chat);
    console.log(setup);
    if (setup) {
        socket.on("connect", function() {
            console.log(socket);
            socket.on("join", function(data) {
                console.log("Joined chat");
                console.log(data);
            })


            socket.emit('join_chat', {
                'token': getSecurityAuth()
            });

            socket.on("message", function(data) {
                console.log("Client ID: " + socket.id);
                console.log(data);
            })
        })
    }
}

document.addEventListener("DOMContentLoaded", function(event) {
    setupChat();
});
