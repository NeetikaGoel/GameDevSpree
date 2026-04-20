<?php

class Feedback
{
    //for storing session feedback and also to display correct msg on dashboard based on it only
    public function store($rating)
    {
        $_SESSION['feedback'] = $rating; //gets saved in session var
    }

    public function get()
    {
        return $_SESSION['feedback'] ?? null; //if we need to return whats teh value of it
    }

    public function msg()
    {
        //abt the msg to show on dashboard based on feedback
        if (!isset($_SESSION['feedback'])) //no feedback so must be 1st time
        {
            return "Welcome! First time here?"; //will be default
        }

        if ($_SESSION['feedback'] === "bad") //negative so we want better
        {
            return "We hope your experience is better this time!!";
        }

        if ($_SESSION['feedback'] === "good") //good so must be good again
        {
            return "Welcome back! We are glad to see you again!!";
        }

        if ($_SESSION['feedback'] === "okay") // atleast user come back so win
        {
            return "Welcome again! Thanks for coming back!!";
        }

        return "Welcome back!"; //yes yes default again
    }
}
?>