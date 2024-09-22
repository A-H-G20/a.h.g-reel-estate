<?php
session_start();
require 'config.php'; // Include the DB connection

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {
    $email = $_POST['email'];
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
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Recharge Reminder';
        $mail->Body = '<p>Dear Realtor,</p>
                       <p>This is a reminder that you have 2 days to recharge your realtor account.</p>
                       <p>Best Regards,</p><p>A.H.G Administrator</p>';

        // Send email
        $mail->send();
        header("loaction: realtor_managment.php");
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    } catch (mysqli_sql_exception $e) {
        echo "Database Error: {$e->getMessage()}";
    }
}
