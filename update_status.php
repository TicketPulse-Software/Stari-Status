<?php
include 'db.php';

function checkService($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $httpCode >= 200 && $httpCode < 400 ? 'up' : 'down';
}

$services = $pdo->query('SELECT * FROM services')->fetchAll(PDO::FETCH_ASSOC);

foreach ($services as $service) {
    if ($service['maintenance_mode']) {
        continue;
    }

    $status = checkService($service['url']);
    $stmt = $pdo->prepare('UPDATE services SET status = ?, last_checked = NOW() WHERE id = ?');
    $stmt->execute([$status, $service['id']]);
    $stmt = $pdo->prepare('INSERT INTO service_logs (service_id, status) VALUES (?, ?)');
    $stmt->execute([$service['id'], $status]);
}
?>

