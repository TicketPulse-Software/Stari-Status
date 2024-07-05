<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}
include '../includes/header.php';
?>
<main>
    <h2 class="container text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">Admin Panel</h2>
    <div class="container">
        <a href="services.php" class="text-blue-500">Manage Services</a>
        <a href="incidents.php" class="text-blue-500">Manage Incidents</a>
    </div>
</main>
<?php include '../includes/footer.php'; ?>
