<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

include '../db.php';

$services = $pdo->query('SELECT * FROM services')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - Uptime Monitor</title>
    <link href="../css/styles.css" rel="stylesheet">
    <link href="../vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h1 class="text-center my-4">Admin Panel</h1>
    <a href="add_service.php" class="btn btn-primary mb-3">Add Service</a>
    <a href="incidents.php" class="btn btn-secondary mb-3">Manage Incidents</a>
    <a href="logout.php" class="btn btn-danger mb-3">Logout</a>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Service</th>
                <th>URL</th>
                <th>Maintenance Mode</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($services as $service): ?>
                <tr>
                    <td><?= htmlspecialchars($service['name']) ?></td>
                    <td><?= htmlspecialchars($service['url']) ?></td>
                    <td><?= $service['maintenance_mode'] ? 'Enabled' : 'Disabled' ?></td>
                    <td>
                        <a href="edit_service.php?id=<?= $service['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="delete_service.php?id=<?= $service['id'] ?>" class="btn btn-danger btn-sm">Delete</a>
                        <a href="toggle_maintenance.php?id=<?= $service['id'] ?>" class="btn btn-secondary btn-sm"><?= $service['maintenance_mode'] ? 'Disable' : 'Enable' ?> Maintenance</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
