<?php

define("DB_HOST", "localhost");
define("DB_PASSWORD", "");
define("DB_USERNAME", "root");
define("DB_NAME", "occupational_specialism_mock");

$connection = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

$users_table = "
CREATE TABLE IF NOT EXISTS users(
	ID INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
	first_name VARCHAR(20) NOT NULL,
	last_name VARCHAR(30) NOT NULL,
	email VARCHAR(20) NOT NULL,
	password VARCHAR(100) NOT NULL,
	user_type VARCHAR(20) DEFAULT 'user' NOT NULL,
	membership_type VARCHAR(20) NOT NULL,
    dob DATE NOT NULL,
    private_key VARCHAR(500) NOT NULL,
	created_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
) AUTO_INCREMENT=100000;
";

$support_chat_table = "
CREATE TABLE IF NOT EXISTS support_chat(
    ID INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
    user_id INTEGER NOT NULL,
    status VARCHAR(20) DEFAULT 'open' NOT NULL,
    started_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(ID)
) AUTO_INCREMENT=100000;
";

$support_chat_messages_table = "
CREATE TABLE IF NOT EXISTS support_chat_messages(
    ID INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
    chat_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    message VARCHAR(255) NOT NULL,
    sent_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    FOREIGN KEY (chat_id) REFERENCES support_chat(ID),
    FOREIGN KEY (user_id) REFERENCES users(ID)
) AUTO_INCREMENT=100000;
";

$articles_table = "
CREATE TABLE IF NOT EXISTS articles (
    ID INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
    creator_id INTEGER NOT NULL,
    title VARCHAR(20) NOT NULL,
    description VARCHAR(100) NOT NULL,
    content VARCHAR(10000) NOT NULL,
    price INTEGER DEFAULT 0 NOT NULL,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    created_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    FOREIGN KEY (creator_id) REFERENCES users(ID)
) AUTO_INCREMENT=100000;
";

$forum_threads_table = "
CREATE TABLE IF NOT EXISTS forum_threads(
    ID INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
    creator_id INTEGER NOT NULL,
    title VARCHAR(20) NOT NULL,
    description VARCHAR(1000) NOT NULL,
    thread_type VARCHAR(20) NOT NULL,
    price INTEGER DEFAULT 0 NOT NULL,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    created_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    FOREIGN KEY (creator_id) REFERENCES users(ID)
) AUTO_INCREMENT=100000;
";

$forum_messages_table = "
CREATE TABLE IF NOT EXISTS forum_messages(
    ID INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
    thread_id INTEGER NOT NULL,
    creator_id INTEGER NOT NULL,
    replying_to INTEGER,
    message VARCHAR(255) NOT NULL,
    sent_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    FOREIGN KEY (thread_id) REFERENCES forum_threads(ID),
    FOREIGN KEY (creator_id) REFERENCES users(ID),
    FOREIGN KEY (replying_to) REFERENCES forum_messages(ID)
) AUTO_INCREMENT=100000;
";

$contact_table = "
CREATE TABLE IF NOT EXISTS contact(
    ID INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
    first_name VARCHAR(20) NOT NULL,
    last_name VARCHAR(20) NOT NULL,
    email VARCHAR(50) NOT NULL,
    message VARCHAR(1000) NOT NULL,
    sent_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) AUTO_INCREMENT=100000;
";

$article_purchase_history_table = "
CREATE TABLE IF NOT EXISTS article_purchase_history(
    ID INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
    user_id INTEGER NOT NULL,
    item_id INTEGER NOT NULL,
    purchased_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(ID),
    FOREIGN KEY (item_id) REFERENCES articles(ID)
) AUTO_INCREMENT=100000;
";

$forum_purchase_history_table = "
CREATE TABLE IF NOT EXISTS forum_purchase_history(
    ID INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
    user_id INTEGER NOT NULL,
    item_id INTEGER NOT NULL,
    purchased_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(ID),
    FOREIGN KEY (item_id) REFERENCES forum_threads(ID)
) AUTO_INCREMENT=100000;
";

$create_tables = array(
    $users_table,
    $support_chat_table,
    $support_chat_messages_table,
    $articles_table,
    $forum_threads_table,
    $forum_messages_table,
    $contact_table,
    $article_purchase_history_table,
    $forum_purchase_history_table
);

for ($i = 0; $i < count($create_tables); $i++) {
    try {
        if (!mysqli_query($connection, $create_tables[$i])) {
            error_log("Error creating table: " . mysqli_error($connection));
            exit("<h1 class='center-text'>Unexpected error occurred. Please try again later.</h1>");
        }
    } catch (Exception $e) {
        error_log("Error creating table: " . $e->getMessage());
        exit("<h1 class='center-text'>Unexpected error occurred. Please try again later.</h1>");
    }

}

?>