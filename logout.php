<!-- logout.php -->
<?php
session_start();
session_unset();
session_destroy();
header("Location: loginht.php");
exit();
?>