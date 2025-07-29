<form method="post" action="register.php">
  <input type="text" name="name" placeholder="Name" required>
  <input type="email" name="email" placeholder="Email" required>
  <input type="password" name="password" placeholder="Password" required>
  <button type="submit">Register</button>
</form>
<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  include '../includes/db.php';
  $name = $_POST['name'];
  $email = $_POST['email'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
  $sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("sss", $name, $email, $password);
  if ($stmt->execute()) {
    echo "Registration successful. <a href='login.php'>Login</a>";
  } else {
    echo "Registration failed: " . $conn->error;
  }
}
?>
