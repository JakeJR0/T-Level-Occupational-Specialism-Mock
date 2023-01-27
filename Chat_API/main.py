"""
This is the main file for the chat API.

Currently this file is used to allow users to send messages to the chat server
and for admins to view and respond to the messages in real time.

The Chat API is built using Flask and SocketIO and connects to the MySQL database,
this is to allow the chat to be persistent and it allows the past messages to be
viewed by the database admins (Allowing staff to view past chats could be a future feature).

"""

from flask import Flask, request
from flask_socketio import SocketIO
from flask_socketio import ConnectionRefusedError
from flask_socketio import join_room, leave_room, send
from flask_mysqldb import MySQL
import gevent

# Create the Flask app
app = Flask(__name__)

# Secret key is used to secure the session cookie

app.config["SECRET_KEY"] = "secret!"

# MySQL configurations

app.config["MYSQL_HOST"] = "localhost"
app.config["MYSQL_USER"] = "root"
app.config["MYSQL_PASSWORD"] = ""
app.config["MYSQL_DB"] = "occupational_specialism_mock"
app.config["MYSQL_CURSORCLASS"] = "DictCursor"

# Middleware

mysql = MySQL(app)
socketio = SocketIO(app, cors_allowed_origins="*", logger=True, engineio_logger=True)


def security_to_user(security_key: int) -> int:
    """
    This function takes in the private key from
    the front end and converts it into a user ID,
    this is to ensure that the user is sending the
    requests to the api.
    """

    cursor = mysql.connection.cursor()
    print(security_key)

    cursor.execute("SELECT ID FROM users WHERE private_key = %s", (security_key,))

    user_id = cursor.fetchone()["ID"]

    return user_id


def user_id_to_first_name(user_id: int) -> str:
    """
    This function takes in a user ID and returns
    the users first name.
    """

    cursor = mysql.connection.cursor()
    cursor.execute("SELECT first_name FROM users WHERE ID = %s", (user_id,))
    first_name = cursor.fetchone()["first_name"]

    return first_name


def user_is_staff(user_id: int) -> bool:
    """
    This function checks if the user is a staff member.
    """

    cursor = mysql.connection.cursor()
    cursor.execute("SELECT user_type FROM users WHERE ID = %s", (user_id,))

    user_type = cursor.fetchone()["user_type"]

    is_staff = False

    if user_type == "staff":
        is_staff = True
    elif user_type == "admin":
        is_staff = True

    return is_staff


def get_current_chat_id(user_id: int) -> int:
    """
    This function gets the current chat ID
    for the user.
    """

    cursor = mysql.connection.cursor()
    cursor.execute(
        "SELECT ID FROM support_chat WHERE user_id = %s ORDER BY ID DESC", (user_id,)
    )
    chat_id = cursor.fetchone()["ID"]

    return chat_id

def user_owns_chat_id(user_id: int, chat_id: int) -> bool:
    """
    This function checks if the user owns the chat ID provided.
    """

    cursor = mysql.connection.cursor()
    cursor.execute(
        "SELECT ID FROM support_chat WHERE user_id = %s AND ID = %s", (user_id, chat_id)
    )

    chat_id = cursor.fetchone()

    if chat_id is None:
        return False
    else:
        return True

def add_message_to_chat(chat_id: int, user_id: int, message: str) -> None:
    """
    This function saves a message to the database.
    """

    cursor = mysql.connection.cursor()
    cursor.execute(
        "INSERT INTO support_chat_messages(chat_id, user_id, message) VALUES(%s, %s, %s)",
        (chat_id, user_id, message),
    )
    # Commits changes
    mysql.connection.commit()


# SocketIO events


@socketio.on("connect")
def handle_connect():
    """
        This function handles the connection event.
    """

    print("Client connected, Client ID: ", request.sid)
    send(
        {
            "message": "You have connected to the chat server",
            "client_id": request.sid,
            "user_id": 0,
            "username": "Server",
        }
    )


@socketio.on("disconnect")
def handle_disconnect():
    """
        This function handles the disconnect event.
    """
    print("Client disconnected, Client ID: ", request.sid)
    send(
        {
            "message": "You have disconnected from the chat server",
            "client_id": request.sid,
            "user_id": 0,
            "username": "Server",
        }
    )


