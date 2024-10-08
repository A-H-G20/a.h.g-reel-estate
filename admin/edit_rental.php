<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$rental_id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Fetch current rental data to keep unchanged fields
    $query = "SELECT * FROM rental WHERE rental_id = ? AND user_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ii", $rental_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $rental = $result->fetch_assoc();

    // Get new values or keep existing ones
    $description = htmlspecialchars($_POST['description']) ?: $rental['description'];
    $category = htmlspecialchars($_POST['category']) ?: $rental['category'];
    $price = floatval($_POST['price']) ?: $rental['price'];
    $is_rental = isset($_POST['is_rental']) ? 1 : 0;

    // Handle file uploads
    $targetDir = "../image/";
    $images = [];
    $uploadOk = true;

    for ($i = 1; $i <= 3; $i++) {
        if (!empty($_FILES["image$i"]["name"])) {
            $targetFile = $targetDir . basename($_FILES["image$i"]["name"]);

            if (getimagesize($_FILES["image$i"]["tmp_name"]) === false || $_FILES["image$i"]["size"] > 2000000) {
                $uploadOk = false;
                break;
            }

            if ($uploadOk && move_uploaded_file($_FILES["image$i"]["tmp_name"], $targetFile)) {
                $images[] = basename($_FILES["image$i"]["name"]);
            } else {
                $uploadOk = false;
                break;
            }
        } else {
            // Keep the existing image if no new file is uploaded
            $images[] = $rental["image$i"];
        }
    }

    if ($uploadOk) {
        // Prepare the update query with only updated fields
        $query = "UPDATE rental SET description = ?, category = ?, price = ?, is_rental = ?, image1 = ?, image2 = ?, image3 = ? WHERE rental_id = ? AND user_id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ssdiiissi", $description, $category, $price, $is_rental, $images[0], $images[1], $images[2], $rental_id, $user_id);

        if ($stmt->execute()) {
            header("Location: rent_management.php?success=1");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "File upload failed. Please try again.";
    }
}

// Fetch rental data for the form
$query = "SELECT * FROM rental WHERE rental_id = ? AND user_id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("ii", $rental_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$rental = $result->fetch_assoc();

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Rental</title>
    <link rel="stylesheet" href="css/edit.css">
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
    </header><br><br>
    <form action="" method="POST" enctype="multipart/form-data">
        <h3>Edit Rental</h3>
        <input type="text" name="description" value="<?php echo htmlspecialchars($rental['description']); ?>" required>
        <select name="category" required>
            <option value="apartment" <?php echo $rental['category'] == 'apartment' ? 'selected' : ''; ?>>Apartment</option>
            <option value="house" <?php echo $rental['category'] == 'house' ? 'selected' : ''; ?>>House</option>
            <option value="commercial" <?php echo $rental['category'] == 'commercial' ? 'selected' : ''; ?>>Commercial</option>
            <option value="land" <?php echo $rental['category'] == 'land' ? 'selected' : ''; ?>>Land</option>
        </select>
        <input type="number" name="price" value="<?php echo htmlspecialchars($rental['price']); ?>" required>
        <label for="is_rental">Is Rental?</label>
        <input type="checkbox" name="is_rental" value="1" <?php echo $rental['is_rental'] ? 'checked' : ''; ?>>

        <h4>Upload New Images (Leave blank to keep existing)</h4>
        <input type="file" name="image1">
        <input type="file" name="image2">
        <input type="file" name="image3">

        <button type="submit">Update Rental</button>
    </form>
    <script src="js/header.js"></script>
</body>

</html>