<?php
require "config/config.php";

session_unset(); //all variables values remove
session_destroy(); //whole session destroyed

header("Location: index.php"); //again bring to index file
exit();
?>