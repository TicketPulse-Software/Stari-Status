<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

include '../db.php';

$incidents = $pdo->query('SELECT incidents.*, services.name as service_name FROM incidents JOIN services ON incidents.service_id = services.id ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Incidents - Admin Panel</title>
    <link href="../css/styles.css" rel="stylesheet">
    <link href="../vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h1 class="text-center my-4">Manage Incidents</h1>
    <a href="add_incident.php" class="btn btn-primary mb-3">Add Incident</a>
    <a href="logout.php" class="btn btn-danger mb-3">Logout</a>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Service</th>
                <th>Title</th>
                <th>Description</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($incidents as $incident): ?>
                <tr>
                    <td><?= htmlspecialchars($incident['service_name']) ?></td>
                    <td><?= htmlspecialchars($incident['title']) ?></td>
                    <td><?= htmlspecialchars($incident['description']) ?></td>
                    <td><?= htmlspecialchars($incident['status']) ?></td>
                    <td><?= htmlspecialchars($incident['created_at']) ?></td>
                    <td>
                        <a href="edit_incident.php?id=<?= $incident['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="delete_incident.php?id=<?= $incident['id'] ?>" class="btn btn-danger btn-sm">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
