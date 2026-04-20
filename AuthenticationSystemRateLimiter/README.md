This project is an Basic Authentication system with the following features::

1. Login system with PHP Language
2. Rate Limiter so as to block repeated bad login attempts so only 2 attempts are allowed in 30 seconds
3. A dashboard containing greetings and a feedback form
4. Feedback is received by Node.js API for asynchronous executioning
5. Session feedback is stored in PHP Endpojnt

Full flow of the system built ::

Step 1:: User opens login page
index.php shows the login form


Step 2:: User clicks Login
Form sends data to login.php


Step 3:: login.php checks:
a. Is request valid?
b. Is user rate-limited i.e. tried more than allowed attempts?
c. Are email and password correct?

If correct::

Session gets created and user goes to dashboard.php

If not correct then ::

Session stores error and we go back to index.php

Step 4:: Dashboard opens

Dashboard.php checks if user is logged in
If not, send him back to login page
If yes, shows dashboard and feedback form


Step 5:: User submits feedback
script.js takes form submission
It sends feedback to Node server at localhost:3000/feedback
Node replies with success
then JS sends same feedback to PHP file save_feedback.php
PHP saves feedback in that paeticular session


Step 6: Session-based message updates
Feedback.php reads stored feedback from session
dashboard.php shows a particular welcome message depending on rating given by user in last time


Step 7: Logout
logout.php destroys session
redirects to login page


In sessions, we need to save:: 

login state, error msg if any, rate limit counts, feedback value

IMPORTANT:::

Node handles external async feedback API behavior.
PHP stores session for dashboard personalization.




//just for the sake of clarity

$_SESSION['useremail'] = tells if user is logged in
$_SESSION['error'] = stores message like “Invalid credentials”
$_SESSION['count'] = number of login attempts did by user
$_SESSION['feedback'] = stores user’s rating


SO WHAT THE FINAL FLOW SHOULD LOOK LIKE:::

i. The system starts at index.php file, which displays the login page.
ii. When the user submits the login form, data is sent by POST to login.php.
iii. In login.php, the request method is verified that is it really post, then the RateLimit class checks whether too many attempts have been made in the present time window.
iv. If the request is allowed, the credentials are checked against hardcoded demo values.
v. If correct, the User class stores the email in session and the user is then redirected to dashboard.php.
vi. The dashboard verifies login state using session, then shows a personalized message using the Feedback class based on prev visit.
vii. When the user submits feedback, JavaScript reads the form and sends the rating asynchronously to the Node.js API at /feedback endpoint.
viii. After Node confirms receipt, JavaScript sends another POST request to save_feedback.php, which stores the same rating inside the PHP session.
ix. On the next dashboard load, the stored session feedback is read and a custom changing welcome message is displayed.
x. Finally, logout.php destroys the session and redirects the user back to the login page.



HOW TO RUN THE PROJECT:: 

We need to run both servers.

1. Starting Node server

From inside node folder:

npm install
npm start


2. Starting PHP server

From the project root:

php -S localhost:8000

Open in browser the following:: 

http://localhost:8000/php/index.php



3. Demo login

email: test@gmail.com
password: 1234