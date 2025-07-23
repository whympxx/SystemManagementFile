<?php
require_once 'config/auth.php';

// Logout user
logoutUser();

// Redirect to login with success message
header("Location: login.php?message=logout_success");
exit;
?>
