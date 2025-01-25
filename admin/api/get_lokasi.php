<?php
// Matikan semua output error
error_reporting(0);
ini_set('display_errors', 0);

// Hapus output buffer sebelumnya
ob_clean();

require_once '../config/session.php';
require_once '../db_config.php';

// Set header JSON
header('Content-Type: application/json');

try {
    // Debug
    error_log("Executing get_lokasi.php");
    
    $query = "SELECT id, nama_lokasi FROM lokasi_absen ORDER BY nama_lokasi";
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    $lokasi = [];
    while ($row = $result->fetch_assoc()) {
        $lokasi[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $lokasi
    ]);

} catch (Exception $e) {
    error_log("Error in get_lokasi.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
exit;
?>