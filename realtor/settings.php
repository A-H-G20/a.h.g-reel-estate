<?php
include 'config.php';
session_start();
// Get the user ID from the session or URL parameter
$user_id = $_SESSION['user_id']; // or $_GET['user_id']

// Query to retrieve the user information
$query = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($mysqli, $query);

// Check if the query was successful
if (!$result) {
  die("Query failed: " . mysqli_error($mysqli));
}

// Fetch the user information
$user = mysqli_fetch_assoc($result);

// Display the user information and allow editing
?>
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
<div class="user-info">
  <link rel="stylesheet" href="css/settings.css">
  <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
    <label for="name">Full Name:</label>
    <input type="text" name="name" required value="<?php echo htmlspecialchars($user['name']); ?>" placeholder="Full Name">

    <label for="phone_number">Phone Number:</label>
    <input type="text" name="phone_number" required value="<?php echo htmlspecialchars($user['phone_number']); ?>" placeholder="Phone Number">

    <label for="address">Address:</label>
    <input type="text" name="address" required value="<?php echo htmlspecialchars($user['address']); ?>" placeholder="Address">

    <label for="date_of_birth">Date of Birth:</label>
    <input type="text" name="date_of_birth" required value="<?php echo htmlspecialchars($user['date_of_birth']); ?>" placeholder="Date of Birth">

    <label for="gender">Gender:</label>
    <select id="gender" name="gender" required>
      <option value="">Select Gender</option>
      <option value="Male" <?php echo $user['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
      <option value="Female" <?php echo $user['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
      <option value="Other" <?php echo $user['gender'] == 'Other' ? 'selected' : ''; ?>>Other</option>
    </select>

    <input type="submit" name="submit" value="Save Changes">

    <button type="button" id="change-password-btn">Change Password</button>

    <div id="change-password-form" style="display: none;">
      <h2>Change Password</h2>
      <label for="old_password">Old Password:</label>
      <input type="password" name="old_password" required>

      <label for="new_password">New Password:</label>
      <input type="password" name="new_password" required>

      <label for="confirm_password">Confirm Password:</label>
      <input type="password" name="confirm_password" required>

      <button type="submit" name="change_password" value="Change Password">Change Password</button>
      <button type="button" id="cancel-btn">Cancel</button>
    </div>
  </form>
</div>

<script>
  document.getElementById('change-password-btn').addEventListener('click', function() {
    document.getElementById('change-password-form').style.display = 'block';
  });

  document.getElementById('cancel-btn').addEventListener('click', function() {
    document.getElementById('change-password-form').style.display = 'none';
  });
</script>

<?php
// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Get the updated user information
  $name = $_POST['name'];
  $phone_number = $_POST['phone_number'];
  $address = $_POST['address'];
  $date_of_birth = $_POST['date_of_birth'];
  $gender = $_POST['gender'];

  // Handle file upload


  // Update the user information in the database
  $query = "UPDATE users SET ";
  $query .= "name = '$name', ";

  $query .= "phone_number = '$phone_number', ";
  $query .= "address = '$address', ";
  $query .= "date_of_birth = '$date_of_birth', ";
  $query .= "gender = '$gender' ";
  $query .= "WHERE id = '$user_id'";

  $result = mysqli_query($mysqli, $query);

  // Check if the update was successful
  if (!$result) {
    die("Update failed: " . mysqli_error($mysqli));
  } else {
    echo "Changes saved successfully!";
    header("location: settings.php");
  }
}

// Check if the change password form has been submitted
if (isset($_POST['change_password'])) {
  $old_password = $_POST['old_password'];
  $new_password = $_POST['new_password'];
  $confirm_password = $_POST['confirm_password'];

  // Check if the old password is correct
  $query = "SELECT password FROM users WHERE id = '$user_id'";
  $result = mysqli_query($mysqli, $query);
  $row = mysqli_fetch_assoc($result);
  $hashed_old_password = $row['password'];

  if (password_verify($old_password, $hashed_old_password)) {
    // Check if the new password and confirm password match
    if ($new_password == $confirm_password) {
      // Hash the new password
      $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);

      // Update the password in the database
      $query = "UPDATE users SET password = '$hashed_new_password' WHERE id = '$user_id'";
      $result = mysqli_query($mysqli, $query);

      // Check if the update was successful
      if (!$result) {
        die("Update failed: " . mysqli_error($mysqli));
      } else {
        echo "Password changed successfully!";
        header("location: settings.php");
      }
    } else {
      echo "New password and confirm password do not match.";
    }
  } else {
    echo "Old password is incorrect.";
  }
}

mysqli_close($mysqli);
?>