<?php
declare(strict_types=1);


require_once __DIR__ . '/dbConnect.php';

//now this file will directly talk to db okayyyy
class DBManager
{
    //this was when we wanted all rows
    public function getAllRows(string $sql): array
    {
        global $conn;
        $result=mysqli_query($conn,$sql);
        $rows=[];
        while ($row=mysqli_fetch_assoc($result))
            {
                $rows[]=$row;
            }

        return $rows;
    }

    //now since we have parameters too so first prepare teh query fully then run
    public function getAllRowsPrepared(string $sql,string $types,array $params):array
    {
        global $conn;
        $query=$conn->prepare($sql);
        $query->bind_param($types,...$params); //connect types and params in the query 
        $query->execute(); //execute the command
        $result=$query->get_result(); //whatever result we get from query

        $rows=[];
        while ($row=mysqli_fetch_assoc($result)) //this mysqli fetch assoc gives 1 row at a time in associative array format which is key value type fomrat
            {
                $rows[]=$row;
            }

        $query->close(); //close it if work over, nice to have thing
        return $rows;
    }

    //now we just want 1 row like when quiz attempt thing use case
    public function getOneRowPrepared(string $sql,string $types,array $params):?array
    {
        return $this->getAttemptIdRow($sql,$types,$params);
    }


    //CHANGES FOR STATELESSNESS!!!!!!!!!!!!!!!

    //but now we dont want all rows but very specific row acc to quizAttemptId
    public function getAttemptIdRow(string $sql,string $types,array $params):?array //maybe array or maybe null //so u can return nullable arrayy
    {
        global $conn;
        $query=$conn->prepare($sql); //take sql line
        $query->bind_param($types,...$params); //types of values taht will be added into query and params the actual values that need to be binded with the query ONE BY ONE THATS WHY .... DOTS
        $query->execute();
        $result=$query->get_result();
        $row=mysqli_fetch_assoc($result);
        $query->close();
        if ($row===null) return null;
        return $row;
    }


    public function runQuery(string $sql,string $types,array $params):void //NO NEED TO RETURN ANYTHING JUST EXECUTE LIKE IF WE MARK ROW AS COMPLETE WHEN THAT SESSION GETS OVER!!
    {
        global $conn;
        $query=$conn->prepare($sql); //take sql line
        $query->bind_param($types,...$params); //types of values taht will be added into query and params the actual values that need to be binded with the query
        $query->execute();
        $query->close();
    }

    //now if want to insert some row like in case of new quiz attempt id
    public function insertAttemptIdRow(string $sql,string $types,array $params):int //maybe array or maybe null
    {
        global $conn;
        $query=$conn->prepare($sql); //take sql line
        $query->bind_param($types,...$params); //types of values taht will be added into query and params the actual values that need to be binded with the query
        $query->execute();
        $insertId=$conn->insert_id;
        //THIS INSERT ID IS GONNA ACT LIKE QUIZATTEMPT ID FOR US SO IT IS VEYR VERY IMPORTANT!!!!!!
        $query->close();
        return $insertId;
    }



}

?>