<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo "Access denied. Only admins allowed.";
    exit;
}

include '../includes/db.php';

$message = '';
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
        $message = '<p class="success-message">Train added successfully.</p>';
    } else {
        $message = '<p class="error-message">Failed to add train: ' . htmlspecialchars($conn->error) . '</p>';
    }
}

// Fetch all trains
$result = $conn->query("SELECT * FROM trains");

// Fetch all bookings with user and train info
$bookings_sql = "SELECT bookings.id AS booking_id, users.name AS user_name, users.email AS user_email, 
                 trains.train_name, trains.source, trains.destination, 
                 bookings.seats, bookings.booking_time
                 FROM bookings
                 JOIN users ON bookings.user_id = users.id
                 JOIN trains ON bookings.train_id = trains.id
                 ORDER BY bookings.booking_time DESC";
$bookings_result = $conn->query($bookings_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Admin Panel: Manage Trains</title>
    <link rel="stylesheet" href="../assets/style.css" />
</head>
<body>
    <nav>
        <a href="logout.php" class="btn logout">Logout</a>
    </nav>
    <h2>Admin Panel: Manage Trains</h2>

    <?php echo $message; ?>

    <section class="train-form-section">
        <h3>Add New Train</h3>
        <form method="post" action="" class="admin-form">
            <input name="train_name" type="text" placeholder="Train Name" required class="input-text" />
            <input name="source" type="text" placeholder="Source" required class="input-text" />
            <input name="destination" type="text" placeholder="Destination" required class="input-text" />
            <input name="depart_time" type="datetime-local" required class="input-datetime" />
            <input name="arrival_time" type="datetime-local" required class="input-datetime" />
            <input name="total_seats" placeholder="Total seats" type="number" min="1" required class="input-number" />
            <button type="submit" class="btn submit-btn">Add Train</button>
        </form>
    </section>

    <section class="train-list-section">
        <h3>All Trains</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Source</th>
                    <th>Destination</th>
                    <th>Departure</th>
                    <th>Arrival</th>
                    <th>Seats</th>
                </tr>
            </thead>
            <tbody>
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
            </tbody>
        </table>
    </section>

    <section class="booking-list-section">
        <h3>All Bookings</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>User Name</th>
                    <th>User Email</th>
                    <th>Train Name</th>
                    <th>Source</th>
                    <th>Destination</th>
                    <th>Seats</th>
                    <th>Booking Time</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $bookings_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['booking_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['user_email']); ?></td>
                    <td><?php echo htmlspecialchars($row['train_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['source']); ?></td>
                    <td><?php echo htmlspecialchars($row['destination']); ?></td>
                    <td><?php echo htmlspecialchars($row['seats']); ?></td>
                    <td><?php echo htmlspecialchars($row['booking_time']); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </section>
</body>
</html>
