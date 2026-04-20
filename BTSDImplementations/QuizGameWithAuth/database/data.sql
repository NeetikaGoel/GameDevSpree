
USE quizGame;

INSERT IGNORE INTO questions (id, text, type) VALUES
(1,'What is 2+2?','mcq'),
(2,'What is the capital of France?','mcq'),
(3,'The sun rises in the East!','true/false'),
(4,'What is 5x3?','mcq'),
(5,'PHP is a backend language!','true/false');

INSERT IGNORE INTO answer_options (id, text, type, question_id, is_correct) VALUES
(1,'3','mcq',1,false),
(2,'4','mcq',1,true),
(3,'5','mcq',1,false),
(4,'Berlin','mcq',2,false),
(5,'Paris','mcq',2,true),
(6,'Rome','mcq',2,false),
(7,'True','true/false',3,true),
(8,'False','true/false',3,false),
(9,'10','mcq',4,false),
(10,'15','mcq',4,true),
(11,'20','mcq',4,false),
(12,'True','true/false',5,true),
(13,'False','true/false',5,false);

INSERT INTO game_configs
(
    id,
    game_config_name,
    question_count_target,
    question_id_list_allowed_json,
    secret_key
)
VALUES
(1,'default_quiz',5,'[1,2,3,4,5]','neetikagoel12345')
ON DUPLICATE KEY UPDATE
question_count_target=VALUES(question_count_target),
question_id_list_allowed_json=VALUES(question_id_list_allowed_json),
secret_key=VALUES(secret_key);



INSERT IGNORE INTO users(uid,user_id,login_type,email,name,password_hash,created_at,updated_at) VALUES (1,'user_admin_001','registered','admin1@example.com','AdminABC','$2y$12$vr2hxA8txTiDkURhbAPzEOhDuOrYFXlI6EANiZtB68nThOC8Mjh2y',NOW(),NOW());

UPDATE users
SET password_hash = '$2y$12$vr2hxA8txTiDkURhbAPzEOhDuOrYFXlI6EANiZtB68nThOC8Mjh2y',
    updated_at = NOW()
WHERE uid = 1;

INSERT IGNORE INTO user_permissions(id,uid,permission_group,created_at,updated_at) VALUES (1,1,'admin',NOW(),NOW());

INSERT IGNORE INTO users(uid,user_id,login_type,email,name,password_hash,created_at,updated_at) VALUES (2,'guest_001','guest',NULL,NULL,NULL,NOW(),NOW());

INSERT IGNORE INTO user_permissions(id,uid,permission_group,created_at,updated_at) VALUES (2,2,'guest',NOW(),NOW());