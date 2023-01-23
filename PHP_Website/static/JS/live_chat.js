const socket = io('http://172.22.19.76:5000');

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

function sendMessage(chat, username="", message="") {
    valid = true;

    if (message.length <= 0) {
        valid = false;
    } else if (message.length > 255) {
        valid = false;
    }

    if (valid == true) {
        console.log("username: " + username + " message: " + message);
        socket.emit('message', {username: username, message: message});
        addMessageToChat(chat, username, message);

        return true;
    } else {
        return false;
    }
}

function setUpChatBox() {
    var chat = document.getElementById('chat');
    setup = true;
    if (chat) {
        var chatIcon = document.querySelector('.chat-icon');
        var chatWindow = document.querySelector('.chat-container');

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

            console.log('Clicked!');
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
                sendMessage(chat, "example", message);
            }
        });

    } else {
        console.warn('Chat not found!');
        setup = false;
    }

    return setup;
}



document.addEventListener("DOMContentLoaded", function(event) {
    setup = setUpChatBox();
    console.log(setup);
    if (setup) {
        socket.on('connect', function() {
            console.log('Connected!');
        });
    }
});

socket.on('message', function(data) {
    console.log(data);
    var chat = document.getElementById('chat');
    addMessageToChat(chat, data.username, data.message);
});