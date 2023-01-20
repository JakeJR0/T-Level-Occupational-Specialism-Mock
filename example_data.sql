-- CREATE TABLE users(
-- 	ID INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
-- 	first_name VARCHAR(20) NOT NULL,
-- 	last_name VARCHAR(30) NOT NULL,
-- 	email VARCHAR(20) NOT NULL,
-- 	password VARCHAR(20) NOT NULL,
-- 	user_type VARCHAR(20) DEFAULT 'user' NOT NULL,
-- 	membership_type VARCHAR(20) NOT NULL,
-- 	created_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
-- ) AUTO_INCREMENT=100000;

-- CREATE TABLE articles (
-- 	ID INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
-- 	creator_id INTEGER NOT NULL,
-- 	title VARCHAR(20) NOT NULL,
-- 	description VARCHAR(1000) NOT NULL,
-- 	price INTEGER DEFAULT 0 NOT NULL,
-- 	last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
-- 	created_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
-- 	FOREIGN KEY (creator_id) REFERENCES users(ID)
-- ) AUTO_INCREMENT=100000;


-- Admin User

INSERT INTO users(first_name, last_name, email, password, user_type, membership_type)
VALUES('Admin', 'User', 'admin@user.com', 'root_account', 'admin', 'admin');

-- Article

INSERT INTO articles(creator_id, title, description, content, price)
VALUES(100000, 'Article Title', 'Article Description', "This is an example of an article written by an admin" , 0);