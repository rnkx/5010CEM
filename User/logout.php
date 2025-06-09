<?php 
// Start output buffering (optional) and initialize session
ob_start();
session_start();

// Include configuration (database connection, BASE_URL, etc.)
include 'admin/inc/config.php';

// Remove the customer session data to log out the user
unset($_SESSION['customer']);

// Redirect to login page after logout
header("Location: " . BASE_URL . "login.php");
exit; // Ensure no further code is executed
?>
