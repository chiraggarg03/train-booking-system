<form method="post" action="login.php">
  <input type="email" name="email" placeholder="Email" required>
  <input type="password" name="password" placeholder="Password" required>
  <button type="submit">Login</button>
</form>
<?php
session_start();
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
    header("Location: dashboard.php");
    exit;
  } else {
    echo "Login failed! Please check your credentials.";
  }
}
?>
