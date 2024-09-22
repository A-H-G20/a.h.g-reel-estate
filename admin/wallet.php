<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Include your database connection file
include 'config.php'; // Make sure this file contains your database connection details

// Query to get users with role 'user' or 'realtor'
$query = "SELECT id, name, username, email, gender, phone_number, role, date_of_birth, address FROM users WHERE role = 'user' OR role = 'realtor'";
$result = $mysqli->query($query);

if ($result->num_rows > 0) {
    // Fetch all users
    $users = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $users = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wallet Management</title>
    <link rel="stylesheet" href="css/wallet.css"> <!-- Link to your CSS file -->
 
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
            <li><a href="wallet.php">Wallet</a></li>
            <li><a href="settings.php">Settings</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </nav>
    </header><br><br>
    <div class="user-table-container">
        <h2>Wallet Management</h2>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
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
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            <td><?php echo htmlspecialchars($user['gender']); ?></td>
                            <td><?php echo htmlspecialchars($user['phone_number']); ?></td>
                            <td><?php echo htmlspecialchars($user['date_of_birth']); ?></td>
                            <td><?php echo htmlspecialchars($user['address']); ?></td>
                            <td>
                                <button onclick="showAmountForm(<?php echo $user['id']; ?>)">Add</button>
                                <div class="overlay" id="overlay-<?php echo $user['id']; ?>"></div>
                                <div class="amount-form" id="form-<?php echo $user['id']; ?>">
                                    <form action="add_amount.php" method="POST" onsubmit="return confirm('Are you sure you want to add this amount?');">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <input type="number" name="amount" placeholder="Amount" required>
                                        <button type="submit">Submit</button>
                                        <button type="button" onclick="hideAmountForm(<?php echo $user['id']; ?>)">Cancel</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9">No users found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <script src="js/header.js"></script>
    <script>
        function showAmountForm(userId) {
            document.getElementById('overlay-' + userId).style.display = 'block'; // Show the overlay
            document.getElementById('form-' + userId).style.display = 'block'; // Show the form
        }

        function hideAmountForm(userId) {
            document.getElementById('overlay-' + userId).style.display = 'none'; // Hide the overlay
            document.getElementById('form-' + userId).style.display = 'none'; // Hide the form
        }

        // Hide the form and overlay if the overlay is clicked
        document.querySelectorAll('.overlay').forEach(overlay => {
            overlay.addEventListener('click', function() {
                const userId = this.id.split('-')[1]; // Extract user ID from overlay ID
                hideAmountForm(userId);
            });
        });
    </script>
</body>

</html>