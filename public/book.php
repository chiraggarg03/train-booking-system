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

$train_id = isset($_GET['train_id']) ? intval($_GET['train_id']) : 0;
if ($train_id <= 0) {
    echo "Invalid train selected.";
    exit;
}

// Fetch train details
$stmt = $conn->prepare("SELECT * FROM trains WHERE id = ?");
$stmt->bind_param('i', $train_id);
$stmt->execute();
$train = $stmt->get_result()->fetch_assoc();

if (!$train) {
    echo "Train not found.";
    exit;
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $seats = intval($_POST['seats']);
    if ($seats < 1) {
        $error = "Please enter a valid number of seats.";
    } elseif ($seats > $train['total_seats']) {
        $error = "Not enough seats available.";
    } else {
        $user_id = $_SESSION['user_id'];

        $conn->begin_transaction();

        try {
            // Insert booking record
            $stmt = $conn->prepare("INSERT INTO bookings (user_id, train_id, seats) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $user_id, $train_id, $seats);
            if (!$stmt->execute()) {
                throw new Exception("Booking failed: " . $conn->error);
            }

            // Decrement available seats
            $stmt2 = $conn->prepare("UPDATE trains SET total_seats = total_seats - ? WHERE id = ? AND total_seats >= ?");
            $stmt2->bind_param("iii", $seats, $train_id, $seats);
            $stmt2->execute();
            if ($stmt2->affected_rows === 0) {
                throw new Exception("Not enough available seats.");
            }

            $conn->commit();

            $booking_id = $stmt->insert_id;
            header("Location: booking_success.php?booking_id=" . $booking_id);
            exit;

        } catch (Exception $e) {
            $conn->rollback();
            $error = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book Ticket - <?php echo htmlspecialchars($train['train_name']); ?></title>
</head>
<body>
    <h2>Book Ticket: <?php echo htmlspecialchars($train['train_name']); ?></h2>
    <p><strong>From:</strong> <?php echo htmlspecialchars($train['source']); ?></p>
    <p><strong>To:</strong> <?php echo htmlspecialchars($train['destination']); ?></p>
    <p><strong>Departure:</strong> <?php echo htmlspecialchars($train['depart_time']); ?></p>
    <p><strong>Arrival:</strong> <?php echo htmlspecialchars($train['arrival_time']); ?></p>
    <p><strong>Seats available:</strong> <?php echo htmlspecialchars($train['total_seats']); ?></p>

    <?php if ($error): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="post" action="">
        <label for="seats">Number of Seats:</label>
        <input type="number" id="seats" name="seats" min="1" max="<?php echo htmlspecialchars($train['total_seats']); ?>" value="1" required>
        <button type="submit">Book Now</button>
    </form>

    <br>
    <a href="trains.php">Back to Train Search</a>
</body>
</html>
