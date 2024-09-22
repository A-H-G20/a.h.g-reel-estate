<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require 'config.php'; // Database connection

// Check if the ID is provided
if (isset($_POST['id'])) {
    $id = (int)$_POST['id']; // Convert ID to integer to avoid SQL injection

    // Prepare the delete query
    $deleteQuery = "DELETE FROM rental_reserved WHERE id = ?";
    $stmt = $mysqli->prepare($deleteQuery);
    
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();

        // Check if the item was deleted successfully
        if ($stmt->affected_rows > 0) {
            echo "Item deleted successfully.";
        } else {
            echo "Item not found or could not be deleted.";
        }
        
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $mysqli->error;
    }
} else {
    echo "No ID provided.";
}

// Close the database connection
$mysqli->close();

// Redirect back to the reserved items page
header("Location: rent_table.php"); // Adjust to your reserved items page
exit();
?>
