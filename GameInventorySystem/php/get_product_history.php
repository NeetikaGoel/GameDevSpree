<?php
declare(strict_types=1); //STRICT TYPING

//TAKE PURCHASE HISTORY FROM DATABASE AND RETURN IN JSON FORMAT
require_once __DIR__ . '/db_connect.php';

header('Content-Type: application/json; charset=utf-8');

$sql = '
    SELECT
        orders.id AS order_id,
        customers.full_name,
        customers.email,
        customers.phone,
        customers.location,
        orders.payment_method,
        orders.total_amount,
        orders.order_date
    FROM orders
    INNER JOIN customers ON orders.customer_id = customers.id
    ORDER BY orders.id DESC
'; //WHOLE SQL QUERY IN STRING FORMAT

$result = mysqli_query($conn, $sql); //Runs SQL Query using DB Connection

if ($result === false) 
    {
        //NO RESULT COMES
        http_response_code(500); //500 MEANS SERVER SIDE ERROR IS THERE
        echo json_encode(['error' => 'Failed to fetch purchase history.']);
        exit;
    }

    //OTHERWISE OFC RESULT IS THERE AND WILL BE FETCHED
$data = [];
while ($row = mysqli_fetch_assoc($result)) //GIVES EACH ROW AS ASSOCIATIVE ARRAY
    {
        $data[] = $row;
    }

echo json_encode($data); //CONVERTS FINAL ARRAY TO JSON AND WILL RETURN BACK