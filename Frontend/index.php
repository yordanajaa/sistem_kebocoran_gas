<?php
session_start();

// Redirect to dashboard if already logged in
if (isset($_SESSION['user_email']) && $_SESSION['logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

// Otherwise redirect to login
header('Location: login.php');
exit;
?>
