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
            
            $search = $conn->real_escape_string($_GET['search'] ?? '');
            $unit = $conn->real_escape_string($_GET['unit'] ?? '');
            $lokasi = $conn->real_escape_string($_GET['lokasi'] ?? '');
            
            // Base query
            $baseQuery = "FROM pers p 
                LEFT JOIN userabsensi u ON p.nip_nrp = u.nip_nrp
                LEFT JOIN lokasi_absen l1 ON u.id_lokasi_1 = l1.id
                LEFT JOIN lokasi_absen l2 ON u.id_lokasi_2 = l2.id
                WHERE 1=1";
            
            // Add filters
            if ($search) {
                $baseQuery .= " AND (p.nama LIKE '%$search%' OR p.nip_nrp LIKE '%$search%')";
            }
            if ($unit) {
                $baseQuery .= " AND p.unit = '$unit'";
            }
            if ($lokasi) {
                $baseQuery .= " AND (u.id_lokasi_1 = '$lokasi' OR u.id_lokasi_2 = '$lokasi')";
            }
            
            // Get total records
            $countQuery = "SELECT COUNT(*) as total " . $baseQuery;
            $totalResult = $conn->query($countQuery);
            $totalRows = $totalResult->fetch_assoc()['total'];
            $totalPages = ceil($totalRows / $limit);
            
            // Get data
            $query = "SELECT p.*, u.device_id, 
                     l1.nama as lokasi_1, l2.nama as lokasi_2 " . 
                     $baseQuery . " LIMIT $offset, $limit";
            
            $result = $conn->query($query);
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

        case 'update_lokasi':
            $data = json_decode(file_get_contents('php://input'), true);
            $nip_nrp = $conn->real_escape_string($data['nip_nrp']);
            $lokasi1 = $conn->real_escape_string($data['lokasi1']);
            $lokasi2 = $data['lokasi2'] ? $conn->real_escape_string($data['lokasi2']) : null;
            
            $query = "UPDATE userabsensi SET 
                     id_lokasi_1 = ?, id_lokasi_2 = ?
                     WHERE nip_nrp = ?";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sss", $lokasi1, $lokasi2, $nip_nrp);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception("Gagal update lokasi");
            }
            break;

        case 'reset_device':
            $nip_nrp = $conn->real_escape_string($_POST['nip_nrp']);
            
            $query = "UPDATE userabsensi SET device_id = NULL 
                     WHERE nip_nrp = ?";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $nip_nrp);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception("Gagal reset device ID");
            }
            break;

        case 'reset_password':
            $nip_nrp = $conn->real_escape_string($_POST['nip_nrp']);
            $default_password = password_hash('123456', PASSWORD_DEFAULT);
            
            $query = "UPDATE userabsensi SET password = ? 
                     WHERE nip_nrp = ?";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ss", $default_password, $nip_nrp);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception("Gagal reset password");
            }
            break;

        case 'get_lokasi':
            $query = "SELECT id, nama FROM lokasi_absen ORDER BY nama";
            $result = $conn->query($query);
            $lokasi = [];
            
            while ($row = $result->fetch_assoc()) {
                $lokasi[] = $row;
            }
            
            echo json_encode(['success' => true, 'data' => $lokasi]);
            break;

        case 'get_unit':
            $query = "SELECT DISTINCT unit FROM pers ORDER BY unit";
            $result = $conn->query($query);
            $unit = [];
            
            while ($row = $result->fetch_assoc()) {
                $unit[] = $row['unit'];
            }
            
            echo json_encode(['success' => true, 'data' => $unit]);
            break;

        default:
            throw new Exception("Invalid action");
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>