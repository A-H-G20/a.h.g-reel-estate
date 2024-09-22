<?php
session_start();
include 'config.php'; // Include your database connection

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);

    // Prepare and execute the delete query
    $query = "DELETE FROM users WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        // Redirect back to user management with success message
        header("Location: user_management.php?success=User deleted successfully.");
    } else {
        // Redirect back with error message
        header("Location: user_management.php?error=Error deleting user.");
    }
    
    $stmt->close();
}
?>
