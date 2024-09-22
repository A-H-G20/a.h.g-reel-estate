<?php
session_start();
require '../config.php'; // Include the DB connection
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $message = $_POST['message'];

    // Create an instance of PHPMailer
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

        // Retrieve users with roles 'user' and 'realtor'
        $query = "SELECT email, name FROM users WHERE role IN ('user', 'realtor')";
        $result = $conn->query($query);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $email = $row['email'];
                $name = $row['name'];

                // Set email content
                $mail->setFrom('your_email@gmail.com', 'A.H.G Administrator');
                $mail->addAddress($email, $name);
                $mail->isHTML(true);
                $mail->Subject = 'Important Notification';
                $mail->Body = '<p>Dear <b>' . htmlspecialchars($name) . '</b>,</p>
                               <p>' . nl2br(htmlspecialchars($message)) . '</p>
                               <p>Regards,<br>A.H.G Administrator</p>';

                // Send email
                $mail->send();

                // Clear all recipients for the next loop
                $mail->clearAddresses();
            }
            header("location: send_for_all.php");
        } else {
            echo "No users found with the role 'user' or 'realtor'.";
        }
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/send_for_all.css">
    <link href="../image/local_image/logo.png" rel="icon">
    <title>Send Email</title>
</head>

<body>
    <header>
        <nav>
            <li><a href="dashboard.php">Home</a></li>


            <select class="nav-menu" id="Buy-select" onchange="navigateToPage(this)">
                <option selected disabled>Management</option>
                <option value="user_management.php">User Management</option>
                <option value="realtor_management.php">Realtor Management</option>
                <option value="admin_management.php">Admin Management</option>
                <option value="rent_management.php">Rent Management</option>
                <option value="buy_management.php">Buy Management</option>
            </select>
            <select class="nav-menu" id="Buy-select" onchange="navigateToPage(this)">
                <option selected disabled>Reserved</option>
                <option value="rent_table.php">Rent Reserved</option>
                <option value="buy_table.php">Buy Reserved</option>
            </select>
            <li><a href="send_for_all.php">Send email</a></li>
            <li><a href="wallet.php">Wallet</a></li>
            <li><a href="settings.php">Settings</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </nav>
    </header>
    <form action="" method="POST">
        <input name="message" placeholder="Enter your message here" required></input>
        <button type="submit">Send</button>
    </form>
</body>

</html>