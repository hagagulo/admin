<?php
// Aktifkan error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Sertakan konfigurasi
require_once '../db_config.php';
require_once '../config/session.php';

// Set header JSON
header('Content-Type: application/json');

// Fungsi untuk membersihkan output sebelumnya
ob_clean();

try {
    // Pastikan user login
    checkLogin();

    // Fungsi ambil total personil
    function getTotalPersonil($conn) {
        $query = "SELECT COUNT(*) as total FROM pers";
        $result = $conn->query($query);
        
        if (!$result) {
            error_log("Query total personil error: " . $conn->error);
            return 0;
        }
        
        $row = $result->fetch_assoc();
        return $row['total'];
    }

    // Fungsi ambil absensi hari ini
    function getAbsensiHariIni($conn) {
        $today = date('Y-m-d');
        $query = "
            SELECT 
                COUNT(*) as total_hadir,
                SUM(CASE WHEN keterangan_masuk = 'Terlambat' THEN 1 ELSE 0 END) as total_terlambat
            FROM absensi 
            WHERE tanggal = '$today'
        ";
        
        $result = $conn->query($query);
        
        if (!$result) {
            error_log("Query absensi error: " . $conn->error);
            return ['total_hadir' => 0, 'total_terlambat' => 0];
        }
        
        return $result->fetch_assoc();
    }

    // Fungsi ambil personil belum absen
    function getBelumAbsen($conn) {
        $today = date('Y-m-d');
        $query = "
            SELECT p.* FROM pers p 
            LEFT JOIN absensi a ON p.nip_nrp = a.nip_nrp AND a.tanggal = '$today'
            WHERE a.id IS NULL
        ";
        
        $result = $conn->query($query);
        
        if (!$result) {
            error_log("Query belum absen error: " . $conn->error);
            return [];
        }
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }



    function getKehadiranMingguan($conn) {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $query = "
                SELECT 
                    COUNT(*) as total_hadir,
                    SUM(CASE WHEN keterangan_masuk = 'Terlambat' THEN 1 ELSE 0 END) as terlambat,
                    SUM(CASE WHEN keterangan_pulang = 'Pulang Cepat' THEN 1 ELSE 0 END) as pulang_cepat
                FROM absensi 
                WHERE tanggal = ?
            ";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $date);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            $data[] = [
                'tanggal' => date('d/m', strtotime($date)),
                'hadir' => (int)$row['total_hadir'],
                'terlambat' => (int)$row['terlambat'],
                'pulang_cepat' => (int)$row['pulang_cepat']
            ];
        }
        return $data;
    }
    
    // Update bagian try-catch untuk menambahkan data grafik
    
        
        $data = [
            'total_personil' => getTotalPersonil($conn),
            'absensi_hari_ini' => getAbsensiHariIni($conn),
            'belum_absen' => getBelumAbsen($conn),
            'kehadiran_mingguan' => getKehadiranMingguan($conn) // Tambahkan ini
        ];
        // ... kode lainnya tetap sama
    



    

    // Kumpulkan data
 

    // Debug: Periksa data sebelum encode
    error_log("Data yang akan di-encode: " . print_r($data, true));

    // Kirim JSON dengan opsi untuk debugging
    $json_output = json_encode($data, JSON_PRETTY_PRINT);
    
    // Periksa apakah JSON valid
    if ($json_output === false) {
        $json_error = json_last_error_msg();
        error_log("JSON Encoding Error: " . $json_error);
        throw new Exception("Gagal membuat JSON: " . $json_error);
    }

    echo $json_output;

} catch (Exception $e) {
    // Tangkap dan catat error
    error_log("Kesalahan: " . $e->getMessage());
    
    // Kirim respon error yang valid dalam format JSON
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
?>