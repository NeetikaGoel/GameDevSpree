CREATE DATABASE IF NOT EXISTS quizGame;
USE quizGame;

-- TINYINT (1 byte = 8 bits) → Signed: -128 to 127 | Unsigned: 0 to 255
-- SMALLINT (2 bytes = 16 bits) → Signed: -32,768 to 32,767 | Unsigned: 0 to 65,535
-- MEDIUMINT (3 bytes = 24 bits) → Signed: -8,388,608 to 8,388,607 | Unsigned: 0 to 16,777,215
-- INT (4 bytes = 32 bits) → Signed: -2,147,483,648 to 2,147,483,647 | Unsigned: 0 to 4,294,967,295
-- BIGINT (8 bytes = 64 bits) → Signed: -9,223,372,036,854,775,808 to 9,223,372,036,854,775,807 | Unsigned: 0 to 18,446,744,073,709,551,615

-- BOOLEAN ALSO BRACKET THING GONE, JUST 1 BIT IT WILL TAKE

-- ENGINE INNO DB

-- ADD COMMENTS TOO

CREATE TABLE IF NOT EXISTS questions
(
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique question identifier',
    type VARCHAR(20) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL COMMENT 'Question type like mcq or true/false',
    text VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Actual question text shown to user'
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS answer_options 
(
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique answer option identifier',
    type VARCHAR(20) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL COMMENT 'Type of question this option belongs to',
    text VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Answer option text',
    question_id INT UNSIGNED NOT NULL COMMENT 'Foreign key linking to questions.id',
    is_correct BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Flag to mark correct answer option',
    INDEX idx_question_id (question_id),
    FOREIGN KEY (question_id) REFERENCES questions(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS quiz_attempts
(
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique quiz attempt identifier',
    score_current SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Current score during quiz',
    questions_done_count TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Number of questions answered so far',
    question_id_order_json VARCHAR(512) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL COMMENT 'Ordered list of question IDs in JSON format',
    question_id_order_index_current TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Current index in question order',
    question_id_current TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Current question ID being attempted',
    is_complete BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Whether quiz attempt is complete',
    created_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT 'Record creation timestamp',
    updated_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3) COMMENT 'Last update timestamp'
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS game_configs
(
    id SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique game configuration identifier',
    game_config_name VARCHAR(100) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL UNIQUE COMMENT 'Name of the game configuration',
    question_count_target TINYINT UNSIGNED NOT NULL COMMENT 'Total number of questions in this config',
    question_id_list_allowed_json VARCHAR(512) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL COMMENT 'Allowed question IDs list in JSON format',
    secret_key VARCHAR(20) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL DEFAULT 'neetikagoel12345' COMMENT 'Secret key used for hashing',
    is_active BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Indicates if this config is currently active',
    created_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT 'Record creation timestamp',
    updated_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3) COMMENT 'Last update timestamp'
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS users
(
    uid INT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique user identifier',
    user_id VARCHAR(100) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL UNIQUE COMMENT 'Public user identifier string',
    login_type VARCHAR(20) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL COMMENT 'Login type: guest or registered',
    email VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT 'User email address',
    name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT 'User display name',
    password_hash VARCHAR(255) CHARACTER SET ascii COLLATE ascii_general_ci NULL COMMENT 'Hashed password for registered users',
    created_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT 'Record creation timestamp',
    updated_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3) COMMENT 'Last update timestamp'
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS user_permissions
(
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique permission record identifier',
    uid INT UNSIGNED NOT NULL COMMENT 'Foreign key linking to users.uid',
    permission_group VARCHAR(32) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL COMMENT 'Permission group like guest, user, admin',
    created_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT 'Record creation timestamp',
    updated_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3) COMMENT 'Last update timestamp',
    INDEX idx_user_permissions_uid (uid),
    FOREIGN KEY (uid) REFERENCES users(uid)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS user_progress_states
(
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique progress state identifier',
    uid INT UNSIGNED NOT NULL COMMENT 'Foreign key linking to users.uid',
    game_config_id SMALLINT UNSIGNED NOT NULL COMMENT 'Foreign key linking to game_configs.id',
    score_current SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Current score in ongoing attempt',
    score_highest SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Highest score achieved by user for this config',
    play_count SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Number of times user played this config',
    question_id_order_json VARCHAR(512) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL COMMENT 'Ordered list of question IDs for this attempt',
    question_id_order_index_current TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Current index in question order',
    created_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT 'Record creation timestamp',
    updated_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3) COMMENT 'Last update timestamp',
    UNIQUE KEY unique_uid_game_config_id (uid, game_config_id),
    INDEX idx_user_progress_states_uid (uid),
    INDEX idx_user_progress_states_game_config_id (game_config_id),
    FOREIGN KEY (uid) REFERENCES users(uid),
    FOREIGN KEY (game_config_id) REFERENCES game_configs(id)
) ENGINE=InnoDB;