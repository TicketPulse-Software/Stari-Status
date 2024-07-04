<?php
require '../includes/db.php';
require '../includes/auth.php';
redirectIfNotLoggedIn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $status = $_POST['status'];
    $stmt = $pdo->prepare("UPDATE services SET status = :status WHERE id = :id");
    $stmt->execute(['status' => $status, 'id' => $id]);
}

$services = $pdo->query("SELECT * FROM services")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Maintenance Mode</title>
</head>
<body>
    <h1>Maintenance Mode</h1>
    <ul>
        <?php foreach ($services as $service): ?>
            <li>
                <?php echo htmlspecialchars($service['name']); ?>
                <form method="POST" action="" style="display: inline;">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($service['id']); ?>">
                    <select name="status">
                        <option value="online" <?php if ($service['status'] === 'online') echo 'selected'; ?>>Online</option>
                        <option value="degraded" <?php if ($service['status'] === 'degraded') echo 'selected'; ?>>Degraded</option>
                        <option value="maintenance" <?php if ($service['status'] === 'maintenance') echo 'selected'; ?>>Maintenance</option>
                        <option value="offline" <?php if ($service['status'] === 'offline') echo 'selected'; ?>>Offline</option>
                    </select>
                    <button type="submit">Update</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
