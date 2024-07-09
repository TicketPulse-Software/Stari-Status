<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

include '../db.php';

$services = $pdo->query('SELECT * FROM services')->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $service_id = $_POST['service_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $status = $_POST['status'];
    $stmt = $pdo->prepare('INSERT INTO incidents (service_id, title, description, status) VALUES (?, ?, ?, ?)');
    $stmt->execute([$service_id, $title, $description, $status]);
    header('Location: incidents.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Incident - Admin Panel</title>
    <link href="../css/styles.css" rel="stylesheet">
    <link href="../vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h1 class="text-center my-4">Add Incident</h1>
    <form method="post">
        <div class="form-group">
            <label for="service_id">Service:</label>
            <select id="service_id" name="service_id" class="form-control" required>
                <?php foreach ($services as $service): ?>
                    <option value="<?= $service['id'] ?>"><?= htmlspecialchars($service['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="description">Description:</label>
            <textarea id="description" name="description" class="form-control" rows="5" required></textarea>
        </div>
        <div class="form-group">
            <label for="status">Status:</label>
            <select id="status" name="status" class="form-control" required>
                <option value="Investigating">Investigating</option>
                <option value="Updated">Updated</option>
                <option value="Monitoring">Monitoring</option>
                <option value="Resolved">Resolved</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Add Incident</button>
    </form>
</div>
<script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
