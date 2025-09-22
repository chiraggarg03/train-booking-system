<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include '../includes/db.php';
$user_id = $_SESSION['user_id'];

$sql = "SELECT bookings.*, trains.train_name, trains.source, trains.destination, trains.depart_time, trains.arrival_time
        FROM bookings
        JOIN trains ON bookings.train_id = trains.id
        WHERE bookings.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Bookings</title>
</head>
<body>
    <h2>My Bookings</h2>
    <a href="trains.php">Book a New Ticket</a>
    <br><br>
    <table border="1" cellpadding="8">
        <tr>
            <th>Train Name</th>
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
                <td><?php echo htmlspecialchars($row['seats']); ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
