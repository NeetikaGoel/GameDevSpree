CREATE DATABASE IF NOT EXISTS quizGame;
USE quizGame;

CREATE TABLE IF NOT EXISTS questions
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(100) NOT NULL,
    text VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS answer_options 
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(100) NOT NULL,
    text VARCHAR(100) NOT NULL,
    question_id INT NOT NULL,
    is_correct BOOLEAN NOT NULL,
    FOREIGN KEY (question_id) REFERENCES questions(id)
);

CREATE TABLE IF NOT EXISTS quiz_attempts
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    score_current INT NOT NULL,
    questions_done INT NOT NULL,
    question_id_order_json TEXT NOT NULL,
    question_id_order_index_current INT NOT NULL,
    question_id_current INT NOT NULL,
    is_complete BOOLEAN NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS game_configs
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_config_name VARCHAR(100) NOT NULL UNIQUE,
    question_count_target INT NOT NULL,
    question_id_list_allowed_json TEXT NOT NULL,
    secret_key VARCHAR(255) NOT NULL DEFAULT 'neetikagoel12345',
    is_active BOOLEAN NOT NULL DEFAULT FALSE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS users
(
    uid INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(100) NOT NULL UNIQUE,
    login_type VARCHAR(50) NOT NULL,
    email VARCHAR(255) NULL,
    name VARCHAR(255) NULL,
    password_hash VARCHAR(255) NULL,
    created_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    updated_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3)
);

CREATE TABLE IF NOT EXISTS user_permissions
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    uid INT NOT NULL,
    permission_group VARCHAR(50) NOT NULL,
    created_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    updated_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),
    FOREIGN KEY (uid) REFERENCES users(uid)
);

CREATE TABLE IF NOT EXISTS user_progress_states
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    uid INT NOT NULL,
    score_current INT NOT NULL,
    questions_done INT NOT NULL,
    question_id_order_json TEXT NOT NULL,
    question_id_order_index_current INT NOT NULL,
    question_id_current INT NOT NULL,
    is_complete BOOLEAN NOT NULL,
    created_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    updated_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),
    FOREIGN KEY (uid) REFERENCES users(uid)
);