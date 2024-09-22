<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Include your database connection file
include 'config.php'; // Make sure this file contains your database connection details

// Query to get users with role 'user'
$query = "SELECT id, name, username, email, gender, phone_number, date_of_birth, address FROM users WHERE role = 'admin'";
$result = $mysqli->query($query);

if ($result->num_rows > 0) {
    // Fetch all users
    $users = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $users = [];
}

// Handle form submission for adding a new user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $phone_number = $_POST['phone_number'];
    $date_of_birth = $_POST['date_of_birth'];
    $address = $_POST['address'];
    $password = $_POST['password']; // Get password input

    // Generate a unique username
    $username = strtolower(str_replace(' ', '_', $name)) . rand(1000, 9999);
    
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare the SQL statement, setting role to 'admin' by default
    $stmt = $mysqli->prepare("INSERT INTO users (name, username, email, password, gender, phone_number, date_of_birth, address, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'admin')");
    $stmt->bind_param("ssssssss", $name, $username, $email, $hashed_password, $gender, $phone_number, $date_of_birth, $address);

    if ($stmt->execute()) {
        echo "User added successfully.";
        header("Location: user_management.php"); // Redirect to the user management page
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="css/admin_management.css"> <!-- Link to your CSS file -->
    <style>
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
<header>
        <nav>
            <li><a href="dashboard.php">Home</a></li>
            

            <select class="nav-menu" id="Buy-select" onchange="navigateToPage(this)">
                <option selected disabled>Management</option>
                <option  value="user_management.php">User Management</option>
                <option  value="realtor_management.php">Realtor Management</option>      
                <option value="admin_management.php">Admin Management</option>      
                <option value="rent_management.php">Rent Management</option>      
                <option value="buy_management.php">Buy Management</option>      
            </select>
            <select class="nav-menu" id="Buy-select" onchange="navigateToPage(this)">
                <option selected disabled>Reserved</option>
                <option value="rent_table.php">Rent Reserved</option>
                <option value="buy_table.php">Buy Reserved</option>      
            </select>
            <li><a href="wallet.php">Wallet</a></li>
            <li><a href="settings.php">Settings</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </nav>
    </header><br><br>
<div class="user-table-container">
    <h2>Admin Management</h2>

    <!-- Add User Button -->
    <button onclick="toggleAddUserForm()">Add User</button>

    <!-- Add User Form -->
    <div id="addUserForm" class="hidden">
        <h3>Add New User</h3>
        <form action="" method="POST">
            <input type="text" name="name" placeholder="Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required> <!-- Password field -->
            <input type="text" name="gender" placeholder="Gender" required>
            <input type="text" name="phone_number" placeholder="Phone Number" required>
            <input type="date" name="date_of_birth" placeholder="Date of Birth" required>
            <input type="text" name="address" placeholder="Address" required><br><br>
            <button type="submit" name="add_user">Add User</button>
            <button type="button" onclick="toggleAddUserForm()">Cancel</button>
        </form>
    </div>
<br><br>
    <h3>Existing Users</h3>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Username</th>
                <th>Email</th>
                <th>Gender</th>
                <th>Phone Number</th>
                <th>Date of Birth</th>
                <th>Address</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($users) > 0): ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['gender']); ?></td>
                        <td><?php echo htmlspecialchars($user['phone_number']); ?></td>
                        <td><?php echo htmlspecialchars($user['date_of_birth']); ?></td>
                        <td><?php echo htmlspecialchars($user['address']); ?></td>
                        <td>
                            <form action="delete_user.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8">No users found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<script src="js/header.js"></script>
<script>
    function toggleAddUserForm() {
        const form = document.getElementById('addUserForm');
        form.classList.toggle('hidden');
    }
</script>
</body>
</html>
