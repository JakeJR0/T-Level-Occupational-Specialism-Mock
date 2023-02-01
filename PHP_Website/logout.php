<?php

// Start the session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Destroy the session
session_destroy();

// Removes Security Cookie

if (isset($_COOKIE["security"])) {
    unset($_COOKIE["security"]);
}

// Redirect to the index page
header("Location: index.php");
