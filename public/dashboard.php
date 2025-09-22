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

// Booking cancellation with seat increment
if (isset($_GET['cancel_id'])) {
    $booking_id = intval($_GET['cancel_id']);

    // Get booking details for seat count and train ID
    $stmt = $conn->prepare("SELECT seats, train_id FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result) {
        $seats = $result['seats'];
        $train_id = $result['train_id'];

        $conn->begin_transaction();

        try {
            // Delete the booking
            $stmt_del = $conn->prepare("DELETE FROM bookings WHERE id = ? AND user_id = ?");
            $stmt_del->bind_param("ii", $booking_id, $user_id);
            if (!$stmt_del->execute()) {
                throw new Exception("Failed to cancel booking.");
            }

            // Increment the available seats for train
            $stmt_upd = $conn->prepare("UPDATE trains SET total_seats = total_seats + ? WHERE id = ?");
            $stmt_upd->bind_param("ii", $seats, $train_id);
            if (!$stmt_upd->execute()) {
                throw new Exception("Failed to update seat availability.");
            }

            $conn->commit();
            $message = "Booking cancelled successfully.";
        } catch (Exception $e) {
            $conn->rollback();
            $message = "Error cancelling booking: " . $e->getMessage();
        }
    } else {
        $message = "Booking not found or unauthorized.";
    }
}

// Fetch all bookings of the logged-in user
$sql = "SELECT bookings.*, trains.train_name, trains.source, trains.destination, trains.depart_time, trains.arrival_time
        FROM bookings
        JOIN trains ON bookings.train_id = trains.id
        WHERE bookings.user_id = ?
        ORDER BY bookings.booking_time DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bookings = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Bookings - Dashboard</title>
</head>
<body>
    <h2>My Bookings</h2>

    <?php if (!empty($message)): ?>
        <p style="color:<?php echo strpos($message, 'Error') === false ? 'green' : 'red'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <p><a href="admin.php">Go to Admin Panel</a></p>
    <?php endif; ?>

    <p>
      <a href="trains.php">Book a New Ticket</a> |
      <a href="logout.php">Logout</a>
    </p>

    <?php if ($bookings->num_rows === 0): ?>
        <p>You have no bookings yet.</p>
    <?php else: ?>
        <table border="1" cellpadding="8">
            <tr>
                <th>Train Name</th>
                <th>Source</th>
                <th>Destination</th>
                <th>Departure</th>
                <th>Arrival</th>
                <th>Seats</th>
                <th>Booking Time</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $bookings->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['train_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['source']); ?></td>
                    <td><?php echo htmlspecialchars($row['destination']); ?></td>
                    <td><?php echo htmlspecialchars($row['depart_time']); ?></td>
                    <td><?php echo htmlspecialchars($row['arrival_time']); ?></td>
                    <td><?php echo htmlspecialchars($row['seats']); ?></td>
                    <td><?php echo htmlspecialchars($row['booking_time']); ?></td>
                    <td>
                        <a href="dashboard.php?cancel_id=<?php echo $row['id']; ?>"
                           onclick="return confirm('Are you sure you want to cancel this booking?');">
                            Cancel
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php endif; ?>

</body>
</html>
