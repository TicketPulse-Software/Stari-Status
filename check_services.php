<?php
require 'includes/db.php';
require 'includes/functions.php';

$services = $pdo->query("SELECT * FROM services")->fetchAll(PDO::FETCH_ASSOC);

foreach ($services as $service) {
    if ($service['status'] !== 'maintenance') {
        $status = checkServiceStatus($service['url']);
        logUptime($pdo, $service['id'], $status);
        $stmt = $pdo->prepare("UPDATE services SET status = :status, last_checked = NOW() WHERE id = :id");
        $stmt->execute(['status' => $status, 'id' => $service['id']]);
    }
}
?>
