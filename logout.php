<?php
session_start();

// Sab session variables remove
$_SESSION = [];

// Session destroy
session_destroy();

// Optional: remember-me cookies etc clear karne hon tan ithhe karo

// Login page te redirect
header("Location: index.php");
exit();
