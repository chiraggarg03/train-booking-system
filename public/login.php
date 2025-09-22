<?php
session_start();

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin.php");
    } else {
        header("Location: dashboard.php");
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include '../includes/db.php';
    $email = $_POST['email'];
    $password = $_POST['password'];
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    if ($result && password_verify($password, $result['password'])) {
        $_SESSION['user_id'] = $result['id'];
        $_SESSION['role'] = $result['role'];
        if ($result['role'] === 'admin') {
            header("Location: admin.php");
        } else {
            header("Location: dashboard.php");
        }
        exit;
    } else {
        $error = "Login failed! Please check your credentials.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Login - Railway Ticketing System</title>
    <link rel="stylesheet" href="../assets/style.css" />
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <form method="post" action="login.php" class="login-form">
            <input type="email" name="email" placeholder="Email" required autofocus class="input-text" />
            <input type="password" name="password" placeholder="Password" required class="input-text" />
            <button type="submit" class="btn submit-btn">Login</button>
        </form>
        <?php if ($error): ?>
            <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <p class="register-link">Don't have an account? <a href="register.php">Register here</a>.</p>
    </div>
</body>
</html>
