yeah so stepwise

1) splitted schema.sql into schema and data where schema only contains create like (DDL) commands and data will contain insert commands

2) make 3 folders - mapper, query, repository

3) 2 files for answerOption and question in each folder

4) also 1 dbmanager that will serve the db side

5) then ormmanager which can connect server to db

6) change path of repository files in quiz.php file





how it will run now:::

mysql -u root -p < database/schema.sql

mysql -u root -p quizGame < database/data.sql


php -S 127.0.0.1:8006