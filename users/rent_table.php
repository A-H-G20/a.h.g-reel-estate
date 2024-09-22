<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require 'config.php'; // Database connection
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

// Fetch user email from session or database
$user_id = $_SESSION['user_id'];
$email_query = "SELECT email ,name FROM users WHERE id = ?";
$email_stmt = $mysqli->prepare($email_query);
$email_stmt->bind_param("i", $user_id);
$email_stmt->execute();
$email_stmt->bind_result($user_email, $name);
$email_stmt->fetch();
$email_stmt->close();

// Fetch the reserved items from the rental_reserved table
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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['rerental'])) {
    $rental_id = $_POST['rental_id'];

    // Check wallet balance
    $wallet_query = "SELECT amount FROM wallet WHERE users_id = ?";
    $wallet_stmt = $mysqli->prepare($wallet_query);
    $wallet_stmt->bind_param("i", $user_id);
    $wallet_stmt->execute();
    $wallet_stmt->bind_result($wallet_amount);
    $wallet_stmt->fetch();
    $wallet_stmt->close();

    // Fetch the rental price
    $price_query = "SELECT price FROM rental WHERE rental_id = ?";
    $price_stmt = $mysqli->prepare($price_query);
    $price_stmt->bind_param("i", $rental_id);
    $price_stmt->execute();
    $price_stmt->bind_result($price);
    $price_stmt->fetch();
    $price_stmt->close();

    if ($wallet_amount >= $price) {
        // Update rental date
        $update_query = "UPDATE rental_reserved SET date = NOW() WHERE rental_id = ? AND users_id = ?";
        $update_stmt = $mysqli->prepare($update_query);
        $update_stmt->bind_param("ii", $rental_id, $user_id);
        $update_stmt->execute();
        $update_stmt->close();

        // Deduct amount from wallet
        $new_amount = $wallet_amount - $price;
        $deduct_query = "UPDATE wallet SET amount = ? WHERE users_id = ?";
        $deduct_stmt = $mysqli->prepare($deduct_query);
        $deduct_stmt->bind_param("di", $new_amount, $user_id);
        $deduct_stmt->execute();
        $deduct_stmt->close();

        // Send confirmation email
        $mail = new PHPMailer(true);
        try {
            // SMTP configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = '22130479@students.liu.edu.lb'; // Your Gmail address
            $mail->Password = 'jqujaycttktvlevd'; // Your Gmail password or App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Email content
            $mail->setFrom('your_email@gmail.com', 'A.H.G Administrator');
            $mail->addAddress($user_email); // Use fetched user email
            $mail->isHTML(true);
            $mail->Subject = 'Re-rental Confirmation';
            $mail->Body = '<p>Dear ' . htmlspecialchars($name) . ',</p>
            <p>Your rental has been successfully re-rented!</p>
              <p>The amount deducted from your wallet is $' . htmlspecialchars($price) . '.</p>
                           <p>Thank you for using our service.</p>
                            <p>Regards,</p>
            <p>A.H.G Administrator</p>';


            // Send email
            $mail->send();
            header("Location: rent_table.php?success=Re-rental successful!");
            exit();
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "<script>alert('Insufficient wallet balance.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Reserved Items</title>
    <link rel="stylesheet" href="css/item_table.css"> <!-- Link to your CSS file -->
    <link href="../image/local_image/logo.png" rel="icon">
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
            <select class="nav-menu" id="My-reserved-select" onchange="navigateToPage(this)">
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

                        <form action="" method="POST" style="display:inline;">
                            <input type="hidden" name="rental_id" value="<?php echo $row['rental_id']; ?>">
                            <button type="submit" name="rerental" onclick="return confirm('Are you sure you want to re-rent this item?');">Re-rent</button>
                        </form>
                        <form action="delete_rental_reserved.php" method="POST" style="display:inline;">
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