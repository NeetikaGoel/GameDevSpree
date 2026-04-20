<?php
require "config/config.php";
require "classes/User.php";
require "classes/Feedback.php";

header('Content-Type: application/json');
//FOR TELLING THAT RESPONSE GIVEN WILL BE IN JSON FORMAT


$user = new User(); //Creating user obj

if (!$user->loggedinornot()) //check whether logged in
    {
        http_response_code(401); //unauthorized access
        echo json_encode([
            "success" => false,
            "message" => "Unauthorized"
        ]); //for php array to convert to json text format
        exit();
    }

    //to do php to json and json to php
$data = json_decode(file_get_contents("php://input"), true);


$rating = $data['rating'] ?? ''; //is rating there

$allowedRatings = ['good', 'okay', 'bad']; //what r the options

if (!in_array($rating, $allowedRatings, true)) 
    {
        http_response_code(400); //not found rating
        echo json_encode([
            "success" => false,
            "message" => "Invalid rating"
        ]);
        exit();
    }

$feedback = new Feedback(); //create feedback obj
$feedback->store($rating); //rating store

echo json_encode([
    "success" => true,
    "message" => "Feedback saved successfully"
]);
?>