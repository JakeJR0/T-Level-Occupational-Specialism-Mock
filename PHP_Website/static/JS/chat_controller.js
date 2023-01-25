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



function setupChatBox() {
    var chat = document.getElementById('chat');
    setup = true;
    if (chat) {
        var chatIcon = document.querySelector('.chat-icon');
        var chatWindow = document.querySelector('.user-chat-container');
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

document.addEventListener('DOMContentLoaded', function() {
    setupChatBox();
});