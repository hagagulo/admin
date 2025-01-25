<?php
require_once '../config/session.php';
require_once '../db_config.php';

header('Content-Type: application/json');

try {
    // Get parameters dengan cleaning
    $unit = isset($_GET['unit']) ? $conn->real_escape_string($_GET['unit']) : '';
    $lokasi = isset($_GET['lokasi']) ? $conn->real_escape_string($_GET['lokasi']) : '';
    $search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

    // Build query dasar
    $query = "SELECT p.*, u.device_id, l1.nama_lokasi as lokasi_1, l2.nama_lokasi as lokasi_2 
              FROM pers p 
              LEFT JOIN userabsensi u ON p.nip_nrp = u.nip_nrp
              LEFT JOIN lokasi_absen l1 ON u.id_lokasi_1 = l1.id
              LEFT JOIN lokasi_absen l2 ON u.id_lokasi_2 = l2.id
              WHERE 1=1";

    $params = [];
    
    // Filter unit kerja jika dipilih
    if (!empty($unit)) {
        $query .= " AND p.unit_kerja = ?";
        $params[] = $unit;
    }
    
    // Filter lokasi jika dipilih
    if (!empty($lokasi)) {
        $query .= " AND (u.id_lokasi_1 = ? OR u.id_lokasi_2 = ?)";
        $params[] = $lokasi;
        $params[] = $lokasi;
    }
    
    // Filter pencarian nama atau NIP/NRP
    if (!empty($search)) {
        $query .= " AND (p.nama LIKE ? OR p.nip_nrp LIKE ?)";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
    }

    // Debugging
    error_log("Query: " . $query);
    error_log("Parameters: " . print_r($params, true));

    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode([
        'success' => true,
        'data' => $data
    ]);

} catch (Exception $e) {
    error_log("Error in get_personil.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>