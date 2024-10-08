<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Include your database connection file
include 'config.php'; // Make sure this file contains your database connection details

// Query to get users with role 'realtor'
$query = "SELECT id, name, username, email, gender, phone_number, date_of_birth, address FROM users WHERE role = 'realtor'";
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
    <title>User Management</title>
    <link rel="stylesheet" href="css/realtor_management.css"> <!-- Link to your CSS file -->
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

    <div class="user-table-container">
        <h2>User Management</h2>
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
                                <form class="reminder" action="send_reminder.php" method="POST" onsubmit="return confirm('Send reminder to this user?');">
                                    <input type="hidden" name="email" value="<?php echo $user['email']; ?>">
                                    <button type="submit">Reminder</button><br><br>
                                </form>
                                <form action="delete_realtor.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
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
</body>

</html>
<style>

</style>