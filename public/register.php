<?php
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include '../includes/db.php';
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $name, $email, $password);

    if ($stmt->execute()) {
        $message = '<p class="success-message">Registration successful. <a href="login.php">Login</a></p>';
    } else {
        $message = '<p class="error-message">Registration failed: ' . htmlspecialchars($conn->error) . '</p>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Register - Railway Ticketing System</title>
    <link rel="stylesheet" href="../assets/style.css" />
</head>
<body>
    <div class="register-container">
        <h2>Register</h2>
        <form method="post" action="register.php" class="register-form">
            <input type="text" name="name" placeholder="Name" required class="input-text" />
            <input type="email" name="email" placeholder="Email" required class="input-text" />
            <input type="password" name="password" placeholder="Password" required class="input-text" />
            <button type="submit" class="btn submit-btn">Register</button>
        </form>

        <?php if ($message): ?>
            <?php echo $message; ?>
        <?php endif; ?>

        <p class="login-link">Already have an account? <a href="login.php">Login here</a>.</p>
    </div>
</body>
</html>
