<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/dashboard.css">
    <link href="../image/local_image/logo.png" rel="icon">
    <title>Home Page</title>
    <script src="js/header.js"></script>
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
    <br><br><br>
    <div class="container">
        <div class="image">
            <img src="../image/local_image/hero-banner.png" alt="">
        </div>
        <div class="info">
            Lorem ipsum dolor sit amet consectetur adipisicing elit. Non, minima quasi? Quibusdam fugit adipisci corrupti cum? Unde distinctio velit repellendus! Provident sapiente ipsum quibusdam, tempore velit fuga veniam quos dolor.
        </div>
    </div>

    <footer>
        <p>A.H.G Real Estate Website</p>
    </footer>
</body>

</html>