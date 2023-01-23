from flask import Flask, request
from flask_socketio import SocketIO
from flask_socketio import join_room, leave_room, send
from flask_mysqldb import MySQL
import gevent


app = Flask(__name__)
app.config['SECRET_KEY'] = 'secret!'
app.config["MYSQL_HOST"] = "localhost"
app.config["MYSQL_USER"] = "root"
app.config["MYSQL_PASSWORD"] = ""
app.config["MYSQL_DB"] = "occupational_specialism_mock"
app.config["MYSQL_CURSORCLASS"] = "DictCursor"

# Middleware

mysql = MySQL(app)
socketio = SocketIO(app, cors_allowed_origins="*")

# TODO: Fix message sending
# TODO: Allow users to join rooms


def security_to_user(security_key: int) -> int:
    """
        This function takes in the private key from
        the front end and converts it into a user ID,
        this is to ensure that the user is sending the
        requests to the api.
    """

    cursor = mysql.connection.cursor()
    cursor.execute("SELECT ID FROM users WHERE private_key = %s", (security_key,))
    user_id = cursor.fetchone()["ID"]

    return user_id

def get_current_chat_id(user_id: int) -> int:
    """
        This function gets the current chat ID
        for the user.
    """

    cursor = mysql.connection.cursor()
    cursor.execute("SELECT ID FROM support_chat WHERE user_id = %s ORDER BY ID DESC", (user_id,))
    chat_id = cursor.fetchone()["ID"]

    return chat_id

def add_message_to_chat(chat_id: int, user_id: int, message: str) -> None:
    """
        This function saves a message to the database.
    """

    cursor = mysql.connection.cursor()
    cursor.execute("INSERT INTO support_chat_messages(chat_id, user_id, message) VALUES(%s, %s, %s)", (chat_id, user_id, message))
    # Commits changes
    mysql.connection.commit()


# SocketIO events

@socketio.on("connect")
def handle_connect():
    print("Client connected, Client ID: ", request.sid)
    send({"message": "You have connected to the server", "client_id": request.sid})

@socketio.on("message")
def handle_message(data):
    security_key: int = data["token"]
    message: str = data["message"]

    if len(message) > 255:
        return socketio.emit("message", {"message": "Message is too long"})
    elif len(message) < 1:
        return socketio.emit("message", {"message": "Message is too short"})

    # Gets the user ID
    user_id = security_to_user(security_key)
    # Gets the chat ID
    chat_id = get_current_chat_id(user_id)

    add_message_to_chat(chat_id, user_id, message)

    # Sends message to every user in room

    socketio.emit("message", {"message": message}, room=str(chat_id))

@socketio.on("create_chat")
def handle_create_chat(data):
    user_token = data["token"]
    print(data)
    user_id = security_to_user(user_token)

    cursor = mysql.connection.cursor()
    # Creates a new chat
    cursor.execute("INSERT INTO support_chat (user_id) VALUES (%s)", (user_id,))
    # Gets the chat ID
    chat_id = cursor.lastrowid
    # Commits the changes
    mysql.connection.commit()

    print("user ID: ", user_id)
    print("chat ID: ", chat_id)
    #the user with the given session ID. If this parameter is not included
    print("Session ID: ", request.sid)
    # Send message to the user
    socketio.emit("message", {"message": "Chat created", "chat_id": chat_id}, room=request.sid)

@socketio.on("join_chat")
def handle_join_chat(data):
    user_token = data["token"]
    user_id = security_to_user(user_token)

    # Gets the chat ID
    cursor = mysql.connection.cursor()
    chat_sql = """
        SELECT ID, status
        FROM support_chat
        WHERE user_id = %s
        ORDER BY ID DESC
    """

    cursor.execute(chat_sql, (user_id,))
    found = False
    chat_id: int = 0

    while not found:
        if cursor.rowcount == 0:
            break

        result = cursor.fetchone()
        chat_id = result["ID"]
        status = result["status"]
        if status == "open":
            found = True

    if found:
        join_room(str(chat_id))
        print("Joined room: ", str(chat_id))
        socketio.emit("join", {"message": "You have joined the chat", "chat_id": chat_id})
        # Q: How do I use sid to send a message to the user?

    else:
        socketio.emit("join", {"message": "No chat found"})

@socketio.on("leave_chat")
def handle_leave_chat(data):
    chat = data["chat_id"]
    leave_room(str(chat), sid=request.sid)

@socketio.on("close_chat")
def handle_close_chat(data):
    chat_id = data["chat_id"]

    cursor = mysql.connection.cursor()
    cursor.execute("UPDATE support_chat SET status = 'closed' WHERE ID = %s", (chat_id,))
    mysql.connection.commit()

    socketio.emit("message", {"message": "Chat closed"}, room=str(chat_id))

if __name__ == "__main__":
    print("Starting server")
    app.run(host="0.0.0.0")