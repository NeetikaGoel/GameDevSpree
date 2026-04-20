<?php
session_start(); //start/resume a session


// need is coz everyhting will be saved in sessions hence to use superglobal variable i.e. $_SESSION - we need to use it

define("MAX_ATTEMPTS", 2);
define("TIME_WINDOW", 30);
define("NODE_API_BASE", "http://localhost:3000"); // base url for node backend so js knows where to send feedback that willbe received by user

?>