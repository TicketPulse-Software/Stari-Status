<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

include '../db.php';

$id = $_GET['id'];
$service = $pdo->query('SELECT maintenance_mode FROM services WHERE id = ' . $id)->fetch(PDO::FETCH_ASSOC);
$new_mode = $service['maintenance_mode'] ? 0 : 1;

$pdo->prepare('UPDATE services SET maintenance_mode = ? WHERE id = ?')->execute([$new_mode, $id]);
header('Location: index.php');
?>
