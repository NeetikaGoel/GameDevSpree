USE quizgame;

ALTER TABLE game_configs
    -> ADD COLUMN is_active BOOLEAN NOT NULL DEFAULT FALSE,
    -> ADD COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    -> ADD COLUMN updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
Query OK, 0 rows affected (0.014 sec)
Records: 0  Duplicates: 0  Warnings: 0


UPDATE game_configs
SET is_active = FALSE;


UPDATE game_configs
SET is_active = TRUE
WHERE game_config_name = 'default_quiz';


ALTER TABLE game_configs
MODIFY secret_key VARCHAR(255) NOT NULL DEFAULT 'neetikagoel12345';

UPDATE game_configs
SET
    created_at = NOW(),
    updated_at = NOW()
WHERE created_at IS NULL OR updated_at IS NULL;


ALTER TABLE users
MODIFY created_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
MODIFY updated_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3);


ALTER TABLE user_permissions
MODIFY created_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
MODIFY updated_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3);


ALTER TABLE user_progress_states
MODIFY created_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
MODIFY updated_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3);


--not needed though but do it for the plot
ALTER TABLE quiz_attempts
MODIFY created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
MODIFY updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;




-- more commands for play again thing

USE quizgame;

ALTER TABLE user_progress_states
ADD COLUMN game_config_id INT NULL AFTER uid,
ADD COLUMN score_highest INT NOT NULL DEFAULT 0 AFTER score_current,
ADD COLUMN play_count INT NOT NULL DEFAULT 1 AFTER score_highest;

UPDATE user_progress_states
SET
    game_config_id = 1,
    score_highest = score_current,
    play_count = 1
WHERE game_config_id IS NULL;

ALTER TABLE user_progress_states
MODIFY game_config_id INT NOT NULL;

ALTER TABLE user_progress_states
DROP COLUMN questions_done,
DROP COLUMN question_id_current,
DROP COLUMN is_complete;

ALTER TABLE user_progress_states
ADD CONSTRAINT unique_uid_game_config_id UNIQUE (uid, game_config_id);

ALTER TABLE user_progress_states
ADD CONSTRAINT fk_user_progress_states_game_config_id
FOREIGN KEY (game_config_id) REFERENCES game_configs(id);

ALTER TABLE user_progress_states
MODIFY created_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
MODIFY updated_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3);