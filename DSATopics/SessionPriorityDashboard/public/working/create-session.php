<?php

//BACKEND API FOR CREATION OF SESSION


declare(strict_types=1); //for strict type checking

require_once __DIR__ . '/../../php/storage.php'; //NEED THIS FILE , DIR WILL HELP MAKE PHP AS MAIN DIR FOR THIS FILE

header('Content-Type: application/json'); //content type will be json file from http response

// create unique session ID for this user
$sessionId = uniqid('session_', true); //will generate unique id itself - is a built in function already
//session_ is a prefix, true will make id more unique

// save empty session file
createSessionFile($sessionId); //create json file for each session

// return response for it
echo json_encode([
    'success' => true,
    'sessionId' => $sessionId
]); //outputs json to browser frontend

?>