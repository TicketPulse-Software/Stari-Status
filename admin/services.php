<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $url = $_POST['url'];

    $stmt = $conn->prepare("INSERT INTO services (name, url) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $url);
    $stmt->execute();
}

$services = $conn->query("SELECT * FROM services");

include '../includes/header.php';
?>
<main>
    <div class="container">
        <h2>Add Service</h2>
        <form action="" method="post">
            <input type="text" name="name" placeholder="Service Name" required>
            <input type="url" name="url" placeholder="Service URL" required>
            <button type="submit">Add</button>
        </form>

        <h2>Manage Services</h2>
        <ul>
            <?php while ($service = $services->fetch_assoc()): ?>
                <li>
                    <?php echo $service['name'] . ' (' . $service['url'] . ')'; ?>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>
</main>
<?php include '../includes/footer.php'; ?>
