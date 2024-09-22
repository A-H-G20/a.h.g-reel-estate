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
$email_query = "SELECT email, name FROM users WHERE id = ?";
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
";
$stmt = $mysqli->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['rerental'])) {
    $rental_id = $_POST['rental_id'];

    // Send reminder email
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
        $mail->Subject = 'Re-rental Reminder';
        $mail->Body = '<p>Dear ' . htmlspecialchars($name) . ',</p>
                       <p>This is a reminder for your reserved rental. Please take action before it expires!</p>
                       <p>Thank you for using our service.</p>
                       <p>Regards,</p>
                       <p>A.H.G Administrator</p>';

        // Send email
        $mail->send();
        header("Location: rent_table.php?success=Reminder sent successfully!");
        exit();
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
    <title>Your Reserved Items</title>
    <link rel="stylesheet" href="css/item_table.css"> <!-- Link to your CSS file -->
    <link href="../image/local_image/logo.png" rel="icon">
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
                            <button type="submit" name="rerental" onclick="return confirm('Are you sure you want to send a reminder to this user?');">Reminder</button>
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