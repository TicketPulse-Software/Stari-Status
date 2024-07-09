<?php
$host = 'localhost';
$db = 'ticketpu_status';
$user = 'ticketpu_status';
$pass = 'ticketpu_status';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}
?>
