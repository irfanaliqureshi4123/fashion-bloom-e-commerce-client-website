<?php
session_start();

// Clear all session data
$_SESSION = [];
session_unset();
session_destroy();

// Start fresh session to store logout message
session_start();
$_SESSION['logout_success'] = "You have logged out successfully!";

// Redirect to login page
header("Location: login.php");
exit;
?>
