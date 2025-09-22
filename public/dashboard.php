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

// Cancel booking if requested
if (isset($_GET['cancel_id'])) {
    $booking_id = intval($_GET['cancel_id']);

    // Get seats and train_id for this booking
    $stmt = $conn->prepare("SELECT seats, train_id FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result) {
        $seats = $result['seats'];
        $train_id = $result['train_id'];

        // Begin transaction
        $conn->begin_transaction();

        try {
            // Delete booking
            $stmt_del = $conn->prepare("DELETE FROM bookings WHERE id = ? AND user_id = ?");
            $stmt_del->bind_param("ii", $booking_id, $user_id);
            if (!$stmt_del->execute()) {
                throw new Exception("Failed to delete booking.");
            }

            // Increment seats back to train
            $stmt_upd = $conn->prepare("UPDATE trains SET total_seats = total_seats + ? WHERE id = ?");
            $stmt_upd->bind_param("ii", $seats, $train_id);
            if (!$stmt_upd->execute()) {
                throw new Exception("Failed to update train seats.");
            }

            $conn->commit();
            echo "<p style='color:green;'>Booking cancelled and seats updated!</p>";
        } catch (Exception $e) {
            $conn->rollback();
            echo "<p style='color:red;'>{$e->getMessage()}</p>";
        }
    } else {
        echo "<p style='color:red;'>Booking not found or unauthorized.</p>";
    }
}


// Fetch user's bookings
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
    <a href="logout.php">Logout</a>
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
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['train_name']); ?></td>
                <td><?php echo htmlspecialchars($row['source']); ?></td>
                <td><?php echo htmlspecialchars($row['destination']); ?></td>
                <td><?php echo htmlspecialchars($row['depart_time']); ?></td>
                <td><?php echo htmlspecialchars($row['arrival_time']); ?></td>
                <td><?php echo htmlspecialchars($row['seats']); ?></td>
                <td>
                    <a href="dashboard.php?cancel_id=<?php echo $row['id']; ?>" onclick="return confirm('Cancel this booking?');">Cancel</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
