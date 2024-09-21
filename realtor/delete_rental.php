<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$rental_id = $_GET['id'];

// Delete related entries from cart
$query = "DELETE FROM cart WHERE rent_id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $rental_id);
$stmt->execute();
$stmt->close();

// Delete the rental
$query = "DELETE FROM rental WHERE rental_id = ? AND user_id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("ii", $rental_id, $user_id);

if ($stmt->execute()) {
    header("Location: rent_management.php?success=1");
    exit();
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$mysqli->close();
?>
