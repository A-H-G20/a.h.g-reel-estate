<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/realtor.css">
    <link href="../image/local_image/logo.png" rel="icon">
    <title>Wallet</title>
</head>
<body>
<header>
    <nav>
        <li><a href="dashboard.php">Home</a></li>
        <li><a href="rent_management.php">Rent Management</a></li>
        <li><a href="buy_management.php">Buy Management</a></li>
        <li><a href="wallet.php">Wallet</a></li>
        <li><a href="settings.php">Settings</a></li>
        <li><a href="../logout.php">Logout</a></li>
    </nav>
</header>
<form action="" method="POST">
    <h2>To recharge your realtor account</h2>
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
            <p>You realtor account have been recharge</p>
            <p>Regards,</p>
            <p>A.H.G Administrator</p>';


            // Send email
            $mail->send();

            // Redirect to realtor dashboard
            header("Location: dashboard.php");
            exit();
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}
?>
