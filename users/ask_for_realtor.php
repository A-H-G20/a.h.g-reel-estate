<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
include 'config.php'; // Ensure this file contains the database connection code

// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id']; // Assuming user_id is saved in the session

    // Fetch the user's wallet balance
    $wallet_query = "SELECT amount, email,name FROM wallet INNER JOIN users ON wallet.users_id = users.id WHERE wallet.users_id = ?";
    $stmt = $mysqli->prepare($wallet_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($amount, $email,$name);
    $stmt->fetch();
    $stmt->close();

    if ($amount < 50) {
        // Set the message
        $message = "You don't have enough amount in your wallet.";
        
        // Redirect to wallet.php with the message
        header("Location: wallet.php?message=" . urlencode($message));
        exit();
    }
    else {
        // Deduct 50 from the wallet
        $new_balance = $amount - 50;
        $update_wallet_query = "UPDATE wallet SET amount = ? WHERE users_id = ?";
        $stmt = $mysqli->prepare($update_wallet_query);
        $stmt->bind_param("di", $new_balance, $user_id);
        $stmt->execute();
        $stmt->close();

        // Add to the realtor table
        $insert_realtor_query = "INSERT INTO realtor (users_id, wallet_id, date) VALUES (?, ?, NOW())";
        $wallet_id_query = "SELECT wallet_id FROM wallet WHERE users_id = ?";
        $stmt = $mysqli->prepare($wallet_id_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($wallet_id);
        $stmt->fetch();
        $stmt->close();

        $stmt = $mysqli->prepare($insert_realtor_query);
        $stmt->bind_param("ii", $user_id, $wallet_id);
        $stmt->execute();
        $stmt->close();

        // Update user role to 'realtor'
        $update_role_query = "UPDATE users SET role = 'realtor' WHERE id = ?";
        $stmt = $mysqli->prepare($update_role_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();

        // Send email notification
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

            // Set up the email content
            $mail->setFrom('your_email@gmail.com', 'A.H.G Administrator');
            $mail->addAddress($email); // Send to user’s email
            $mail->isHTML(true);
            $mail->Subject = 'Congratulations!';
            $mail->Body = '<p>Dear ' . htmlspecialchars($name) . ',</p>
            <p>You are now a realtor!</p>
            <p>Login agin to open realtor page</p>
            <p>Regards,</p>
            <p>A.H.G Administrator</p>';


            // Send email
            $mail->send();

            // Redirect to realtor dashboard
            header("Location: ../logout.php");
            exit();
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/aks_for_realtor..css">
    <title>Ask for realtor Page</title>
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
    <form action="" method="POST">
    <h2>To be a realtor</h2>
    <p>You have to pay $50 per month to be a realtor and we can add your real estate.</p>
    <button type="submit">Pay</button>
</form>
<?php
// Display message if set
if (isset($message)) {
    echo "<p>$message</p>";
}
?>

</body>
</html>
