<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include '../includes/db.php';

// Validate booking ID
if (!isset($_GET['booking_id']) || !is_numeric($_GET['booking_id'])) {
    echo "Invalid booking.";
    exit;
}

$booking_id = intval($_GET['booking_id']);
$user_id = $_SESSION['user_id'];

// Fetch booking and train details for this user and booking id
$sql = "SELECT bookings.seats, bookings.booking_time, trains.train_name, trains.source, trains.destination, trains.depart_time, trains.arrival_time
        FROM bookings
        JOIN trains ON bookings.train_id = trains.id
        WHERE bookings.id = ? AND bookings.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Booking not found.";
    exit;
}

$booking = $result->fetch_assoc();

// Redirect to dashboard after 10 seconds
header("refresh:5;url=dashboard.php");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Booking Successful</title>
</head>
<body>
    <h2>Booking Successful!</h2>
    <p>Thank you for your booking. Here is your booking summary:</p>
    <table border="1" cellpadding="8">
        <tr><th>Train Name</th><td><?php echo htmlspecialchars($booking['train_name']); ?></td></tr>
        <tr><th>From</th><td><?php echo htmlspecialchars($booking['source']); ?></td></tr>
        <tr><th>To</th><td><?php echo htmlspecialchars($booking['destination']); ?></td></tr>
        <tr><th>Departure</th><td><?php echo htmlspecialchars($booking['depart_time']); ?></td></tr>
        <tr><th>Arrival</th><td><?php echo htmlspecialchars($booking['arrival_time']); ?></td></tr>
        <tr><th>Seats Booked</th><td><?php echo htmlspecialchars($booking['seats']); ?></td></tr>
        <tr><th>Booking Time</th><td><?php echo htmlspecialchars($booking['booking_time']); ?></td></tr>
    </table>

    <p>You will be redirected to your dashboard shortly.</p>
    <p>If you are not redirected, <a href="dashboard.php">click here</a>.</p>
</body>
</html>
