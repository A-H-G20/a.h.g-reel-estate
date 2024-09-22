<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Include database configuration
require_once '../config.php';

// Fetch items from the rental table
$rentalQuery = "SELECT * FROM rental where category ='vacation_rentals'";
$rentalResult = $mysqli->query($rentalQuery);


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/item.css">
    <title>Vacation rentals</title>

</head>
<body>
<header>
        <nav>
            <li><a href="../dashboard.php">Home</a></li>
            <select class="nav-menu" id="Rentel-select" onchange="navigateToPage(this)">
                <option selected disabled>Rentel</option>
                <option value="houses.php">Houses</option>
                <option value="vacation_rentals.php">Vacation Rentals</option>
                <option value="studios.php">Studios</option>
                <option value="duplexes.php">Duplexes</option>
                <option value="basement_apartments.php">Basement Apartments</option>
                <option value="farmhouses.php">Farmhouses</option>
                <option value="swimming_pool.php">Swimming Pool</option>           
            </select>

            <select class="nav-menu" id="Buy-select" onchange="navigateToPage(this)">
                <option selected disabled>Buy</option>
                <option value="../buy/houses.php">Houses</option>
                <option value="../buy/vacation_rentals.php">Vacation Rentals</option>
                <option value="../buy/studios.php">Studios</option>
                <option value="../buy/duplexes.php">Duplexes</option>
                <option value="../buy/basement_apartments.php">Basement Apartments</option>
                <option value="../buy/farmhouses.php">Farmhouses</option>
                <option value="../buy/swimming_pool.php">Swimming Pool</option>           
            </select>
            <select class="nav-menu" id="Buy-select" onchange="navigateToPage(this)">
                <option selected disabled>My reserved</option>
                <option value="../rent_table.php">Rent Reserved</option>
                <option value="../buy_table.php">Buy Reserved</option>      
            </select>
            <li><a href="../wallet.php">Wallet</a></li>
            <li><a href="../ask_for_realtor.php">Ask to be realtor</a></li>
            <li><a href="../settings.php">Settings</a></li>
            <li><a href="../../logout.php">Logout</a></li>
        </nav>
    </header><br><br>
    <div class="rental-items">
    <?php if ($rentalResult->num_rows > 0): ?>
        <?php while ($rentalItem = $rentalResult->fetch_assoc()): ?>
            <div class="item">
                <img src="../../image/<?php echo htmlspecialchars($rentalItem['image1']); ?>" alt="Rental Image" onerror="this.src='../users/image/default.jpg'">
               
                <label><?php echo "$" . number_format($rentalItem['price'], 2); ?></label>
              
                <button type="button" onclick="location.href='rental_details.php?id=<?php echo $rentalItem['rental_id']; ?>'">More details</button>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No rental items available.</p>
    <?php endif; ?>
</div>
<script src="../js/header.js"></script>
</body>
</html>
