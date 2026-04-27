Lets see how to make it work now

Create question set api

1. load questions list from DB with pagination (we will use cursor pagination coz its scalable hehe)
2. admin selects questions with checkboxes 
3. admin enters:
->  question set name
->  question count target can be decided directly by no of questions it is selecting hehe
4. backend will create that config
5. backend will store the same secret key that it stored for the rest of the game configs hehe

 !!!!!! backend does not expose secret key !!!!!!!!


Edit question set api

1. load existing configs from db that r present there
2. admin selects one config that he wants to update
3. admin will see what??:: 
-> config name
-> question count target
-> selected question ids
-> active/inactive state of that game config
4. admin can change what?? lets see:
-> question count target
-> selected questions
-> whether this config should become active
-> he should also can see what questions are part of that game config and then all questions that is same as create question set thing so he can again choose through checkbox feature which to select and which to deselect and ques list order will then should be automatically created based on what the admin selected and he could also update the game config name hehe


Now how to do this active config selection

1. only one current config should drive quiz load
2. admin can make one config “current” by making it active , we can put that toggle thing to make it active or inactive on frontend to make it look nice hehe
3. quiz load should read that active config instead of fixed default_quiz and then work upon that '

OMG SO MUCH WORK NOW
SIMPLE QUESTION ADD CHANGES WILL NOT WORK HERE GIRLLL
GET TO WORK!!!!!!!

SERIOUS SERIOUS!!!



how to run:::::

mysql -u root -p < database/schema.sql
mysql -u root -p < database/data.sql
mysql -u root -p < database/updatedb.sql



//so added isactive, createdat, updatedat cols in the game_configs file for this update
