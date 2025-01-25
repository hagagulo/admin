<?php
require_once '../config/session.php';
require_once '../db_config.php';

header('Content-Type: application/json');

try {
    // Ambil data yang dikirim
    $nip_nrp = isset($_POST['nip_nrp']) ? $conn->real_escape_string($_POST['nip_nrp']) : '';
    $lokasi1 = isset($_POST['lokasi1']) ? $conn->real_escape_string($_POST['lokasi1']) : '';
    $lokasi2 = !empty($_POST['lokasi2']) ? $conn->real_escape_string($_POST['lokasi2']) : null;

    // Validasi input
    if (empty($nip_nrp) || empty($lokasi1)) {
        throw new Exception('NIP/NRP dan Lokasi 1 harus diisi');
    }

    // Update lokasi di tabel userabsensi
    $query = "UPDATE userabsensi SET id_lokasi_1 = ?, id_lokasi_2 = ? WHERE nip_nrp = ?";
    
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        throw new Exception('Gagal mempersiapkan query');
    }

    // Gunakan 'i' untuk integer pada bind_param
    $stmt->bind_param('iis', $lokasi1, $lokasi2, $nip_nrp);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Lokasi berhasil diupdate'
        ]);
    } else {
        throw new Exception('Gagal mengupdate data: ' . $stmt->error);
    }

} catch (Exception $e) {
    error_log("Error in update_lokasi.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}