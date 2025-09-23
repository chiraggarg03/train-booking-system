<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include '../includes/db.php';

$source = isset($_GET['source']) ? $_GET['source'] : '';
$destination = isset($_GET['destination']) ? $_GET['destination'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

$where = [];
$params = [];
$types = '';

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
if (!empty($start_date)) {
    $where[] = "depart_time >= ?";
    $params[] = $start_date . " 00:00:00";
    $types .= 's';
}
if (!empty($end_date)) {
    $where[] = "depart_time <= ?";
    $params[] = $end_date . " 23:59:59";
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
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Search Trains</title>
    <link rel="stylesheet" href="../assets/style.css" />
</head>
<body>
    <nav>
        <a href="logout.php" class="btn logout">Logout</a>
        <a href="dashboard.php" class="btn nav-btn">Dashboard</a>
    </nav>

    <h2>Find Trains</h2>

    <form method="get" action="trains.php" class="search-form">
        <label for="source">From:</label>
        <input type="text" id="source" name="source" value="<?php echo htmlspecialchars($source); ?>" class="input-text">

        <label for="destination">To:</label>
        <input type="text" id="destination" name="destination" value="<?php echo htmlspecialchars($destination); ?>" class="input-text">

        <label for="start_date">Departure From:</label>
        <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>" class="input-date">

        <label for="end_date">Departure To:</label>
        <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>" class="input-date">

        <button type="submit" class="btn submit-btn">Search</button>
        <a href="trains.php" class="btn reset-btn">Reset</a>
    </form>

    <table class="data-table">
        <thead>
            <tr>
                <th>Train Name</th>
                <th>Source</th>
                <th>Destination</th>
                <th>Departure</th>
                <th>Arrival</th>
                <th>Seats</th>
                <th>Book</th>
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
                    <td><a href="book.php?train_id=<?php echo $row['id']; ?>" class="btn">Book</a></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
