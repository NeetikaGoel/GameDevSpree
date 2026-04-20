
## SESSION PRIORITY DASHBOARD


# FLOW OF THE SYSTEM ::

1. User opens the dashboard first
2. Sees various task cards
3. Drags items up/down to set priority order he/she likes
4. Clicks Create Session button that will start session
5. Clicks Save Priority so priority gets fixed for that session
6. Clicks Start Processing which will start the execution
7. Backend verifies session and uses SplPriorityQueue to decide which tasks to show first
8. Frontend shows the tasks being processed in that order decided by the user


# FILE STRUCTURE :: 

session_priority_dashboard/
│
├── public/     ->>>Browser files
│   ├── index.html    ->>>Page that user will see
│   ├── styles.css    ->>>Styling of the page user will see
│   ├── working/
│   │   ├── create-session.php  ->>>Creates a session for the user
│   │   ├── save-priority.php   ->>>Saves the priority saved by the user
│   │   └── process-session.php   ->>>Uses SplPriorityQueue built-in function and returns tasks in priority order set by user
│   └── outputs/
│       └── main.js
│
├── main.ts.  ->>>Frontend Logic file in Typescript
│── php/
│   ├── storage.php.  ->>>Session data store in PHP
│   └── tasks.php     ->>>Task names and messages to be shown
│
├── storage/      ->>> Stores session data that backend sends
│   └── sessions/
│
├── package.json
└── tsconfig.json


Semantic version style :::
majorversion.minorversion.patchupdate


tsc-typescript compiler command


npm run build- build tsc file

will convert main.ts to main.js in outputs folder



Main.ts file working --- typescript

-> handles frontend behaviour
-> reads all imp html elements first
-> sets up drag drop functionality
-> give buttons its actions
-> start processing whcih will go to backedn
-> frontend display tasks 1 by 1
-> start to done message for each task


How to run this project ?????

From the project folder:

npm install
npm run build
php -S localhost:8009 -t public

Then open:

http://localhost:8009