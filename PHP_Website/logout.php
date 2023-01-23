<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Destroy the session
session_destroy();
// Removes the security cookie
setcookie("security", "", time() - 3600, "/");



header("Location: index.php");

?>