<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Include database connection
require_once 'config.php'; // Adjust the path as needed

// Get the logged-in user's ID
$user_id = $_SESSION['user_id'];

// Prepare and execute query to get user profile


// Prepare and execute query to get counts
$count_query = "SELECT (SELECT COUNT(*) FROM users Where role='user') AS user_count, (SELECT COUNT(*) FROM rental) AS post_count ,(SELECT COUNT(*) FROM buy) AS buy_count,(SELECT COUNT(*) FROM users Where role='realtor') AS realtor_count";
$count_result = $mysqli->query($count_query);
$count_data = $count_result->fetch_assoc();

// Handle date filter
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '1970-01-01';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Prepare and execute query to get registration traffic with date filter
$traffic_query = "SELECT
                    IF(email_verified_at IS NOT NULL, 'Verified', 'Unverified') AS status,
                    COUNT(*) AS count
                  FROM users
                  WHERE DATE(email_verified_at) BETWEEN ? AND ?
                  GROUP BY status";

$traffic_stmt = $mysqli->prepare($traffic_query);
$traffic_stmt->bind_param("ss", $start_date, $end_date);
$traffic_stmt->execute();
$traffic_result = $traffic_stmt->get_result();

$traffic_data = [];
while ($row = $traffic_result->fetch_assoc()) {
    $traffic_data[] = $row;
}

$traffic_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="../image/local_image/logo.png" rel="icon">
    <title>Dashboard</title>
</head>
<body>
<header>
        <nav>
            <li><a href="dashboard.php">Home</a></li>
            

            <select class="nav-menu" id="Buy-select" onchange="navigateToPage(this)">
                <option selected disabled>Management</option>
                <option  value="user_management.php">User Management</option>
                <option  value="realtor_management.php">Realtor Management</option>      
                <option value="admin_management.php">Admin Management</option>      
                <option value="rent_management.php">Rent Management</option>      
                <option value="buy_management.php">Buy Management</option>      
            </select>
            
            <li><a href="wallet.php">Wallet</a></li>
            <li><a href="settings.php">Settings</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </nav>
    </header>

<div class="dash">
    <div class="count-container">
      
        <a href="user_management.php" class="count-users">
            <label for="user">Users: <?php echo htmlspecialchars($count_data['user_count']); ?></label>
            </a>
            
            <a href="realtor_management.php" class="count-users">
            <label for="user">Realtor: <?php echo htmlspecialchars($count_data['realtor_count']); ?></label>
            </a>
      
        <a href="rent_management.php" class="count-post">
        <label for="count">Rent item: <?php echo htmlspecialchars($count_data['post_count']); ?></label>
    </a>
    <a href="buy_management.php" class="count-post">
        <label for="count">Buy item: <?php echo htmlspecialchars($count_data['buy_count']); ?></label>
    </a>
    </div>
    <div class="chart-container">
        <canvas id="trafficChart"></canvas>
    </div>
</div>


<div class="filters">
 
    <form action="" method="get">
    <h3>Filters</h3>
        <label for="start_date">Start Date:</label>
        <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
        <label for="end_date">End Date:</label>
        <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
        <button type="submit">Apply Filters</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('trafficChart').getContext('2d');
    var trafficData = <?php echo json_encode($traffic_data); ?>;

    var chartLabels = trafficData.map(data => data.status);
    var chartData = trafficData.map(data => data.count);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartLabels,
            datasets: [{
                label: 'User Registration Traffic',
                data: chartData,
                backgroundColor: ['#4e73df', '#1cc88a'],
                borderColor: '#ffffff',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
<script src="js/header.js"></script>
</body>
</html>
