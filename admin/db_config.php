<?php
// Aktifkan error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'personil';

try {
    $conn = new mysqli($host, $username, $password, $database);
    
    if ($conn->connect_errno) {
        // Log error koneksi dengan detail
        error_log("Koneksi database gagal: " . $conn->connect_error);
        throw new Exception("Koneksi database gagal: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    // Catat error ke file log
    error_log("Kesalahan database: " . $e->getMessage());
    
    // Tampilkan pesan error
    die("Koneksi database gagal: " . $e->getMessage());
}
?>