<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Destroy the session
session_destroy();

// Removes Security Cookie

if (isset($_COOKIE["security"])) {
    unset($_COOKIE["security"]);
}


header("Location: index.php");
