<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require_once '../db_config.php';
require_once '../config/session.php';

try {
    // Validasi session admin
    checkLogin();

    // Ambil data JSON dari request body
    $data = json_decode(file_get_contents('php://input'), true);

    // Validasi input yang diperlukan
    if (!isset($data['nip_nrp']) || !isset($data['tanggal'])) {
        throw new Exception('NIP/NRP dan Tanggal harus diisi');
    }

    // Persiapkan data
    $nip_nrp = $conn->real_escape_string($data['nip_nrp']);
    $tanggal = $conn->real_escape_string($data['tanggal']);
    $jam_masuk = !empty($data['jam_masuk']) ? $conn->real_escape_string($data['jam_masuk']) : null;
    $jam_pulang = !empty($data['jam_pulang']) ? $conn->real_escape_string($data['jam_pulang']) : null;
    $keterangan = !empty($data['keterangan']) ? $conn->real_escape_string($data['keterangan']) : '';

    // Tentukan status absensi
    $keterangan_masuk = null;
    $keterangan_pulang = null;

    if ($jam_masuk) {
        $jam = (int)substr($jam_masuk, 0, 2) + (int)substr($jam_masuk, 3, 2)/60;
        if ($jam > 8) {
            $keterangan_masuk = 'Terlambat';
        }
    }

    if ($jam_pulang) {
        $jam = (int)substr($jam_pulang, 0, 2) + (int)substr($jam_pulang, 3, 2)/60;
        $isJumat = date('N', strtotime($tanggal)) == 5;
        if (($isJumat && $jam < 16) || (!$isJumat && $jam < 15.5)) {
            $keterangan_pulang = 'Pulang Cepat';
        }
    }

    // Cek apakah data absensi sudah ada untuk tanggal tersebut
    $check_query = "SELECT id FROM absensi WHERE nip_nrp = ? AND tanggal = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("ss", $nip_nrp, $tanggal);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        // Update data yang sudah ada
        $query = "UPDATE absensi SET 
                  jam_masuk = ?, 
                  jam_pulang = ?, 
                  keterangan_masuk = ?,
                  keterangan_pulang = ?,
                  keterangan = ?,
                  input_manual = 1,
                  updated_at = CURRENT_TIMESTAMP
                  WHERE nip_nrp = ? AND tanggal = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssss", 
            $jam_masuk, 
            $jam_pulang, 
            $keterangan_masuk,
            $keterangan_pulang,
            $keterangan,
            $nip_nrp,
            $tanggal
        );
    } else {
        // Insert data baru
        $query = "INSERT INTO absensi (
                    nip_nrp, tanggal, jam_masuk, jam_pulang, 
                    keterangan_masuk, keterangan_pulang, keterangan, 
                    input_manual, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 1, CURRENT_TIMESTAMP)";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssss", 
            $nip_nrp,
            $tanggal,
            $jam_masuk,
            $jam_pulang,
            $keterangan_masuk,
            $keterangan_pulang,
            $keterangan
        );
    }

    // Eksekusi query
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Data absensi berhasil disimpan'
        ]);
    } else {
        throw new Exception("Error saat menyimpan data: " . $stmt->error);
    }

} catch (Exception $e) {
    error_log("Error in input_manual_absen.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>