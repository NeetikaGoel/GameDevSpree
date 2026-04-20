SO NOW TO MAKE THIS PROJECT STATELESS- WE WILL NEED TO STORE IT IN EITHER OF THESE 3 OPTIONS:::

A. CLIENT SIDE
B. TEMPORARY FILE STORAGE
C. DATABASE

CLIENT SIDE IS UNTRUSTWORTHY!!!!
DATABASE BETTER ALTERNATIVE FROM TEMPORARY FILE STORAGE


SO NOW WE NEED 1 MORE TABLE NAMED AS MAYBE quiz_attempts

it will save all the attempts of hte user with the info that were saved in session variables- 

On starting new quiz- a new row of attempt id will be added in the quiz_attempts table in the database

frontend will send attempt id on start of quiz
and then on each question attempt id and answerOption id he/she chose


backend will check all the boundary validation sanitization checks on the attempt data fetched from the database

for result as well, quizAttempt id will help us to find the actual score of the user


//now we won't need question id from the frontend coz we will already know it from the database hehe, amazing!!!!!!



files that will be added new are

quizattempt in all 3 folders of database

files that will change::::::
frontend/quiz.ts
backend/quizLoad.php
backend/quizSubmit.php
backend/quizResultShow.php

now what else?//????//

1. stateless



quizLoad.php

Create or load quiz attempt from DB, then return current question!

quizSubmit.php

Validate selected answer against current DB-backed attempt state, then update attempt in DB!

quizResultShow.php

Load completed attempt from DB and return final result summary!


tsc quiz.ts --target ES2020 --module ES2020
mysql -u root -p < database/schema.sql
mysql -u root -p < database/data.sql  
php -S 127.0.0.1:8004




2. quiz config addition class+table+repo


to change:: questionRepo, answerOptionRepo, all 3 backend api files, and much more omg


3. code documentation





4. boundary check all steps


DONE DONE DONE

5. git commit


HAVE TO STILL



6. make technical document in detail


A BIT HAVE TO CORRECT


7. correct folder structure


WE WILL SEE