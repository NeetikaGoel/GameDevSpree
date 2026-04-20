<?php
declare(strict_types=1);


require_once __DIR__ . '/dbConnect.php';

class DBManager
{
    public function dbManage(string $sql): array
    {
        global $conn;
        $result = mysqli_query($conn, $sql);
        $rows = [];
        while ($row=mysqli_fetch_assoc($result))
            {
                $rows[]=$row;
            }

        return $rows;
    }
}

?>