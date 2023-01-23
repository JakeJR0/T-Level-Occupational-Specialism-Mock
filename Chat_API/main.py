from flask import Flask
from flask_socketio import SocketIO
from flask_mysqldb import MySQL
import gevent


app = Flask(__name__)
app.config['SECRET_KEY'] = 'secret!'
app.config["MYSQL_HOST"] = "localhost"
app.config["MYSQL_PASSWORD"] = ""
app.config["MYSQL_DB"] = "occupational_specialism_mock"
app.config["MYSQL_CURSORCLASS"] = "DictCursor"

# Middleware

mysql = MySQL(app)
socketio = SocketIO(app, cors_allowed_origins="*")

# SocketIO events

@socketio.on('connect')
def test_connect():
    print('Client connected')

@socketio.on('disconnect')
def test_disconnect():
    print('Client disconnected')

@socketio.on("start_chat")
def start_chat():
    if chat_number == -1:
        cursor = mysql.connection.cursor()

        create_chat = """
            INSERT INTO support_chat(user_id, status)
            VALUES (%s, %s)
        """

        cursor.execute(create_chat, )
        chat_number = cursor.lastrowid

    cursor = mysql.connection.cursor()

    get_messages = """
        SELECT * FROM support_chat_messages
        WHERE support_chat_id = %s
    """

    cursor.execute(get_messages, )
    messages = cursor.fetchall()

    socketio.emit("chat_messages", messages)

@socketio.on('message')
def handle_message(message_information):
    username = message_information["username"]
    message = message_information["message"]

    cursor = mysql.connection.cursor()

    user_message = """
        INSERT INTO support_chat_messages ()
    """

    cursor.execute(user_message, (username, message))

if __name__ == "__main__":
    app.run(host="0.0.0.0", debug=True)