<?php
session_start();
session_destroy();
header("Location: technician_signin.php");
exit();
?>
