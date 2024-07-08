<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $service_id = $_POST['service_id'];
    $maintenance_mode = $_POST['maintenance_mode'];

    $stmt = $pdo->prepare('UPDATE services SET maintenance_mode = ? WHERE id = ?');
    $stmt->execute([$maintenance_mode, $service_id]);
    header('Location: admin/index.php');
}
?>
