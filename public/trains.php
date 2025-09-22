<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../includes/db.php';

// Get filters from URL
$source = isset($_GET['source']) ? $_GET['source'] : '';
$destination = isset($_GET['destination']) ? $_GET['destination'] : '';

$where = [];
$params = [];
$types = '';

// Build SQL WHERE clause dynamically
if (!empty($source)) {
    $where[] = "source = ?";
    $params[] = $source;
    $types .= 's';
}
if (!empty($destination)) {
    $where[] = "destination = ?";
    $params[] = $destination;
    $types .= 's';
}

$sql = "SELECT * FROM trains";
if ($where) {
    $sql .= " WHERE " . implode(' AND ', $where);
}
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Search Trains</title>
</head>
<body>
    <h2>Find Trains</h2>
    <a href="logout.php">Logout</a>
    <form method="get" action="trains.php">
        <label>From:</label>
        <input type="text" name="source" value="<?php echo htmlspecialchars($source); ?>">
        <label>To:</label>
        <input type="text" name="destination" value="<?php echo htmlspecialchars($destination); ?>">
        <button type="submit">Search</button>
    </form>
    <br>
    <table border="1" cellpadding="8">
        <tr>
            <th>Train Name</th>
            <th>Source</th>
            <th>Destination</th>
            <th>Departure</th>
            <th>Arrival</th>
            <th>Seats</th>
            <th>Book</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['train_name']); ?></td>
                <td><?php echo htmlspecialchars($row['source']); ?></td>
                <td><?php echo htmlspecialchars($row['destination']); ?></td>
                <td><?php echo htmlspecialchars($row['depart_time']); ?></td>
                <td><?php echo htmlspecialchars($row['arrival_time']); ?></td>
                <td><?php echo htmlspecialchars($row['total_seats']); ?></td>
                <td>
                    <a href="book.php?train_id=<?php echo $row['id']; ?>">Book</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
