<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Only allow logged-in users
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include '../includes/db.php';

$train_id = isset($_GET['train_id']) ? intval($_GET['train_id']) : 0;
if ($train_id <= 0) {
    echo "Invalid train selected."; exit;
}

// Fetch train details
$stmt = $conn->prepare("SELECT * FROM trains WHERE id = ?");
$stmt->bind_param('i', $train_id);
$stmt->execute();
$train = $stmt->get_result()->fetch_assoc();

if (!$train) {
    echo "Train not found."; exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $seats = intval($_POST['seats']);
    if ($seats < 1 || $seats > $train['total_seats']) {
        $error = "Please enter a valid number of seats.";
    } else {
        // Optionally: Check for already booked seats...
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare(
            "INSERT INTO bookings (user_id, train_id, seats) VALUES (?, ?, ?)"
        );
        $stmt->bind_param("iii", $user_id, $train_id, $seats);
        if ($stmt->execute()) {
            $success = "Booking successful!";
        } else {
            $error = "Booking failed: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Book Ticket</title></head>
<body>
    <h2>Book Ticket: <?php echo htmlspecialchars($train['train_name']); ?></h2>
    <p>From: <?php echo htmlspecialchars($train['source']); ?></p>
    <p>To: <?php echo htmlspecialchars($train['destination']); ?></p>
    <p>Departure: <?php echo htmlspecialchars($train['depart_time']); ?></p>
    <p>Arrival: <?php echo htmlspecialchars($train['arrival_time']); ?></p>
    <p>Seats available: <?php echo htmlspecialchars($train['total_seats']); ?></p>
    <br>
    <?php if (!empty($error)) { echo "<p style='color:red;'>$error</p>"; } ?>
    <?php if (!empty($success)) { echo "<p style='color:green;'>$success</p>"; } ?>
    <form method="post" action="">
        <label>How many seats?</label>
        <input type="number" name="seats" min="1" max="<?php echo htmlspecialchars($train['total_seats']); ?>" value="1" required>
        <button type="submit">Book Now</button>
    </form>
    <br>
    <a href="trains.php">Back to Train Search</a>
</body>
</html>
