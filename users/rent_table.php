<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require 'config.php'; // Database connection

// Fetch the reserved items from the buy_reserved table
$user_id = $_SESSION['user_id'];
$query = "
    SELECT br.rental_id, b.image1, b.category, b.price 
    FROM rental_reserved br
    JOIN rental b ON br.rental_id = b.rental_id
    WHERE br.users_id = ?
";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Reserved Items</title>
    <link rel="stylesheet" href="css/item_table.css"> <!-- Link to your CSS file -->
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
<h2>Your Reserved Items</h2>

<table border="1">
    <thead>
        <tr>
            <th>Image</th>
            <th>Category</th>
            <th>Price</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><img src="../image/<?php echo htmlspecialchars($row['image1']); ?>" alt="Item Image" width="100"></td>
                <td><?php echo htmlspecialchars($row['category']); ?></td>
                <td>$<?php echo number_format($row['price'], 2); ?></td>
                <td>
                    <form action="delete_rental_reserved" method="POST">
                        <input type="hidden" name="id" value="<?php echo $row['rental_id']; ?>">
                        <button type="submit" onclick="return confirm('Are you sure you want to delete this item?');">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
        <?php if ($result->num_rows === 0): ?>
            <tr>
                <td colspan="4">No reserved items found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
$stmt->close();
$mysqli->close();
?>
<script src="js/header.js"></script>
</body>
</html>
