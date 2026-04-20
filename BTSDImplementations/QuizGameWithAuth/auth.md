so now instead of quizattemptid thing, i want user linked thing

users table


uid = primary key, internal db id
user_id = public unique server-generated id
login_type = guest/registered
email
name
password
created_at
updated_at


user_permissions table 


id
uid
permission_group
created_at
updated_at


user_progress_states table

id
uid
score_current
questions_done
question_id_order_json
question_id_order_index_current
question_id_current
is_complete
created_at
updated_at

kinda similar to quizattempt, we'll see later dw


so our goal here is:::::: progress state of user is saved and it can be restored!!!!!!



HOW WILLL I PROCEED NOW?????

1. database changes- create tables first, add dummy data- simple dimple

2. entity + mapper + query + repository files for all 3 tables

3. add login api - guest + registered

4. updating everything from quiz api to save data into user progress table instead of quiz attempt table 

5. next we can add permission authorization things 

    dont know how but will figure out dw

6. next is extra cute api - add question one for which only admin has access tooo!!!!!



TO RUN:::


cd frontend
tsc auth.ts --target ES2020 --module ES2020
tsc login.ts --target ES2020 --module ES2020
tsc register.ts --target ES2020 --module ES2020
tsc quiz.ts --target ES2020 --module ES2020
tsc questionAdd.ts --target ES2020 --module ES2020
tsc questionSetCreate.ts --target ES2020 --module ES2020
tsc questionSetEdit.ts --target ES2020 --module ES2020

mysql -u root -p < database/schema.sql
mysql -u root -p < database/data.sql


php -S 127.0.0.1:8005


//now checking all tables



in terminal :::

mysql -u root -p
USE quizGame;
SHOW TABLES;
SELECT * FROM users;
SELECT * FROM user_permissions;
SELECT * FROM user_progress_states;
SELECT * FROM questions;
SELECT * FROM answer_options;
SELECT * FROM game_configs;
EXIT;

//to get pw hash

php -r "echo password_hash('admin123', PASSWORD_DEFAULT) . PHP_EOL;"


NEW USER WORKING CHECK


sudo -u appuser -i
whoami
cd /Users/neetika.goel/Desktop/InternPrepWorkAgain/Projects/BTSDImplementations/QuizGameWithAuth

php -S 127.0.0.1:8005



//HAD TO GIVE TRAVERSAL PERMISSIONS



new hash after secret key
php -r "echo password_hash('admin123::neetikagoel12345', PASSWORD_DEFAULT) . PHP_EOL;"





auth.ts

!!! shared auth utility file

It does:

set cookies
get cookies
delete cookies
save user session after login/register/guest login
clear old session before switching guest to registered
decide if current user is admin
show/hide navbar buttons

This is the most important helper file now.

index.html

This is now your home page.

It shows:

login
register
guest login
start quiz
admin-only add question button
logout if logged in
login.html

This is for registered user login.

It has:

email input
password input
login button
guest login button too
register.html

This is for new account creation.

It has:

name input
email input
password input
register button
guest login button too
login.ts

This controls:

guest login from index/login page
registered login from login page
cookie saving
redirect to quiz
start quiz button behavior on index page

It also implements your rule:

if guest already exists → reuse guest
if registered user clicks guest → clear old cookies and create new guest
if guest later logs in → guest cookies cleared and registered cookies saved
register.ts

This controls:

registration submit
guest login from register page
cookie save
redirect to quiz
quiz.html

This remains your quiz page, but now has:

navbar
admin-only add question button
logout button
user text label
result.html

This remains your result page, but now also has:

navbar
admin-only add question button
logout button
user text label
quiz.ts

This now no longer depends on:

quizAttemptId
URL-based attempt state

Instead it uses:

uid from cookies

So:

quiz load uses uid
quiz submit uses uid
result load uses uid

This matches your new user-based stateless backend direction.



What each file is doing now
loginGuest.php

Creates guest session identity through service and returns user info.

loginUser.php

Validates email/password and returns registered user info.

registerUser.php

Creates registered user or upgrades guest to registered.

quizLoad.php

Authenticates by uid, authorizes by permission, then loads current quiz state through service.

quizSubmit.php

Authenticates by uid, authorizes by permission, validates answerOptionId, delegates answer submission.

quizResultShow.php

Authenticates by uid, authorizes by permission, delegates result loading.

questionAdd.php

Authenticates admin by uid, validates question payload, delegates create-question logic.
