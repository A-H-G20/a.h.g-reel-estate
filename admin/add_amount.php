<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Include your database connection file
include 'config.php'; // Make sure this file contains your database connection details

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the user ID and amount from the form
    $user_id = $_POST['user_id'];
    $amount = $_POST['amount'];

    // Validate the input
    if (!is_numeric($amount) || $amount <= 0) {
        echo "Invalid amount. Please enter a valid number.";
        exit();
    }

    // Prepare the SQL statement to insert the amount into the wallet table
    $stmt = $mysqli->prepare("INSERT INTO wallet (users_id, amount, date) VALUES (?, ?, NOW())");
    $stmt->bind_param("id", $user_id, $amount);

    if ($stmt->execute()) {
        // Fetch the user's email and name for the email
        $stmt = $mysqli->prepare("SELECT name, email FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $name = $user['name'];
            $email = $user['email'];

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

                // Set up the email content
                $mail->setFrom('your_email@gmail.com', 'A.H.G');
                $mail->addAddress($email, $name);
                $mail->isHTML(true);
                $mail->Subject = 'Wallet Amount Added';
                $mail->Body = '<p>Dear <b>' . htmlspecialchars($name) . '</b>,</p>
                               <p>An amount of <b>' . htmlspecialchars($amount) . '</b> has been successfully added to your wallet.</p>
                               <p>Regards,</p><p>A.H.G Administrator</p>';

                // Send email
                $mail->send();

                echo "Amount added and email sent successfully.";
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            echo "User not found.";
        }

        // Close the statement
        $stmt->close();
    } else {
        // Error message
        echo "Error: " . $stmt->error;
    }

    // Close the database connection
    $mysqli->close();

    // Redirect back to the user management page
    header("Location: wallet.php");
    exit();
}
