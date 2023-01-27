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
        "chat_id": chat.getAttribute('data-chat-id'),
        'token': token
    });

}

function DisplayChatRoom(chatID) {
    deleteChats();

    var chat = document.getElementById('chat');
    if (!chat) {
        return false;
    }

    // Makes input box visible
    var chatInput = chat.querySelector('.chat-input');
    chatInput.style.display = "flex";

    // Request chat messages
    var token = getSecurityAuth();
    socket.emit('get_chat_messages', {
        'chat_id': chatID,
        'token': token
    });


    // Sets chat ID
    chat.setAttribute('data-chat-id', chatID);
}

function AddAvailableChat(chat_id, chat_username) {
    var chat = document.getElementById('chat');

    if (!chat) {
        return false;
    }

    var chatContainer = chat.querySelector('.chat-container');
    var chatMessageContainer = chat.querySelector('.chat-message-container');

    var chatElement = document.createElement('button');
    var chatTitle = document.createElement('p');
    var chatUsername = document.createElement('p');
    var chatStatus = document.createElement('p');

    chatTitle.classList.add('chat-title');
    chatUsername.classList.add('chat-username');
    chatStatus.classList.add('chat-status');

    chatTitle.innerHTML = chat_id;
    chatUsername.innerHTML = chat_username;
    chatStatus.innerHTML = "Open";

    chatElement.appendChild(chatTitle);
    chatElement.appendChild(chatUsername);
    chatElement.appendChild(chatStatus);

    chatElement.setAttribute("chat-id", chat_id);

    chatElement.addEventListener('click', function() {
        var chat_id = this.getAttribute('chat-id');
        var token = getSecurityAuth();

        socket.emit('join_chat', {
            'chat_id': chat_id,
            'token': token
        });

        DisplayChatRoom(chat_id);
    });

    chatMessageContainer.appendChild(chatElement);

    return true;
}

function removeChat(chat_id) {
    var chat = document.getElementById('chat');
    if (!chat) {
        return false;
    }

    var chatContainer = chat.querySelector('.chat-container');
    var chatMessageContainer = chat.querySelector('.chat-message-container');

    var chatElements = chatMessageContainer.querySelectorAll('button');
    for (var i = 0; i < chatElements.length; i++) {
        var chatElement = chatElements[i];
        var chatTitle = chatElement.querySelector('.chat-title');

        if (chatTitle.innerHTML == chat_id) {
            chatMessageContainer.removeChild(chatElement);
        }
    }
}

function changeChatStatus(chat_id, status) {
    var chat = document.getElementById('chat');
    if (!chat) {
        return false;
    }

    var chatContainer = chat.querySelector('.chat-container');
    var chatMessageContainer = chat.querySelector('.chat-message-container');

    var chatElements = chatMessageContainer.querySelectorAll('button');
    for (var i = 0; i < chatElements.length; i++) {
        var chatElement = chatElements[i];
        var chatTitle = chatElement.querySelector('.chat-title');

        if (chatTitle.innerHTML == chat_id) {
            var chatStatus = chatElement.querySelector('.chat-status');
            chatStatus.innerHTML = chat_status;
        }
    }
}

function loadAvailableChats() {
    var token = getSecurityAuth();

    socket.emit('get_available_chats', {
        'token': token
    });
}


function joinStaffRoom() {
    var token = getSecurityAuth();

    socket.emit('join_staff', {
        'token': token
    });

}

function requestChats() {
    var token = getSecurityAuth();

    socket.emit('request_chats', {
        'token': token
    });
}

function addChats(chats) {
    for (var i = 0; i < chats.length; i++) {
        var chat = chats[i];
        var chat_id = chat["ID"] || null;
        var chat_username = chat["username"] || null;
        if (chat_id != null && chat_username != null) {
            AddAvailableChat(chat_id, chat_username);
        }
    }
}

