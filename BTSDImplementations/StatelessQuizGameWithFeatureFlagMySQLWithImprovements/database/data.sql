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