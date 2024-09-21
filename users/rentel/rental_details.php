<?php
session_start();

// Redirect to login if no session is found
if (!isset($_SESSION['user_id']) || trim($_SESSION['user_id']) == '') {
    echo '<script>window.location = "login.php";</script>';
    exit();
}

require '../config.php'; // Database connection

// Check if rental ID is passed via URL
if (isset($_GET['id'])) {
    $rental_id = (int) $_GET['id']; // Convert ID to integer to avoid SQL injection

    // Fetch the item's details from the database
    $query = "SELECT b.image1, b.image2, b.image3, b.price, b.description, u.name as realtor_name 
              FROM rental b 
              JOIN users u ON b.user_id = u.id 
              WHERE b.rental_id = ?";
    $stmt = mysqli_prepare($mysqli, $query);
    mysqli_stmt_bind_param($stmt, 'i', $rental_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rentalItem = mysqli_fetch_assoc($result);

    if (!$rentalItem) {
        echo 'Item not found!';
        exit();
    }
} else {
    echo 'Invalid item ID!';
    exit();
}

// Handle form submission for adding to cart
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Logic to add the item to the cart (to be implemented)
    echo '<script>alert("Item added to cart successfully!");</script>';
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
        <img id="slider-image" src="../../image/<?php echo htmlspecialchars($rentalItem['image1']); ?>" />
        <button class="arrow left" onclick="changeImage(-1)">&#10094;</button>
        <button class="arrow right" onclick="changeImage(1)">&#10095;</button>
    </div>
    <div class="description">
        <h2><?php echo nl2br(htmlspecialchars($rentalItem['description'])); ?></h2>
        <h1>Price: $<?php echo htmlspecialchars($rentalItem['price']); ?></h1>
        <h3>Realtor: <?php echo htmlspecialchars($rentalItem['realtor_name']); ?></h3>
        
        <form method="POST">
            <button type="submit">Add to Cart</button>
            <button type="submit">rental</button>

        </form>
    </div>
</div>

<script>
    let currentImageIndex = 0;
    const images = [
        '<?php echo htmlspecialchars($rentalItem['image1']); ?>',
        '<?php echo htmlspecialchars($rentalItem['image2']); ?>',
        '<?php echo htmlspecialchars($rentalItem['image3']); ?>'
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
