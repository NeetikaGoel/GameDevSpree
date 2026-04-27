

Flow for the backend changes–

STEP 1
    Update DB schema+migration+seed expectations

STEP 2
    Update entity classes to match new DB shape

STEP 3
    Update query files

STEP 4
    Update mapper files

STEP 5
    Update repository files

STEP 6
    Update concise DB README

STEP 7
    Then move to backend services+APIs:
        quizLoad.php
        quizSubmit.php
        quizResultShow.php
        quizReset.php   (NEW)
        questionSetShowToUser.php   (NEW)


Files that need change-

schema.sql
data.sql
updatedb.sql
database/query/userProgressStateQuery.php
database/mapper/userProgressStateMapper.php
database/repository/userProgressStateRepository.php
database/query/gameConfigQuery.php
database/repository/gameConfigRepository.php
backend/entity/userProgressState.php
backend/entity/gameConfig.php   (only comment meaning update, structure same)
database/README.md


Not to be changed files-

dbManager.php
ormManager.php
database/mapper/gameConfigMapper.php




questionSetShowToUserService.php

Features it should have:

A. fetch all active configs
B. fetch all progress rows for this user
C. map progress by game_config_id
D. for each active config we will have to calculate:
    1. playedAlready
    2. scoreCurrent
    3. scoreHighest
    4. playCount
    5. isComplete
    6. showPlay
    7. showResume
    8. showPlayAgain


quizResetService.php

Features it should have:

A. fetch config by gameConfigId
B. fetch progress row by uid+gameConfigId
C. validate row exists
D. take config question list
E. reset same row:
F. score_current=0
G. question_id_order_index_current=0
H. preserve score_highest
I. increment play_count





# What changed in each updated service file

## `QuizLoadService.php`
- now takes `uid` and `gameConfigId`
- loads selected config by id
- validates config is active
- fetches or creates progress row using `uid+gameConfigId`
- derives completion from `question_id_order_index_current`
- returns `gameConfigId`, `gameConfigName`, `scoreHighest`, `playCount`
- returns full `resultLink` with both `uid` and `gameConfigId`

## `QuizResultShowService.php`
- now takes `uid` and `gameConfigId`
- fetches progress by `uid+gameConfigId`
- derives `questionsDone` from current index
- derives completion instead of using old `is_complete`
- returns `gameConfigName`, `scoreHighest`, `playCount`

## `QuizSubmitService.php`
- now takes `uid`, `gameConfigId`, `answerOptionIdByUser`
- fetches progress by `uid+gameConfigId`
- derives current question id from order array
- updates same row by `uid+gameConfigId`
- preserves highest score using max logic
- completes quiz by moving index to total count
- returns full `resultLink` with both `uid` and `gameConfigId`





Main frontend idea now::::

index.html
    -> Start Quiz
    -> if logged in -> questionSetShowToUser.html
    -> else -> login.html

login/register/guest
    -> save cookies
    -> redirect to questionSetShowToUser.html

questionSetShowToUser.html
    -> call questionSetShowToUser.php
    -> show all active configs
    -> Play / Resume / Play Again buttons

quiz.html
    -> must read gameConfigId from query param
    -> call quizLoad.php?uid=...&gameConfigId=...
    -> submit with gameConfigId too

result.html
    -> must read gameConfigId from query param
    -> call quizResultShow.php?uid=...&gameConfigId=...
    -> Play Again button calls quizReset.php
    -> then redirects to questionSetShowToUser.html



Routing summary nwo::: 


index start quiz
    -> login if not logged in
    -> questionSetShowToUser.html if logged in

login/register/guest success
    -> questionSetShowToUser.html

user clicks Play / Resume
    -> quiz.html?gameConfigId=...

quiz submit complete
    -> result.html?gameConfigId=...

result play again
    -> quizReset.php
    -> questionSetShowToUser.html



HOW TO RUNN:::::::


cd frontend
tsc auth.ts --target ES2020 --module ES2020
tsc login.ts --target ES2020 --module ES2020
tsc register.ts --target ES2020 --module ES2020
tsc quiz.ts --target ES2020 --module ES2020
tsc questionAdd.ts --target ES2020 --module ES2020
tsc questionSetCreate.ts --target ES2020 --module ES2020
tsc questionSetEdit.ts --target ES2020 --module ES2020
tsc questionSetShowToUser.ts --target ES2020 --module ES2020

mysql -u root -p < database/schema.sql
mysql -u root -p < database/data.sql


php -S 127.0.0.1:8006


//now checking all tables

in terminal:::

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
