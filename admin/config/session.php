<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function checkLogin() {
    if (!isLoggedIn()) {
        header("Location: ../modules/auth/login.php");
        exit;
    }
}

function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        header("Location: ../../index.php");
        exit;
    }
}
?>