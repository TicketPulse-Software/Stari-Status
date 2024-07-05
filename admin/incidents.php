<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $description = $_POST['description'];

    $stmt = $conn->prepare("INSERT INTO incidents (description) VALUES (?)");
    $stmt->bind_param("s", $description);
    $stmt->execute();
}

$incidents = $conn->query("SELECT * FROM incidents ORDER BY date DESC");

include '../includes/header.php';
?>
<main>
    <div class="container">
        <h2>Add Incident</h2>
        <form action="" method="post">
            <textarea name="description" placeholder="Description" required></textarea>
            <button type="submit">Add</button>
        </form>

        <h2>Manage Incidents</h2>
        <ul>
            <?php while ($incident = $incidents->fetch_assoc()): ?>
                <li>
                    <?php echo date('M d, Y', strtotime($incident['date'])) . ': ' . $incident['description']; ?>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>
</main>
<?php include '../includes/footer.php'; ?>
