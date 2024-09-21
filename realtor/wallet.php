<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Include database configuration
require_once 'config.php';

// Get the user_id from the session
$user_id = $_SESSION['user_id'];

// Prepare the SQL query to fetch the wallet amount for the logged-in user
$query = "SELECT amount FROM wallet WHERE users_id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if the user has a wallet entry
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $amount = $row['amount'];
} else {
    $amount = "0.00"; // Default amount if no wallet entry found
}

// Close the statement and connection
$stmt->close();
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/wallet.css">
    <title>Wallet</title>
</head>
<body>
<header>
        <nav>
        <li><a href="dashboard.php">Home</a></li>
        <li><a href="rent_management.php">Rent managment</a></li>
        <li><a href="buy_management.php">Buy managment</a></li>
        <li><a href="wallet.php">wallet</a></li>
        <li><a href="settings.php">Settings</a></li>
        <li><a href="../logout.php">Logout</a></li>
        </nav>
    </header>
    <div class="wallet-form-container">
        <form action="">
            
            <div class="wallet-balance">
        <h2>Your Wallet Balance</h2>
        <p>Current Amount: <strong>$<?php echo number_format($amount, 2); ?></strong></p>
    </div><br><br>
            <div class="form-group">
                <p>For adding to your wallet, send money via your preferred money app to this phone number: <strong>76976048</strong></p>
                <p>The note of the money transfer should include your name and email account.</p>
            </div>
        </form>
    </div>
</body>
</html>