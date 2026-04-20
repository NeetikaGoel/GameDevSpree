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

-- NEW TABLE FOR THE PURPOSE OF MAKING IT STATELESS
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
    question_id_list_allowed_json TEXT NOT NULL
);
