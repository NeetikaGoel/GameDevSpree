<?php

declare(strict_types=1);

//WILL HANDLE MAIN BACKEND SAVING LOGIC PROCESSING FOR THIS PROJECT

//WILL RECEIVE PERSON AND CART DATA FROM THE CHECKOUT FORM THERE IS FROM THE FRONTEND, CONNECT WITH DB AND THEN INSERT THOSE DATA IN THE DB

//IF FAILURE IN ANY STEP, JUST ROLLBACK ALL CHANGES AND KEEP DB CONSISTENT V IMP
require_once __DIR__ . '/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') //NO GET, ONLY POST
{
    http_response_code(405); //WRONG HTTP METHOD CODE
    exit('Method Not Allowed');
}


//TAKE ALL VARIABLES FROM FRONTEND
$fullName = isset($_POST['full_name']) ? trim((string) $_POST['full_name']) : '';

$email = isset($_POST['email']) ? trim((string) $_POST['email']) : '';

$phone = isset($_POST['phone']) ? trim((string) $_POST['phone']) : '';

$location = isset($_POST['location']) ? trim((string) $_POST['location']) : '';

$paymentMethod = isset($_POST['payment_method']) ? trim((string) $_POST['payment_method']) : '';


$cartJson = isset($_POST['cart_data']) ? (string) $_POST['cart_data'] : '';


//WHAT TO DO NOW FOR REQUIRED FIELDS AND USER JUST CANT SEE THEM
if ($fullName === '' || $email === '' || $phone === '' || $location === '' || $paymentMethod === '' || $cartJson === '') 
    {
        http_response_code(400); //BAD REQUEST IS 400
        exit('Missing required fields.');
    }

$cartItems = json_decode($cartJson, true); //JSON STRING TO PHP ARRAY DECODING WILL BE HERE

if (!is_array($cartItems) || count($cartItems) === 0) 
    {
        http_response_code(400); //NOTHING TO SAVE
        exit('Cart is empty!!');
    }

mysqli_begin_transaction($conn); //DB CONNECTION


//MAIN HANDLING NOW
try {
    $customerSql = 'INSERT INTO customers (full_name, email, phone, location) VALUES (?, ?, ?, ?)';

    $customerStmt = mysqli_prepare($conn, $customerSql);

    if ($customerStmt === false) 
        {
            throw new RuntimeException('Failed to prepare customer insert statement!!');
        }

    mysqli_stmt_bind_param($customerStmt, 'ssss', $fullName, $email, $phone, $location); //BIND 4 STRINGS TOGETHER

    mysqli_stmt_execute($customerStmt);

    $customerId = (int) mysqli_insert_id($conn);

    mysqli_stmt_close($customerStmt);


    //NOW DO TOTAL PRICE FOR THE CART PURCHASE
    $totalAmount = 0.0;
    foreach ($cartItems as $item) 
        {
            $price = isset($item['price']) ? (float) $item['price'] : 0.0;
            $quantity = isset($item['quantity']) ? (int) $item['quantity'] : 0;
            $totalAmount += $price * $quantity;
        }

    $orderSql = 'INSERT INTO orders (customer_id, total_amount, payment_method) VALUES (?, ?, ?)';

    $orderStmt = mysqli_prepare($conn, $orderSql);

    if ($orderStmt === false) 
        {
            throw new RuntimeException('Failed to prepare order insert statement!!');
        }

    mysqli_stmt_bind_param($orderStmt, 'ids', $customerId, $totalAmount, $paymentMethod);

    mysqli_stmt_execute($orderStmt);

    $orderId = (int) mysqli_insert_id($conn);

    mysqli_stmt_close($orderStmt);

    $itemSql = 'INSERT INTO order_items (order_id, product_id, quantity, item_price) VALUES (?, ?, ?, ?)';

    $itemStmt = mysqli_prepare($conn, $itemSql);

    if ($itemStmt === false) 
        {
            throw new RuntimeException('Failed to prepare order item insert statement!!');
        }

    foreach ($cartItems as $item) 
        {
            $productId = isset($item['id']) ? (int) $item['id'] : 0;

            $quantity = isset($item['quantity']) ? (int) $item['quantity'] : 0;

            $itemPrice = isset($item['price']) ? (float) $item['price'] : 0.0;

            mysqli_stmt_bind_param($itemStmt, 'iiid', $orderId, $productId, $quantity, $itemPrice);

            mysqli_stmt_execute($itemStmt);
        }

    mysqli_stmt_close($itemStmt);

    mysqli_commit($conn);

    header('Location: ../order_success.html');

    exit;
} 

catch (Throwable $exception) 
{
    mysqli_rollback($conn);
    http_response_code(500); //SERVER SIDE ERROR
    exit('Error while saving order: ' . $exception->getMessage());
}
