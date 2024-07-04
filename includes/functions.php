<?php
function checkServiceStatus($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($httpCode >= 200 && $httpCode < 300) ? 'online' : 'offline';
}

function logUptime($pdo, $service_id, $status) {
    $stmt = $pdo->prepare("INSERT INTO uptimes (service_id, status) VALUES (:service_id, :status)");
    $stmt->execute(['service_id' => $service_id, 'status' => $status]);
}

function getUptime($pdo, $service_id, $days = 90) {
    $stmt = $pdo->prepare("SELECT * FROM uptimes WHERE service_id = :service_id AND checked_at > NOW() - INTERVAL :days DAY");
    $stmt->execute(['service_id' => $service_id, 'days' => $days]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addIncident($pdo, $service_id, $description) {
    $stmt = $pdo->prepare("INSERT INTO incidents (service_id, description) VALUES (:service_id, :description)");
    $stmt->execute(['service_id' => $service_id, 'description' => $description]);
}

function resolveIncident($pdo, $incident_id) {
    $stmt = $pdo->prepare("UPDATE incidents SET status = 'resolved' WHERE id = :id");
    $stmt->execute(['id' => $incident_id]);
}

function getOpenIncidents($pdo) {
    $stmt = $pdo->query("SELECT * FROM incidents WHERE status = 'open'");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
