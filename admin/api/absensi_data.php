<?php
require_once '../config/session.php';
require_once '../db_config.php';
header('Content-Type: application/json');

try {
    checkLogin();
    
    $action = $_GET['action'] ?? '';
    
    switch($action) {
        case 'list':
            // Parameter paging dan filter
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = 10;
            $offset = ($page - 1) * $limit;
            
            // Filter
            $startDate = $conn->real_escape_string($_GET['start_date'] ?? date('Y-m-d'));
            $endDate = $conn->real_escape_string($_GET['end_date'] ?? date('Y-m-d'));
            $unit = $conn->real_escape_string($_GET['unit'] ?? '');
            $status = $conn->real_escape_string($_GET['status'] ?? '');
            $search = $conn->real_escape_string($_GET['search'] ?? '');
            
            // Base query
            $query = "SELECT a.*, p.nama, p.unit_kerja 
                     FROM absensi a 
                     JOIN pers p ON a.nip_nrp = p.nip_nrp 
                     WHERE a.tanggal BETWEEN ? AND ?";
            
            $params = [$startDate, $endDate];
            $types = "ss";
            
            if (!empty($unit)) {
                $query .= " AND p.unit_kerja = ?";
                $params[] = $unit;
                $types .= "s";
            }
            
            if (!empty($status)) {
                switch($status) {
                    case 'tepat':
                        $query .= " AND a.keterangan_masuk IS NULL";
                        break;
                    case 'terlambat':
                        $query .= " AND a.keterangan_masuk = 'Terlambat'";
                        break;
                    case 'pulang_cepat':
                        $query .= " AND a.keterangan_pulang = 'Pulang Cepat'";
                        break;
                }
            }
            
            if (!empty($search)) {
                $query .= " AND (p.nama LIKE ? OR p.nip_nrp LIKE ?)";
                $searchParam = "%$search%";
                $params[] = $searchParam;
                $params[] = $searchParam;
                $types .= "ss";
            }
            
            // Count total
            $countQuery = "SELECT COUNT(*) as total FROM (" . $query . ") as subquery";
            $stmt = $conn->prepare($countQuery);
            
            if ($stmt === false) {
                throw new Exception('Prepare statement error: ' . $conn->error);
            }
            
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $totalRows = $stmt->get_result()->fetch_assoc()['total'];
            $totalPages = ceil($totalRows / $limit);
            
            // Get data with limit
            $query .= " ORDER BY a.tanggal DESC, a.jam_masuk DESC LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $limit;
            $types .= "ii";
            
            $stmt = $conn->prepare($query);
            if ($stmt === false) {
                throw new Exception('Prepare statement error: ' . $conn->error);
            }
            
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            
            echo json_encode([
                'success' => true,
                'data' => $data,
                'totalPages' => $totalPages,
                'currentPage' => $page
            ]);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    error_log("Error in absensi_data.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>