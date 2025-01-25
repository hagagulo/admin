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

    // Set default password (123456) dengan MD5
    $default_password = md5('123456');

    // Update password
    $query = "UPDATE userabsensi SET password = ? WHERE nip_nrp = ?";
    
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        throw new Exception('Gagal mempersiapkan query');
    }

    $stmt->bind_param('ss', $default_password, $nip_nrp);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Password berhasil direset ke default (123456)'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ]);
        }
    } else {
        throw new Exception('Gagal mereset password');
    }

} catch (Exception $e) {
    error_log("Error in reset_password.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}