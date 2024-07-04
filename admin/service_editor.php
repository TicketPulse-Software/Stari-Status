<?php
require '../includes/db.php';
require '../includes/auth.php';
redirectIfNotLoggedIn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $name = $_POST['name'];
        $url = $_POST['url'];
        $stmt = $pdo->prepare("INSERT INTO services (name, url) VALUES (:name, :url)");
        $stmt->execute(['name' => $name, 'url' => $url]);
    } elseif (isset($_POST['update'])) {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $url = $_POST['url'];
        $stmt = $pdo->prepare("UPDATE services SET name = :name, url = :url WHERE id = :id");
        $stmt->execute(['id' => $id, 'name' => $name, 'url' => $url]);
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM services WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }
}

$services = $pdo->query("SELECT * FROM services")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Services</title>
</head>
<body>
    <h1>Edit Services</h1>
    <form method="POST" action="">
        <input type="text" name="name" placeholder="Service Name" required>
        <input type="url" name="url" placeholder="Service URL" required>
        <button type="submit" name="add">Add Service</button>
    </form>
    <h2>Existing Services</h2>
    <ul>
        <?php foreach ($services as $service): ?>
            <li>
                <form method="POST" action="" style="display: inline;">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($service['id']); ?>">
                    <input type="text" name="name" value="<?php echo htmlspecialchars($service['name']); ?>" required>
                    <input type="url" name="url" value="<?php echo htmlspecialchars($service['url']); ?>" required>
                    <button type="submit" name="update">Update</button>
                    <button type="submit" name="delete">Delete</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
