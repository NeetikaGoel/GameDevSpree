<?php
//for actual login logic purpose
require "config/config.php";
require "classes/RateLimit.php";
require "classes/User.php";

$ratelimit = new RateLimit(); //new obj to validate attempts
$user = new User(); //new user to login

if ($_SERVER['REQUEST_METHOD'] !== 'POST') 
    {
        //so no get access is there 
        // default access get so will be redirected to index file
        header("Location: index.php");
        exit();
    }

list($permit, $timeleft) = $ratelimit->checklim(); //get answers to variables in list form

if (!$permit) 
    {
        //not allowed attempts problem
        $_SESSION['error'] = "Too many attempts. Try again in {$timeleft} seconds!"; //store error in session var
        header("Location: index.php"); //remain on same pg
        exit(); //stop script
    }

$email = trim($_POST['email'] ?? ''); //taking email from form
$password = trim($_POST['password'] ?? ''); //taking pw from form

if ($email === "test@gmail.com" && $password === "1234") //just for DEMO purpose hardcoded
    {
        $user->login($email); //correct credentials-so login
        $ratelimit->reset(); //all ratelimit resets
        header("Location: dashboard.php"); //go to dashboard
        exit(); //stop here
    } 
    else 
        {
            $_SESSION['error'] = "Invalid credentials."; //save this error now in session var
            header("Location: index.php"); //remain on same pg
            exit(); //stop here
        }
?>