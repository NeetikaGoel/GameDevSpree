<?php
declare(strict_types=1);

require_once __DIR__ . '/dbConnect.php';
require_once __DIR__ . '/../backend/answerOption.php';

//to make path stable otherwise can give error new learning

class QuestionRepository
{
    public function getQuestions():array
    {
        $sql = '
            SELECT id,text,type
            FROM questions
            ORDER BY id ASC
        '; //WHOLE SQL QUERY IN STRING FORMAT

        global $conn;   ///////????????
        $result = mysqli_query($conn, $sql); //Runs SQL Query using DB Connection

        if ($result === false) 
            {
                //NO RESULT COMES
                http_response_code(500); //500 MEANS SERVER SIDE ERROR IS THERE
                echo json_encode(['error' => 'Failed to fetch questions!!']);
                exit;
            }

            //OTHERWISE OFC RESULT IS THERE AND WILL BE FETCHED
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) //GIVES EACH ROW AS ASSOCIATIVE ARRAY
            {
                $data[] = new Question((int)$row['id'], $row['text'], $row['type']);  ////????now got it
            }

        return $data;
    }
}


?>