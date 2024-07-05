<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['loggedin'] = true;
            $_SESSION['phone'] = $user['phone'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid password';
        }
    } else {
        $error = 'No user found';
    }
}

$conn->close();
?>

<?php include '../includes/header.php'; ?>
<main>
    <div class="container">
        <form action="" method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <?php if (isset($error)) echo '<p>' . $error . '</p>'; ?>
    </div>
</main>
<?php include '../includes/footer.php'; ?>

