<!-- php -S localhost:8000 -->
<!-- http://localhost:8000/BasicCalculator.php  -->

<!DOCTYPE html>
<html>
<head>
    <title>Simple PHP Calculator</title>
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
            width: 300px;
            margin: auto;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #1a043b;
        }
        p {
            font-size: 24px;
            margin: 8px 0;
        }
    </style>
</head>

<body>

<div class="container">
    <h2>Basic PHP Calculator</h2>

<?php

function addnumbers($n1, $n2) {
    return $n1 + $n2;
}

function subtractnumbers($n1, $n2) {
    return $n1 - $n2;
}

function multiplynumbers($n1, $n2) 
{
    return $n1 * $n2;
}

function dividenumbers($n1, $n2) 
{
    if ($n2 != 0) 
    {
        return $n1 / $n2;
    } 
    else {
        return "Cannot divide by zero!";
    }
}

$a = 5;
$b = 4;
echo "<p>Numbers are: " . $a . " and " . $b . "</p>";
echo "<p>Addition: " . addnumbers($a, $b) . "</p>";
echo "<p>Subtraction: " . subtractnumbers($a, $b) . "</p>";
echo "<p>Multiplication: " . multiplynumbers($a, $b) . "</p>";
echo "<p>Division: " . dividenumbers($a, $b) . "</p>";

?>

</div>

</body>
</html>

