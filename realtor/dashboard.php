<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Include database configuration
require_once 'config.php';

// Fetch items from the rental table
$rentalQuery = "SELECT * FROM rental";
$rentalResult = $mysqli->query($rentalQuery);

// Fetch items from the buy table
$buyQuery = "SELECT * FROM buy";
$buyResult = $mysqli->query($buyQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/dashboard.css">
    <title>Home Page</title>
</head>
<body>
    <header>
        <nav>
            <li><a href="dashboard.php">Home</a></li>
            <li><a href="rent_management.php">Rent Management</a></li>
            <li><a href="buy_management.php">Buy Management</a></li>
            <li><a href="recharge_for_realtor.php">Recharge my account</a></li>
            <li><a href="wallet.php">Wallet</a></li>
            <li><a href="settings.php">Settings</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </nav>
    </header>

    <!-- Display Rental Items -->
 <!-- Display Rental Items -->
 <h2>Rental items</h2>
<form action="" class="rent">
  
    <?php if ($rentalResult->num_rows > 0): ?>
        <?php while ($rentalItem = $rentalResult->fetch_assoc()): ?>
            <div class="item">
                <img src="../image/<?php echo $rentalItem['image1']; ?>" alt="Rental Image">
                <p class="category"><?php echo htmlspecialchars($rentalItem['category']); ?></p>
                <label for=""><?php echo "$" . number_format($rentalItem['price'], 2); ?></label>
               
                <button type="button" onclick="location.href='rental_details.php?id=<?php echo $rentalItem['rental_id']; ?>'">More details</button>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No rental items available.</p>
    <?php endif; ?>
</form>
<h2>Buy items</h2>
    <!-- Display Buy Items -->
  <!-- Display Buy Items -->
<form action="" class="buy">
    <?php if ($buyResult->num_rows > 0): ?>
        <?php while ($buyItem = $buyResult->fetch_assoc()): ?>
            <div class="item">
                <img src="../image/<?php echo $buyItem['image1']; ?>" alt="Buy Image">
                <label for=""><?php echo "$" . number_format($buyItem['price'], 2); ?></label>
                <?php if (isset($buyItem['category'])): ?>
                    <p class="category"><?php echo htmlspecialchars($buyItem['category']); ?></p>
                <?php else: ?>
                    <p class="category">No category available</p>
                <?php endif; ?>
                <button type="button" onclick="location.href='buy_details.php?id=<?php echo $buyItem['buy_id']; ?>'">More details</button>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No buy items available.</p>
    <?php endif; ?>
</form>

</body>
</html>

<?php
// Close the database connection
$mysqli->close();
?>
