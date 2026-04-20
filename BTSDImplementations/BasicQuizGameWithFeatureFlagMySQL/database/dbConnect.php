<?php


declare(strict_types=1);


mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);  //so this line will turn mysql errors into exceptions and return it gracefully and not silently which is not nice


//errors occur at runtime and disrupt flow coz occur by env/system
//exceptions we can handle nicely coz occur in code and we can correct it

//CONNECT PHP TO MYSQL DATABASE
$host = "localhost"; //DB SHOULD ALSO RUN ON SAME MACHINE
$username = "root";  //MYSQL USERNAME
$password = "work"; //REAL PW
$database = "quizGame"; //DB NAME

try {
    $conn = new mysqli($host, $username, $password, $database);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $exception) {
    die('Database connection failed: ' . $exception->getMessage());
}


?>