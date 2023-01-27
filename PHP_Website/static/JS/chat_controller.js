const socket = io('http://172.22.18.4:5000');

var events = {
    "chat_open": null,
    "chat_close": null,
    "force_close": closeChatElement
}

function closeChatElement() {
    var chat = document.getElementById('chat');
    if (!chat) {
        return false;
    }

    if (chat.classList.contains("open")) {
        chat.classList.remove("open");
        chat.classList.add("closed");
    }
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

function deleteChats() {
    var chat = document.getElementById('chat');
    if (!chat) {
        return false;
    }

    var chatContainer = chat.querySelector('.chat-container');
    var chatMessageContainer = chat.querySelector('.chat-message-container');

    var chatElements = chatMessageContainer.querySelectorAll('button');
    for (var i = 0; i < chatElements.length; i++) {
        var chatElement = chatElements[i];
        chatMessageContainer.removeChild(chatElement);
    }

    var chatMessages = chatContainer.querySelectorAll('.chat-message');
    for (var i = 0; i < chatMessages.length; i++) {
        var chatMessage = chatMessages[i];
        chatMessageContainer.removeChild(chatMessage);
    }
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

                if (events["chat_close"] != null) {
                    events["chat_close"]();
                }

            } else {
                try {
                    chat.classList.remove('closed');
                } catch (e) {};

                chat.classList.add('open');

                if (events["chat_open"] != null) {
                    events["chat_open"]();
                }

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