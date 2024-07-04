<?php
require '../includes/auth.php';
logout();
header('Location: login.php');
exit();
?>
