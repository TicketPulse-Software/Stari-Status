<?php
require '../includes/db.php';
require '../includes/auth.php';
redirectIfNotLoggedIn();

$configFile = '../includes/smtp_config.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $smtpHost = $_POST['smtp_host'];
    $smtpUser = $_POST['smtp_user'];
    $smtpPass = $_POST['smtp_pass'];
    $smtpPort = $_POST['smtp_port'];
    $smtpSecure = $_POST['smtp_secure'];

    $smtpConfig = [
        'host' => $smtpHost,
        'user' => $smtpUser,
        'pass' => $smtpPass,
        'port' => $smtpPort,
        'secure' => $smtpSecure,
    ];

    file_put_contents($configFile, json_encode($smtpConfig));
}

$smtpConfig = file_exists($configFile) ? json_decode(file_get_contents($configFile), true) : [];
?>
<!DOCTYPE html>
<html>
<head>
    <title>SMTP Config</title>
</head>
<body>
    <h1>SMTP Configuration</h1>
    <form method="POST" action="">
        <input type="text" name="smtp_host" placeholder="SMTP Host" value="<?php echo htmlspecialchars($smtpConfig['host'] ?? ''); ?>" required>
        <input type="text" name="smtp_user" placeholder="SMTP User" value="<?php echo htmlspecialchars($smtpConfig['user'] ?? ''); ?>" required>
        <input type="password" name="smtp_pass" placeholder="SMTP Password" value="<?php echo htmlspecialchars($smtpConfig['pass'] ?? ''); ?>" required>
        <input type="number" name="smtp_port" placeholder="SMTP Port" value="<?php echo htmlspecialchars($smtpConfig['port'] ?? ''); ?>" required>
        <input type="text" name="smtp_secure" placeholder="SMTP Secure (tls/ssl)" value="<?php echo htmlspecialchars($smtpConfig['secure'] ?? ''); ?>" required>
        <button type="submit">Save</button>
    </form>
</body>
</html>
