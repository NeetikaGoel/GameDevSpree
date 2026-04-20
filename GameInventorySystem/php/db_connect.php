<?php

//CONNECT PHP TO MYSQL DATABASE
$host = "localhost"; //DB SHOULD ALSO RUN ON SAME MACHINE
$username = "root";  //MYSQL USERNAME
$password = "work"; //REAL PW
$database = "game_inventory_system"; //DB NAME


//NOW CREATING MYSQL OBJECT THAT WILL HELP IN CONNECTION
$conn = new mysqli($host, $username, $password, $database); 

if ($conn->connect_error) 
    {
        //IN CASE ERROR IS THERE
        die("Connection failed: " . $conn->connect_error);
        //STOP EXECUTION AND NOW SHOW ERROR
    }
?>