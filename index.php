<?php
require '/includes/db.php';
require '/includes/functions.php';

$services = $pdo->query("SELECT * FROM services")->fetchAll(PDO::FETCH_ASSOC);
$incidents = getOpenIncidents($pdo);

foreach ($services as &$service) {
    $service['uptime'] = getUptime($pdo, $service['id']);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Status Page</title>
</head>
<body>
    <h1>Service Status</h1>
    <?php foreach ($services as $service): ?>
        <div>
            <h2><?php echo htmlspecialchars($service['name']); ?></h2>
            <p>Status: <?php echo htmlspecialchars($service['status']); ?></p>
            <p>Uptime for last 90 days: <?php echo count(array_filter($service['uptime'], fn($u) => $u['status'] === 'online')) / count($service['uptime']) * 100; ?>%</p>
        </div>
    <?php endforeach; ?>
    <h1>Open Incidents</h1>
    <?php foreach ($incidents as $incident): ?>
        <div>
            <h2><?php echo htmlspecialchars($incident['description']); ?></h2>
            <p>Status: <?php echo htmlspecialchars($incident['status']); ?></p>
            <p>Created at: <?php echo htmlspecialchars($incident['created_at']); ?></p>
        </div>
    <?php endforeach; ?>
</body>
</html>
