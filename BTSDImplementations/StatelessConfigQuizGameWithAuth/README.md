QUESTION ANSWERING SYSTEM

QUESTIONS OF 2 TYPES -  
1) MCQ
2) TRUE/FALSE


FOR MCQ WE WILL MATCH ANSWER ID WITH QUESTION ID AND HENCE GIVE OPTIONS OF ANSWERS ON THE RESPECTIVE QUESTION ON FRONTEND

FOR TRUE FALSE- ONLY 2 OPTIONS
REST QUESTIONS-  3 OPTIONS


PROPERTIES OF QUES/ANS:

1) ID
2) TYPE
3) TEXT


WHAT FILES WOULD WE NEED

FOR BACKEND-

A CLASS FOR QUESTION
A CLASS FOR QUIZ
A CLASS FOR 1 ANSWER OPTION
SOME QUIZ DATA TO PUT FOR NOW
SOME FILE TO REPRESENT CONSTANT VALUES


SO WE CAN START WITH INDEX.HTML
WILL HAVE ITS OWN STYLE.CSS

THEN WE WILL GO TO QUIZLOAD.PHP WHICH WILL SHOW QUESTIONS TO THE USER
THEN QUIZSUBMIT TO SUBMIT ANSWER TO THAT QUESTION AND CHECK ANSWER 
THEN AGAIN QUIZLOAD.PHP IN LOOP SO ITERATIVELY ALL QUESTIONS ARE BEING SHOWN AND THEN 
FINALLY QUIZRESULTSHOW.PHP WHCIH WILL SHOW THE RESULT TO USER

HURRAY DONEEEE!!!!!



//RUN THE PROJECT IN TERMINAL


From project directory--- 
php -S 127.0.0.1:8000


TO TEST IN TERMINAL
---WE NEED TO USE COOKIE JAR FILE THAT SAVES OUR SESSION REQUESTS


Step 1: Start quiz / load first question
curl -c cookie.txt http://127.0.0.1:8000/backend/quizLoad.php

THIS HELPS TO CREATE SESSION, SEND REQUEST TO QUIZLOAD.PHP AND STORY SESSION DATA IN COOKIE JAR FILE TOO AND PRINTS JSON FOR 1ST QEUSTION


Step 2: Submit answer for q1
curl -b cookie.txt -c cookie.txt -X POST \
  -d "questionId=1&answerOptionId=2" \
  http://127.0.0.1:8000/backend/quizSubmit.php


THIS WILL FIRST SEND THE SESSION COOKIE ALREADY SAVED ( -B) AND THEN UPDATE IT WITH (-C) IF THERE IS ANY NEED OFC, THEN -X POST WILL USE POST REQUEST AND -D IS FROM WHERE SENDS FORM DATA, BACKEND WILL CHECK ANSWER AND GIVE RESULT IN JSON FORMAT



///  change parameters to see if it actually works for invalid input too




Step 3: Load current question again
curl -b cookie.txt -c cookie.txt http://127.0.0.1:8000/backend/quizLoad.php

SESSION NOW HAVE UPDATED QUES ID AS CURRENT SO IT WILL GIVE NEXT QUES



Step 4: Continue quiz
Ques 2 :::: correct ans id = 5
curl -b cookie.txt -c cookie.txt -X POST \
  -d "questionId=2&answerOptionId=5" \
  http://127.0.0.1:8000/backend/quizSubmit.php



Ques 3 :::: correct ans id = 7
curl -b cookie.txt -c cookie.txt -X POST \
  -d "questionId=3&answerOptionId=7" \
  http://127.0.0.1:8000/backend/quizSubmit.php



Ques 4 :::: correct ans id = 10
curl -b cookie.txt -c cookie.txt -X POST \
  -d "questionId=4&answerOptionId=10" \
  http://127.0.0.1:8000/backend/quizSubmit.php



Ques 5 :::: correct ans id = 12
curl -b cookie.txt -c cookie.txt -X POST \
  -d "questionId=5&answerOptionId=12" \
  http://127.0.0.1:8000/backend/quizSubmit.php


NOW IT SHOULD SAY QUIZ DONE HURRAY THANKSS!!!


NOW TO SHOW FINAL RESULT

curl -b cookie.txt -c cookie.txt http://127.0.0.1:8000/backend/quizResultShow.php




EXTRA TEST CASES TO CHECK IN AND OUT EVERYTHING

A.....
Invalid input test - STRING ID
curl -b cookie.txt -c cookie.txt -X POST \
  -d "questionId=abc&answerOptionId=xyz" \
  http://127.0.0.1:8000/backend/quizSubmit.php


  IS IT GIVING ERROR?? COZ IT SHOULD!!


B....
Wrong answer test

Q1 wrong answer is for eg option 1

curl -b cookie.txt -c cookie.txt -X POST \
  -d "questionId=1&answerOptionId=1" \
  http://127.0.0.1:8000/backend/quizSubmit.php

IT SHOULD NOT INCREASE SCORE AND QUES SHOULD GO TO NEXT


C.....

Mismatched answer and question

curl -b cookie.txt -c cookie.txt -X POST \
  -d "questionId=1&answerOptionId=5" \
  http://127.0.0.1:8000/backend/quizSubmit.php

  ANY OTHER QUES, ANY OTHER ANS 
  SHOULD BE REJECTED BY THE BACKEND!!!




SO I HAVE NOW 3 BACKEND API ENDPOINTS:

1. quizLoad.php
Which will load current quiz question state

2. quizSubmit.php
which will submit selected answer and progress quiz

3. quizResultShow.php
fetch final quiz result summary

these will return json



//now for running ts

npm install -g typescript
tsc --version
tsc quiz.ts --target ES2020 --module ES2020



Start your PHP backend server:
php -S 127.0.0.1:8001
Open browser:
http://127.0.0.1:8001/frontend/index.html
Click Start Quiz !!!!!!!!!!!!!!!!!