--
--  mysql -u root
--
-- CREATE USER 'u245853626_user'@'localhost' IDENTIFIED BY 'password';
-- GRANT USAGE ON *.* TO u245853626_user@localhost identified by 'password';
-- GRANT ALL PRIVILEGES ON u245853626_base.* TO u245853626_user@localhost;
-- CREATE DATABASE IF NOT EXISTS u245853626_base;
-- ALTER DATABASE u245853626_base CHARACTER SET utf8 COLLATE utf8_general_ci;
-- CONNECT u245853626_user;
--
-- SHOW TABLES;
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

INSERT INTO game (board, status, turn) VALUES ('         ', 0, 'Stanza');