function exitChat() {
    var chat = document.getElementById('chat');

    if (!chat) {
        return false;
    }

    var chatContainer = chat.querySelector('.chat-container');
    var chatMessageContainer = chat.querySelector('.chat-message-container');
    var chatInput = chat.querySelector('.chat-input');
    var chatBack = chat.querySelector('.chat-close');
    var chatClose = chat.querySelector('.chat-exit');

    chatInput.style.display = "none";
    chatBack.style.display = "none";
    chatClose.style.display = "none";

    // Gets chat ID
    var chat_id = chat.getAttribute('data-chat-id');

    // Removes chat ID
    chat.setAttribute('data-chat-id', null);

    // Leaves chat room
    var token = getSecurityAuth();
    socket.emit('leave_chat', {
        'chat_id': chat_id,
        'token': token
    });

    deleteChats();
    requestChats();
}

function closeChat() {
    var chat = document.getElementById('chat');

    if (!chat) {
        return false;
    }

    var chat_id = chat.getAttribute('data-chat-id');

    if (chat_id == null) {
        console.warn("Chat ID is null");
        return false;
    }

    socket.emit('close_chat', {
        'chat_id': chat.getAttribute('data-chat-id'),
        'token': getSecurityAuth()
    });

    exitChat();
}

function Chat() {
    socket.on("message", function(data) {
        var type = data.type || "message";
        var message = data.message || "";
        var username = data.username || "Server";

        if (type == "chat_created") {
            var chat_id = data.chat_id || null;
            var chat_username = data.chat_username || null;

            if (chat_id != null && chat_username != null) {
                AddAvailableChat(chat_id, chat_username);
            }
        } else if (type == "chat_closed") {
            var chat_id = data.chat_id || null;

            if (chat_id != null) {
                removeChat(chat_id);
            }
        } else if (type == "chat_change_status") {
            var chat_id = data.chat_id || null;
            var chat_status = data.chat_status || null;

            if (chat_id != null && chat_status != null) {
                changeChatStatus(chat_id, chat_status);
            }
        } else if (type == "chats") {
            // This Event is when the user goes to the available chats page or when the user refreshes the page
            var chat = document.getElementById('chat');
            var chatInput = chat.querySelector('.chat-input');
            var chatBack = chat.querySelector('.chat-close');
            var chatExit = chat.querySelector('.chat-exit');

            chatInput.style.display = "none";
            chatBack.style.display = "none";
            chatExit.style.display = "none";

            deleteChats();
            // Add all chats

            var chats = data["chats"] || null;

            if (chats != null) {
                addChats(chats);
            }
        } else if (type == "chat_history") {
            var chat = document.getElementById('chat');
            if (!chat) {
                return false;
            }

            var messages = data["message"] || null;

            for ($i = 0; $i < messages.length; $i++) {
                var message = messages[$i];
                var username = message["first_name"] || null;
                var message = message["message"] || null;

                if (username != null && message != null) {
                    addMessageToChat(chat, username, message);
                }
            }

            var chatInput = chat.querySelector('.chat-input');
            var chatBack = chat.querySelector('.chat-close');
            var chatExit = chat.querySelector('.chat-exit');
            chatInput.style.display = "flex";
            chatBack.style.display = "flex";
            chatExit.style.display = "flex";

            var Chat = document.getElementById('chat');
            Chat.scrollTop = Chat.scrollHeight;

        } else if (type == "chat_message") {
            var chat = document.getElementById('chat');
            if (!chat) {
                return false;
            }

            var chatContainer = chat.querySelector('.chat-container');
            var chatMessageContainer = chat.querySelector('.chat-message-container');


            var username = data["username"] || null;
            var message = data["message"] || null;

            if (username != null && message != null) {
                addMessageToChat(chat, username, message);
            }
        } else if (type == "server_message") {
            console.log("Server Message: " + message);
        }
    });


    joinStaffRoom();
    requestChats();
}

document.addEventListener("DOMContentLoaded", function(event) {
    Chat();
    console.log("Loaded Staff Live Chat");
});