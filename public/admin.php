<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Only allow admins
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo "Access denied. Only admins allowed.";
    exit;
}

include '../includes/db.php';

// Handle add train form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sql = "INSERT INTO trains (train_name, source, destination, depart_time, arrival_time, total_seats)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssssi",
        $_POST['train_name'],
        $_POST['source'],
        $_POST['destination'],
        $_POST['depart_time'],
        $_POST['arrival_time'],
        $_POST['total_seats']
    );
    if ($stmt->execute()) {
        echo "<p style='color:green;'>Train added successfully.</p>";
    } else {
        echo "<p style='color:red;'>Failed to add train: " . $conn->error . "</p>";
    }
}

// Fetch all trains
$result = $conn->query("SELECT * FROM trains");

?>

<!DOCTYPE html>
<html>
<head><title>Admin Panel: Manage Trains</title></head>
<body>
    <a href="logout.php">Logout</a>

    <h2>Admin Panel: Manage Trains</h2>

    <h3>Add New Train</h3>
    <form method="post" action="">
        <input name="train_name" placeholder="Train Name" required>
        <input name="source" placeholder="Source" required>
        <input name="destination" placeholder="Destination" required>
        <input name="depart_time" type="datetime-local" required>
        <input name="arrival_time" type="datetime-local" required>
        <input name="total_seats" type="number" min="1" required>
        <button type="submit">Add Train</button>
    </form>

    <h3>All Trains</h3>
    <table border="1" cellpadding="8">
        <tr>
            <th>Name</th>
            <th>Source</th>
            <th>Destination</th>
            <th>Departure</th>
            <th>Arrival</th>
            <th>Seats</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['train_name']); ?></td>
            <td><?php echo htmlspecialchars($row['source']); ?></td>
            <td><?php echo htmlspecialchars($row['destination']); ?></td>
            <td><?php echo htmlspecialchars($row['depart_time']); ?></td>
            <td><?php echo htmlspecialchars($row['arrival_time']); ?></td>
            <td><?php echo htmlspecialchars($row['total_seats']); ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
