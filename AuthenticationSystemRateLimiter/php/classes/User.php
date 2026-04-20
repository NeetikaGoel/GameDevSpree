<?php

class User
{

    //will be for user operations in a session such as login etc

    public function login($email) //email will be passed from login.php - will help user to login

    {
        $_SESSION['useremail'] = $email; //store email in session var
        //will mark user as logged in, can be used later as well

        if (!isset($_SESSION['logincount'])) //check logincount created before ? if not will create one otherwise will increment
        {
            $_SESSION['logincount'] = 1;
        } 
        else {
            $_SESSION['logincount']++; //only successful logins ofc
        }
    }

    public function loggedinornot()
    {
        return isset($_SESSION['useremail']) && !empty($_SESSION['useremail']); //return true if email exists of user and also session var exists
        //will prevent any unauthenticated accesss
    }

    public function getUserEmail()
    {
        return $_SESSION['useremail'] ?? null; //returns now user email detail
    }

    public function logout()
    {
        //will destroy user session!!!
        session_unset(); //removal of values of session vars
        session_destroy();
    }
}
?>