<?php
session_start();

// Redirect to login if no session is found
if (!isset($_SESSION['user_id']) || trim($_SESSION['user_id']) == '') {
    echo '<script>window.location = "login.php";</script>';
    exit();
}

require '../config.php'; // Database connection

// Include PHPMailer classes at the top
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../PHPMailer/src/Exception.php';
require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';

// Check if buy ID is passed via URL
if (isset($_GET['id'])) {
    $buy_id = (int) $_GET['id']; // Convert ID to integer to avoid SQL injection

    // Fetch the buy item's details from the database
    $query = "SELECT r.image1, r.image2, r.image3, r.price, r.description, u.name as realtor_name 
              FROM buy r 
              JOIN users u ON r.user_id = u.id 
              WHERE r.buy_id = ?";
    $stmt = mysqli_prepare($mysqli, $query);
    mysqli_stmt_bind_param($stmt, 'i', $buy_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $buyItem = mysqli_fetch_assoc($result);

    if (!$buyItem) {
        echo 'Item not found!';
        exit();
    }

    // Check if the item is already reserved
    $reservedCheckQuery = "SELECT COUNT(*) FROM buy_reserved WHERE buy_id = ?";
    $stmt = $mysqli->prepare($reservedCheckQuery);
    $stmt->bind_param("i", $buy_id);
    $stmt->execute();
    $stmt->bind_result($reservedCount);
    $stmt->fetch();
    $stmt->close();

    $isReserved = $reservedCount > 0; // Check if the item is already reserved
} else {
    echo 'Invalid item ID!';
    exit();
}

// Handle form submission for buying
$purchaseSuccessful = false; // Track if purchase was successful

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$isReserved) {
    $user_id = $_SESSION['user_id'];

    // Fetch the user's wallet balance
    $wallet_query = "SELECT amount, email, name FROM wallet INNER JOIN users ON wallet.users_id = users.id WHERE wallet.users_id = ?";
    $stmt = $mysqli->prepare($wallet_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($amount, $email, $name);
    $stmt->fetch();
    $stmt->close();

    if ($amount < $buyItem['price']) {
        // Set the message
        echo "You don't have enough amount in your wallet.";
        header("Location: javascript:history.back();");
    } else {
        // Deduct from the wallet
        $new_balance = $amount - $buyItem['price'];
        $update_wallet_query = "UPDATE wallet SET amount = ? WHERE users_id = ?";
        $stmt = $mysqli->prepare($update_wallet_query);
        $stmt->bind_param("di", $new_balance, $user_id);
        $stmt->execute();
        $stmt->close();

        // Add to the buy table
        $reserveQuery = "INSERT INTO buy_reserved (users_id, buy_id, date) VALUES (?, ?, NOW())";
        $stmt = mysqli_prepare($mysqli, $reserveQuery);
        mysqli_stmt_bind_param($stmt, 'ii', $user_id, $buy_id);
        mysqli_stmt_execute($stmt);
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
            $mail->Subject = 'Purchase Confirmation';
            $mail->Body = '<p>Dear ' . htmlspecialchars($name) . ',</p>
            <p>Your purchase of the buy item is complete!</p>
            <p>The amount deducted from your wallet is $' . htmlspecialchars($buyItem['price']) . '.</p>
            <p>Regards,</p>
            <p>A.H.G Administrator</p>';

            // Send email
            $mail->send();
            $purchaseSuccessful = true; // Set the flag to true after successful purchase

            // Redirect to a confirmation page
            header("Location: javascript:history.back();");
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
    <link rel="stylesheet" href="../css/details.css"> <!-- Link to your CSS file -->
    <title>Item Details</title>
</head>
<body>

<div class="card">
    <nav>
        <a href="javascript:history.back();"><img src="../../image/local_image/back.png" alt="Back" /></a>
    </nav>

    <div class="slider">
        <img id="slider-image" src="../../image/<?php echo htmlspecialchars($buyItem['image1']); ?>" />
        <button class="arrow left" onclick="changeImage(-1)">&#10094;</button>
        <button class="arrow right" onclick="changeImage(1)">&#10095;</button>
    </div>
    <div class="description">
        <h2><?php echo nl2br(htmlspecialchars($buyItem['description'])); ?></h2>
        <h1>Price: $<?php echo htmlspecialchars($buyItem['price']); ?></h1>
        <h3>Realtor: <?php echo htmlspecialchars($buyItem['realtor_name']); ?></h3>
        
        <form method="POST">
            <button type="submit" <?php echo $isReserved ? 'disabled' : ''; ?>>Buy</button>
        </form>
        <?php if ($isReserved): ?>
            <p>This item is already reserved.</p>
        <?php endif; ?>
    </div>
</div>

<script>
    let currentImageIndex = 0;
    const images = [
        '<?php echo htmlspecialchars($buyItem['image1']); ?>',
        '<?php echo htmlspecialchars($buyItem['image2']); ?>',
        '<?php echo htmlspecialchars($buyItem['image3']); ?>'
    ];

    function changeImage(direction) {
        currentImageIndex += direction;
        if (currentImageIndex < 0) {
            currentImageIndex = images.length - 1; // Loop to last image
        } else if (currentImageIndex >= images.length) {
            currentImageIndex = 0; // Loop to first image
        }
        document.getElementById('slider-image').src = '../../image/' + images[currentImageIndex];
    }
</script>

<?php
// Close the connection
mysqli_close($mysqli);
?>
</body>
</html>
