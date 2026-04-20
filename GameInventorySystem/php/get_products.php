<?php
declare(strict_types=1); //STRICT TYPE CHECKING


//FETCH ALL PRODUCT RECORD FROM PRODUCT TABLE AND RETURN AS JSON TO FRONTEND
require_once __DIR__ . '/db_connect.php';

header('Content-Type: application/json; charset=utf-8');

$sql = '
    SELECT
        id,
        name,
        category,
        price,
        rarity
    FROM products
    ORDER BY id ASC
'; //WHOLE QUERY IN STRING FORMAT

$result = mysqli_query($conn, $sql); //RUNS SQL QUERY WITH DATABASE CONNECTION

if ($result === false) 
    {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch products.']);
        exit;
    }

$data = [];
while ($row = mysqli_fetch_assoc($result)) 
    {
        $data[] = $row;
    }

echo json_encode($data);