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
                
                addMessageToChat(chat, data['username'], data['message']);
                console.log("Received message from Chat API, from " + data['username'] + ":" + data['message']);
            })
        })
    }
}

document.addEventListener("DOMContentLoaded", function(event) {
    setupChat();
});
