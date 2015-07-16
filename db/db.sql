--
--  mysql -u root
--
-- CREATE USER 'user'@'localhost' IDENTIFIED BY 'pass';
-- GRANT USAGE ON *.* TO user@localhost identified by 'pass';
-- GRANT ALL PRIVILEGES ON user.* TO user@localhost;
-- CREATE DATABASE IF NOT EXISTS base;
-- ALTER DATABASE base CHARACTER SET utf8 COLLATE utf8_general_ci;
--
-- SHOW VARIABLES LIKE 'character_set_database';
-- SHOW VARIABLES LIKE 'collation_database';
--

DROP TABLE IF EXISTS game;

CREATE TABLE IF NOT EXISTS game (
	id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
	board varchar(9) NOT NULL,
	status INT NOT NULL,
	turn varchar(32) NOT NULL
);

INSERT INTO game (board, status, turn) VALUES ('         ', 0, 'Cris Stanza');
