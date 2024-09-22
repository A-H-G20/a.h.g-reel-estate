<?php
session_start();
ob_start(); // Start output buffering to prevent 'headers already sent' issues

// Include the database configuration file
include 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get the user_id from the session
$user_id = $_SESSION['user_id'];

// Fetch rental information for the logged-in user
$query = "SELECT * FROM buy WHERE user_id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../image/local_image/logo.png" rel="icon">
    <link rel="stylesheet" href="css/management.css">
    <title>Rent Management</title>
</head>
<body>
<header>
        <nav>
        <li><a href="dashboard.php">Home</a></li>
        <li><a href="rent_management.php">Rent managment</a></li>
        <li><a href="buy_management.php">Buy managment</a></li>
        <li><a href="recharge_for_realtor.php">Recharge my account</a></li>
        <li><a href="wallet.php">wallet</a></li>
        <li><a href="settings.php">Settings</a></li>
        <li><a href="../logout.php">Logout</a></li>
        </nav>
    </header>

<!-- Add Button -->
<button id="add-button" class="button">Add Rental</button>

<!-- Add Rental Form -->
<div class="form-container" id="rental-form">
    <h3>Add Rental</h3>
    <form action="" method="POST" enctype="multipart/form-data">
        <input type="text" name="description" placeholder="Description" required>
        
        <!-- Category Selection -->
        <select name="category" id="category" required>
            <option value="">Select a category</option>
            <option value="basement">Basement</option>
            <option value="house">House</option>
            <option value="duplexes">Duplexes</option>
            <option value="farmhouses">Farmhouses</option>
            <option value="swimming_poll">Swimming poll</option>
            <option value="studios">Studios</option>
            <option value="vacation_rentals">Vacation rentals</option>
            
            <!-- Add more categories as needed -->
        </select>
        
        <input type="number" name="price" placeholder="Price" required>
        <input type="file" name="image1" required>
        <input type="file" name="image2" required>
        <input type="file" name="image3" required>
        <button type="submit">Add Rental</button>
        <button type="button" id="cancel-button">Cancel</button>
    </form>
</div>

<table>
    <thead>
        <tr>
            <th>Image1</th>
            <th>Image2</th>
            <th>Image3</th>
            <th>Description</th>
            <th>Category</th>
            <th>Price</th>
            <th>Available</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><img src="../image/<?php echo htmlspecialchars($row['image1']); ?>" alt="Image1"></td>
                <td><img src="../image/<?php echo htmlspecialchars($row['image2']); ?>" alt="Image2"></td>
                <td><img src="../image/<?php echo htmlspecialchars($row['image3']); ?>" alt="Image3"></td>
                <td><?php echo htmlspecialchars($row['description']); ?></td>
                <td><?php echo htmlspecialchars($row['category']); ?></td>
                <td><?php echo htmlspecialchars($row['price']); ?></td>
                <td><?php echo htmlspecialchars($row['is_buy'] ? 'Yes' : 'No'); ?></td>
                <td>
    <div class="action-links">
        <a href="edit_buy.php?id=<?php echo $row['buy_id']; ?>">Edit</a>
        <a href="delete_buy.php?id=<?php echo $row['buy_id']; ?>">Delete</a>
    </div>
</td>

            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<script>
    document.getElementById('add-button').onclick = function() {
        document.getElementById('rental-form').style.display = 'block';
    };
    
    document.getElementById('cancel-button').onclick = function() {
        document.getElementById('rental-form').style.display = 'none';
    };
</script>
</body>
</html>

<?php
// Processing the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = htmlspecialchars($_POST['description']);
    $category = htmlspecialchars($_POST['category']); // Category as VARCHAR
    $price = floatval($_POST['price']); // Ensure price is a float

    // File upload handling
    $targetDir = "../image/";
    $images = [];
    $uploadOk = true;

    for ($i = 1; $i <= 3; $i++) {
        $imageName = "image" . $i;
        $targetFile = $targetDir . basename($_FILES[$imageName]["name"]);

        if (getimagesize($_FILES[$imageName]["tmp_name"]) === false || $_FILES[$imageName]["size"] > 2000000) {
            $uploadOk = false;
            break;
        }

        if ($uploadOk && move_uploaded_file($_FILES[$imageName]["tmp_name"], $targetFile)) {
            $images[] = basename($_FILES[$imageName]["name"]);
        } else {
            $uploadOk = false;
            break;
        }
    }

    if ($uploadOk && count($images) === 3) {
        // Insert into the database with category as VARCHAR
        $query = "INSERT INTO buy (user_id, image1, image2, image3, description, category, price, is_buy, date) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("issssds", $user_id, $images[0], $images[1], $images[2], $description, $category, $price);

        if ($stmt->execute()) {
            header("Location: buy_management.php?success=1");
            exit();  // Ensure you exit after redirect
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "File upload failed. Please try again.";
    }
}

// Close the statement and database connection
$stmt->close();
$mysqli->close();

// Flush the buffer and send output to the browser
ob_end_flush();
?>
