<?php
require_once '../config/session.php';
require_once '../db_config.php';

header('Content-Type: application/json');

try {
    // Cek method request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method tidak diizinkan');
    }

    // Ambil nip_nrp dari POST
    $nip_nrp = isset($_POST['nip_nrp']) ? $conn->real_escape_string($_POST['nip_nrp']) : '';

    // Validasi input
    if (empty($nip_nrp)) {
        throw new Exception('NIP/NRP tidak boleh kosong');
    }

    // Update device_id menjadi NULL
    $query = "UPDATE userabsensi SET device_id = NULL WHERE nip_nrp = ?";
    
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        throw new Exception('Gagal mempersiapkan query');
    }

    $stmt->bind_param('s', $nip_nrp);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Device ID berhasil direset'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ]);
        }
    } else {
        throw new Exception('Gagal mereset device ID');
    }

} catch (Exception $e) {
    error_log("Error in reset_device.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}