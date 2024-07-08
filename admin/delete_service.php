<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

include '../db.php';

$id = $_GET['id'];
$pdo->prepare('DELETE FROM incidents WHERE id = ?')->execute([$id]);
header('Location: incidents.php');
?>
