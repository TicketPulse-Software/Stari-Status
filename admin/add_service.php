<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

include '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $url = $_POST['url'];
    $stmt = $pdo->prepare('INSERT INTO services (name, url, status) VALUES (?, ?, "unknown")');
    $stmt->execute([$name, $url]);
    header('Location: index.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Service - Admin Panel</title>
    <link href="../css/styles.css" rel="stylesheet">
    <link href="../vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h1 class="text-center my-4">Add Service</h1>
    <form method="post">
        <div class="form-group">
            <label for="name">Service Name:</label>
            <input type="text" id="name" name="name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="url">Service URL:</label>
            <input type="url" id="url" name="url" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Add Service</button>
    </form>
</div>
<script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
