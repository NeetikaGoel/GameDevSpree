<!-- php -S localhost:8001
http://localhost:8001/Calculatorwithuserinput.php -->


<!DOCTYPE html>
<html>
<head>
    <title>User Input Calculator</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            text-align: center;
            padding: 50px;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 320px;
            margin: auto;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        input {
            width: 90%;
            padding: 8px;
            margin: 8px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            padding: 10px 15px;
            background: #1100ff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background: #b32d00;
        }
        p {
            font-size: 20px;
            margin: 6px 0;
        }
    </style>
</head>

<body>

<div class="container">
    <h2>User Input Calculator</h2>

    <!-- FORM TO SUBMIT NUMS -->
    <form method="post">
        <input type="number" name="num1" placeholder="Enter first number!" required><br>
        <input type="number" name="num2" placeholder="Enter second number!" required><br>
        <button type="submit">Calculate -></button>
    </form>

<?php

function add($a, $b) 
{
    return $a+$b;
}

function subtract($a, $b) 
{
    return $a-$b;
}

function multiply($a, $b) 
{
    return $a*$b;
}

function divide($a, $b) 
{
    return ($b!= 0) ? $a/$b : "Cannot divide by zero!!!";
}

// CHECK IF FORM IS SUBMITTED
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $a = $_POST['num1'];
    $b = $_POST['num2'];

    echo "<h3>Results:</h3>";
    echo "<p>Numbers are: " . $a . " and " . $b . "</p>";
    echo "<p>Addition: " . add($a, $b) . "</p>";
    echo "<p>Subtraction: " . subtract($a, $b) . "</p>";
    echo "<p>Multiplication: " . multiply($a, $b) . "</p>";
    echo "<p>Division: " . divide($a, $b) . "</p>";
}

?>

</div>

</body>
</html>