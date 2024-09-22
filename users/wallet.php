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

// Prepare the SQL query to fetch all wallet amounts for the logged-in user
$query = "SELECT amount FROM wallet WHERE users_id = ?"; 
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Sum all amounts
$totalAmount = 0;
while ($row = $result->fetch_assoc()) {
    $totalAmount += $row['amount']; // Accumulate the total amount
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
            <select class="nav-menu" id="Rentel-select" onchange="navigateToPage(this)">
                <option selected disabled>Rentel</option>
                <option value="rentel/houses.php">Houses</option>
                <option value="rentel/vacation_rentals.php">Vacation Rentals</option>
                <option value="rentel/studios.php">Studios</option>
                <option value="rentel/duplexes.php">Duplexes</option>
                <option value="rentel/basement_apartments.php">Basement Apartments</option>
                <option value="rentel/farmhouses.php">Farmhouses</option>
                <option value="rentel/swimming_pool.php">Swimming Pool</option>           
            </select>

            <select class="nav-menu" id="Buy-select" onchange="navigateToPage(this)">
                <option selected disabled>Buy</option>
                <option value="buy/houses.php">Houses</option>
                <option value="buy/vacation_rentals.php">Vacation Rentals</option>
                <option value="buy/studios.php">Studios</option>
                <option value="buy/duplexes.php">Duplexes</option>
                <option value="buy/basement_apartments.php">Basement Apartments</option>
                <option value="buy/farmhouses.php">Farmhouses</option>
                <option value="buy/swimming_pool.php">Swimming Pool</option>           
            </select>
            <select class="nav-menu" id="Buy-select" onchange="navigateToPage(this)">
                <option selected disabled>My reserved</option>
                <option value="rent_table.php">Rent Reserved</option>
                <option value="buy_table.php">Buy Reserved</option>      
            </select>
            
            <li><a href="wallet.php">Wallet</a></li>
            <li><a href="ask_for_realtor.php">Ask to be realtor</a></li>
            <li><a href="settings.php">Settings</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </nav>
    </header>
    <div class="wallet-form-container">
    <h2>Your Wallet Balance</h2>
    
    <div class="wallet-balance">
        <p>Current Amount: <strong>$<?php echo number_format($totalAmount, 2); ?></strong></p>
    </div><br>
    
    <div class="form-group">
        <p>To add funds to your wallet, send money via your preferred money app to this phone number: <strong>76976048</strong>.</p>
        <p>The note of the money transfer should include your name and email account.</p>
    </div>
</div>
    <script src="js/header.js"></script>
</body>
</html>