@socketio.on("message")
def handle_message(data):
    """
        This function handles the message event and controls what happens when a message is sent.

        This function is used to send messages to the chat server and to the users additionally this
        function is used to send messages to the database which can be viewed by the database admins.
    """

    security_key: int = data["token"]
    message: str = data["message"]

    if len(message) > 255:
        return socketio.emit("message", {"message": "Message is too long"})
    elif len(message) < 1:
        return socketio.emit("message", {"message": "Message is too short"})

    # Gets the user ID
    user_id = security_to_user(security_key)

    if user_is_staff(user_id):
        chat_id: int = data["chat_id"]
    else:
        # Gets the chat ID
        chat_id = get_current_chat_id(user_id)

    print("Chat ID: R ", chat_id)
    add_message_to_chat(chat_id, user_id, message)

    # Gets the users first name
    first_name = user_id_to_first_name(user_id)

    # Sends message to every user in room
    socketio.emit(
        "message",
        {
            "message": message,
            "user_id": user_id,
            "username": first_name,
            "chat_id": chat_id,
            "type": "chat_message",
        },
        room=str(chat_id),
        include_self=True,
    )


@socketio.on("get_chat_messages")
def handle_get_chat_history(data):
    """
        This function / event is primarily used for admins as they cannot load all of the users chats
        and messages at once, this function is used to get the messages for a specific chat.

        Additionally, this verifies that the user is a staff member in order to prevent users from
        accessing the chat history of other users.
    """


    user_token = data["token"]
    chat_id = data["chat_id"]
    user_id = security_to_user(user_token)
    staff = user_is_staff(user_id)

    if staff:
        cursor = mysql.connection.cursor()
        chat_sql = """
            SELECT users.first_name, support_chat_messages.message
            FROM support_chat_messages
            INNER JOIN users ON support_chat_messages.user_id = users.ID
            WHERE chat_id = %s
            ORDER BY support_chat_messages.ID ASC
        """

        cursor.execute(
            chat_sql,
            (chat_id,),
        )
        messages = cursor.fetchall()
        socketio.emit(
            "message",
            {"message": messages, "type": "chat_history", "chat_id": chat_id},
        )

    else:
        socketio.emit(
            "message",
            {
                "message": "You are not allowed to view this chat",
                "type": "chat_history",
                "chat_id": chat_id,
            },
        )


@socketio.on("create_chat")
def handle_create_chat(data):
    """
        This function handles the create chat event and creates a new chat for the user.
    """
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
    # the user with the given session ID. If this parameter is not included
    print("Session ID: ", request.sid)
    # Send message to the user
    socketio.emit(
        "message", {"message": f"You started chat with ID: #{chat_id}", "type": "created_chat", "username": "Server", "chat_id": chat_id}, room=request.sid
    )

    user_username = user_id_to_first_name(user_id)

    # Adds the user to the room
    join_room(str(chat_id))

    # Sends the message to the staff
    socketio.emit(
        "message",
        {
            "message": f"User with ID: #{user_id} has started a new chat with ID: #{chat_id}",
            "type": "chat_created",
            "chat_id": chat_id,
            "chat_username": user_username
        },
        room="staff",
    )


@socketio.on("join_staff")
def handle_join_staff(data):
    """
        This function handles the join staff event and joins the user to the staff room.

        The staff room is used to send messages to all of the staff members at once such as
        when a new chat is created.
    """
    token = data["token"]
    user_id = security_to_user(token)

    # Checks if the user is staff
    valid = user_is_staff(user_id)

    if valid:
        join_room("staff")
        socketio.emit(
            "message",
            {"message": "You have joined the staff room", "type":"server_message", "user_id": user_id},
            room=request.sid,
        )


