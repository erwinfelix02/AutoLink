<?php
session_start();
session_unset();
session_destroy();
header("Location: /AutolinkWeb/Login/index.html"); 
exit();
?>

