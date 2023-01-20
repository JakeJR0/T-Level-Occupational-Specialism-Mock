from flask import Flask
from flask_socketio import SocketIO
from flask_mysqldb import MySQL
import eventlet

# Set up eventlet server as middleware
eventlet.monkey_patch()


app = Flask(__name__)
app.config['SECRET_KEY'] = 'secret!'
app.config["MYSQL_HOST"] = "localhost"
app.config["MYSQL_PASSWORD"] = ""
app.config["MYSQL_DB"] = "occupational_specialism_mock"
app.config["MYSQL_CURSORCLASS"] = "DictCursor"

# Middleware

mysql = MySQL(app)
socketio = SocketIO(app, cors_allowed_origins="*")

if __name__ == "__main__":
    app.run(host="0.0.0.0", debug=True)