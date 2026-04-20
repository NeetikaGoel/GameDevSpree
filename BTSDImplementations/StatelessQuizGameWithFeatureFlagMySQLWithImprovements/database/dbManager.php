<?php
declare(strict_types=1);


require_once __DIR__ . '/dbConnect.php';

class DBManager
{
    //this was when we wanted all rows
    public function getAllRows(string $sql): array
    {
        global $conn;
        $result=mysqli_query($conn, $sql);
        $rows=[];
        while ($row=mysqli_fetch_assoc($result))
            {
                $rows[]=$row;
            }

        return $rows;
    }


    //CHANGES FOR STATELESSNESS!!!!!!!!!!!!!!!

    //but now we dont want all rows but very specific row acc to quizAttemptId
    public function getAttemptIdRow(string $sql, string $types, array $params):?array //maybe array or maybe null //so u can return nullable arrayy
    {
        global $conn;
        $query=$conn->prepare($sql); //take sql line
        $query->bind_param($types, ...$params); //types of values taht will be added into query and params the actual values that need to be binded with the query ONE BY ONE THATS WHY .... DOTS
        $query->execute();
        $result=$query->get_result();
        $row=mysqli_fetch_assoc($result);
        $query->close();
        if ($row===null) return null;
        return $row;
    }


    public function runQuery(string $sql, string $types, array $params):void //NO NEED TO RETURN ANYTHING JUST EXECUTE LIKE IF WE MARK ROW AS COMPLETE WHEN THAT SESSION GETS OVER!!
    {
        global $conn;
        $query=$conn->prepare($sql); //take sql line
        $query->bind_param($types, ...$params); //types of values taht will be added into query and params the actual values that need to be binded with the query
        $query->execute();
        $query->close();
    }


    public function insertAttemptIdRow(string $sql, string $types, array $params):int //maybe array or maybe null
    {
        global $conn;
        $query=$conn->prepare($sql); //take sql line
        $query->bind_param($types, ...$params); //types of values taht will be added into query and params the actual values that need to be binded with the query
        $query->execute();
        $insertId=$conn->insert_id;
        //THIS INSERT ID IS GONNA ACT LIKE QUIZATTEMPT ID FOR US SO IT IS VEYR VERY IMPORTANT!!!!!!
        $query->close();
        return $insertId;
    }



}

?>