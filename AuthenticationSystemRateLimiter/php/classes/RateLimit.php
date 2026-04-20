<?php

class RateLimit
{
    // for limiting how many login attempts are allowed in a time window

    // will return [true,0] if allowed
    // else [false, timeleft for trying again] if not allowed

    public function checklim()
    {
        if (!isset($_SESSION['starttime']) || !isset($_SESSION['count'])) 
            //in case ratelimit not started even, then initialize
            {
                $_SESSION['starttime'] = time(); //marking curr time for this attempt
                $_SESSION['count'] = 1; //first attempt count is set here
                return [true, 0]; //allow true, no wait required
            }

        $currtime = time(); //whats the curr time???
        $gonetime = $currtime - $_SESSION['starttime']; //how much timeleft for next try

        if ($gonetime > TIME_WINDOW) //check and then reset ratelimit
            {
                $_SESSION['starttime'] = time(); //currtime
                $_SESSION['count'] = 1; //first attempt
                return [true, 0]; //allowed
            }

        //here comes else that we r still in time window
        $_SESSION['count']++; //if still in time window, count of login in session var will increase

        if ($_SESSION['count'] > MAX_ATTEMPTS) //if max attempts reached in time window
            {
                $lefttime = TIME_WINDOW - $gonetime; //lefttime since it is to be returned with false
                return [false, $lefttime];
            }

        return [true, 0]; //otherwise allowed since count still within limit
    }

    public function reset()
    {
        unset($_SESSION['starttime'], $_SESSION['count']); //reset all session vars if user gets successfullly logged in
    }
}
?>