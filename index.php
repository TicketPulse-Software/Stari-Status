<?php
include 'db.php';

$query = $pdo->query('SELECT * FROM services');
$services = $query->fetchAll(PDO::FETCH_ASSOC);

function getStatusClass($status) {
    return $status === 'up' ? 'status-up' : 'status-down';
}

function getUptimeBars($serviceId, $pdo) {
    $date = new DateTime();
    $date->modify('-90 days');
    $pastDate = $date->format('Y-m-d H:i:s');

    $stmt = $pdo->prepare('SELECT DATE(checked_at) as day, status FROM service_logs WHERE service_id = ? AND checked_at >= ? GROUP BY day');
    $stmt->execute([$serviceId, $pastDate]);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $bars = array_fill(0, 90, 'status-unknown');
    foreach ($logs as $log) {
        $dayIndex = (new DateTime($log['day']))->diff(new DateTime($pastDate))->days;
        $bars[$dayIndex] = $log['status'] === 'up' ? 'status-up' : 'status-down';
    }
    return $bars;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Uptime Monitor</title>
    <link href="css/styles.css" rel="stylesheet">
    <link href="vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<header class="bg-primary text-white text-center py-3">
    <h1>Service Status</h1>
</header>
<div class="container my-4">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Service</th>
                <th>Status</th>
                <th>Last Checked</th>
                <th>Uptime (Last 90 days)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($services as $service): ?>
                <tr>
                    <td><?= htmlspecialchars($service['name']) ?></td>
                    <td class="<?= getStatusClass($service['status']) ?>"><?= htmlspecialchars($service['status']) ?></td>
                    <td><?= htmlspecialchars($service['last_checked']) ?></td>
                    <td>
                        <div class="uptime-bars">
                            <?php foreach (getUptimeBars($service['id'], $pdo) as $barClass): ?>
                                <div class="uptime-bar <?= $barClass ?>"></div>
                            <?php endforeach; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<footer class="bg-dark text-white text-center py-3">
    Powered by TicketPulse Software | Stari-Status
</footer>
<script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
