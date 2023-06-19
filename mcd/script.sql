-- ## Fichier crée le 14/06/2023 - dernière modification le 14/06/2023
-- ## SQL pour la base de données MySQL de l'application Pat'Perdue



-- #################################################################################
-- ### GROUPES #####################################################################

-- Table groups --
DROP TABLE IF EXISTS `groups`;

CREATE TABLE `groups` (
    id INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
    name VARCHAR(20) NOT NULL
);

-- Table permissions --
DROP TABLE IF EXISTS permissions;

CREATE TABLE permissions (
    id INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
    name VARCHAR(50),
    level TINYINT(2)
);

-- Table groups _ permissions --
DROP TABLE IF EXISTS groups_permissions;

CREATE TABLE groups_permissions (
	group_id INTEGER NOT NULL,
    FOREIGN KEY (group_id) REFERENCES `groups`(id),
	permission_id INTEGER NOT NULL,
    FOREIGN KEY (permission_id) REFERENCES permissions(id)
);



-- #################################################################################
-- ### USERS #######################################################################

-- Table users --
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
    username VARCHAR(16) NOT NULL,
    password TEXT NOT NULL,
    email VARCHAR(150) NOT NULL,
	group_id INTEGER NOT NULL DEFAULT 1,
    FOREIGN KEY (group_id) REFERENCES `groups`(id),
    verification_code_email VARCHAR(6),
    is_verified TINYINT(1) DEFAULT 0 NOT NULL,
    token VARCHAR(120),
    verification_code_email_date DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
    creation_date DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
    last_login_date DATETIME
);


-- #################################################################################
-- ### LOGIN_ATTEMPTS ##############################################################

-- Table login_attempts --
DROP TABLE IF EXISTS login_attempts;

CREATE TABLE login_attempts (
    id INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
	user_id INTEGER NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),  
    ip_address VARCHAR(15) NOT NULL,
    attempt_date DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
    attempt_result TINYINT(1) NOT NULL DEFAULT 0
);


-- #################################################################################
-- ### ADVERTS #####################################################################

-- Table adverts --
DROP TABLE IF EXISTS adverts;

CREATE TABLE adverts (
    id INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
	user_id INTEGER NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),  
    animal_type VARCHAR(32) NOT NULL,
    animal_name VARCHAR(32) NOT NULL,
    description VARCHAR(100) NOT NULL,
    city VARCHAR(60) NOT NULL,
    start_date DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
    end_date DATETIME NOT NULL,
    is_premium TINYINT(1) NOT NULL DEFAULT 0,
    is_google_ads TINYINT(1) NOT NULL DEFAULT 0,
    google_ads_time DATETIME,
    latitude FLOAT NOT NULL,
    longitude FLOAT NOT NULL,
    radius FLOAT NOT NULL,
    is_deleted TINYINT(1) NOT NULL DEFAULT 0
);


-- #################################################################################
-- ### IMAGES ######################################################################

-- Table images --
DROP TABLE IF EXISTS images;

CREATE TABLE images (
    id INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
    folder_name VARCHAR(40) NOT NULL,
    file_name VARCHAR(40) NOT NULL,
    link VARCHAR(255) NOT NULL,
    is_main TINYINT(1) NOT NULL DEFAULT 0
);


-- #################################################################################
-- ### ADVERTS_IMAGES ##############################################################

-- Table adverts_images --
DROP TABLE IF EXISTS adverts_images;

CREATE TABLE adverts_images (
	advert_id INTEGER NOT NULL,
    FOREIGN KEY (advert_id) REFERENCES advert(id),
	image_id INTEGER NOT NULL,
    FOREIGN KEY (image_id) REFERENCES images(id)
);


-- #################################################################################
-- ### PRUCHASE_HISTORY ############################################################

-- Table purchase_history --
DROP TABLE IF EXISTS purchase_history;

CREATE TABLE purchase_history (
    id INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
	advert_id INTEGER NOT NULL,
    FOREIGN KEY (advert_id) REFERENCES advert(id),
    paypal_order_id VARCHAR(25) NOT NULL,
    paypal_authorization_id VARCHAR(25) NOT NULL,
    paypal_status VARCHAR(50) NOT NULL,
    date DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL
);


-- #############################################################################################
-- ### VUES ### VUES ### VUES ### VUES ### VUES ### VUES ### VUES ### VUES ### VUES ### VUES ###
-- #############################################################################################

-- View user_group_view --
DROP VIEW IF EXISTS user_group_view;

CREATE VIEW user_group_view AS
SELECT u.id AS user_id, u.username AS user_username, u.token AS user_token, u.email AS user_email, u.group_id, g.name AS group_name
FROM users u
INNER JOIN `groups` g ON u.group_id = g.id
WHERE u.is_verified = 1;

-- Fin du fichier ;)