@socketio.on("request_chats")
def handle_request_chats(data):
    """
        This function is only used by staff members and is used to get all of the open chats.

        This is used to display the chats in the staff panel.
    """

    token = data["token"]
    user_id = security_to_user(token)

    # Checks if the user is staff
    valid = user_is_staff(user_id)

    if valid:
        cursor = mysql.connection.cursor()
        chats_sql = """
            SELECT support_chat.ID, users.first_name AS username, support_chat.user_id, support_chat.status
            FROM support_chat
            INNER JOIN users ON support_chat.user_id = users.ID
            WHERE status = 'open'
            ORDER BY ID DESC
        """
        cursor.execute(chats_sql)

        chats = cursor.fetchall()

        socketio.emit(
            "message",
            {"message": "Chats found", "type": "chats", "chats": chats},
            room=request.sid,
        )


@socketio.on("join_chat")
def handle_join_chat(data):
    """
        This function handles the join chat event and joins the user to the chat room.

        The chat room is used to send messages to all of the users in the chat at once.

        Additionally, this verifies that the user has access to the chat in order to prevent users from
        accessing the chat history of other users.
    """
    user_token = data["token"]
    user_id = security_to_user(user_token)

    staff = user_is_staff(user_id)

    if not staff:
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
            socketio.emit(
                "join", {"message": "You have joined the chat", "chat_id": chat_id}
            )
        else:
            socketio.emit("join", {"message": "No chat found"})
    else:
        chat_id = data["chat_id"]
        username = user_id_to_first_name(user_id)
        # Sends a message that the staff has joined the chat
        socketio.emit(
            "message",
            {
                "message": f"Staff Member {username} has joined the chat",
                "type": "message",
                "username": "Server"
            },
            room=str(chat_id),
        )

        join_room(str(chat_id))


@socketio.on("leave_chat")
def handle_leave_chat(data):
    chat = data["chat_id"]
    user_token = data["token"]
    user_id = security_to_user(user_token)

    staff = user_is_staff(user_id)

    leave_room(str(chat), sid=request.sid)

    socketio.emit(
        "message",
        {"message": "You have left the chat", "type": "server_message", "username": "Server"},
        room=request.sid,
    )

    # Sends a message to the room that the user has left the chat

    if staff:
        username = user_id_to_first_name(user_id)
        socketio.emit(
            "message",
            {
                "message": f"Staff Member {username} has left the chat",
                "type": "message",
                "username": "Server"
            },
            room=str(chat),
        )
    else:
        socketio.emit(
            "message",
            {
                "message": f"User with ID: #{user_id} has left the chat",
                "type": "message",
                "username": "Server"
            },
            room=str(chat),
        )


@socketio.on("close_chat")
def handle_close_chat(data):
    chat_id = data["chat_id"]
    user_token = data["token"]
    user_id = security_to_user(user_token)

    staff = user_is_staff(user_id)
    user_chat = user_owns_chat_id(user_id, chat_id)

    has_access = False

    if staff:
        has_access = True
    elif user_chat:
        has_access = True

    if not has_access:
        socketio.emit(
            "message",
            {"message": "You do not have permission to close this chat", "type": "error"},
            room=request.sid,
        )
        return


    cursor = mysql.connection.cursor()
    cursor.execute(
        "UPDATE support_chat SET status = 'closed' WHERE ID = %s", (chat_id,)
    )
    mysql.connection.commit()

    if staff:
        first_name = user_id_to_first_name(user_id)
        # Sends message to the user that the chat has been closed
        socketio.emit(
            "message",
            {
                "message": f"Chat closed by Staff Member {first_name}",
                "type": "close_chat",
                "chat_id": chat_id,
                "user_id": user_id,
                "username": "Server"
            },
            room=str(chat_id),
        )

        # Sends message to the staff that the chat has been closed
        socketio.emit(
            "message",
            {
                "message": "Chat closed",
                "type": "close_chat",
                "chat_id": chat_id,
                "user_id": user_id
            },
            room="staff",
        )

    else:
        # Sends message to the user that the chat has been closed
        socketio.emit(
            "message",
            {"message": "Chat closed", "type": "close_chat", "chat_id": chat_id, "username": "Server"},
            room=str(chat_id),
        )

        # Sends message to the staff that the chat has been closed
        socketio.emit(
            "message",
            {"message": "Chat closed by user", "type": "close_chat", "chat_id": chat_id},
            room="staff",
        )

if __name__ == "__main__":
    print("Starting server")
    socketio.run(app=app, host="0.0.0.0")
    print("Closing server")
