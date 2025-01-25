<?php
session_start();
require_once '../../db_config.php';

// Tambahkan error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    $level = $conn->real_escape_string($_POST['level']);

    $query = "SELECT * FROM admin_users WHERE username = ? AND level = ?";
    $stmt = $conn->prepare($query);
    
    if ($stmt === false) {
        error_log("Prepare statement error: " . $conn->error);
        die("Kesalahan persiapan statement: " . $conn->error);
    }
    
    $stmt->bind_param("ss", $username, $level);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Tambahkan log untuk debugging
        error_log("User ditemukan: " . print_r($user, true));
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_level'] = $user['level'];
            $_SESSION['admin_nama'] = $user['username']; // Tambahkan ini
            
            header("Location: ../../index.php");
            exit;
        } else {
            error_log("Verifikasi password gagal");
            header("Location: login.php?error=Username atau password salah");
            exit;
        }
    }

    header("Location: login.php?error=Username atau password salah");
    exit;
}

header("Location: login.php");
exit;
?>