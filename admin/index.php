<?php
require '../includes/auth.php';
redirectIfNotLoggedIn();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
</head>
<body>
    <h1>Admin Panel</h1>
    <ul>
        <li><a href="service_editor.php">Edit Services</a></li>
        <li><a href="maintenance_mode.php">Maintenance Mode</a></li>
        <li><a href="smtp_config.php">SMTP Config</a></li>
        <?php if (isAdmin()): ?>
            <li><a href="user_management.php">User Management</a></li>
        <?php endif; ?>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</body>
</html>
