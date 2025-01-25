<?php
require_once '../config/session.php';
require_once '../db_config.php';

header('Content-Type: application/json');

// Verify CSRF token
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Validate coordinates
function validateCoordinates($lat, $lng) {
    return is_numeric($lat) && is_numeric($lng) &&
           $lat >= -90 && $lat <= 90 && 
           $lng >= -180 && $lng <= 180;
}

try {
    // Check authentication
    if (!isLoggedIn()) {
        throw new Exception('Unauthorized access', 401);
    }

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate CSRF
    if (!isset($input['csrf_token']) || !verifyCsrfToken($input['csrf_token'])) {
        throw new Exception('Invalid security token', 403);
    }

    // Validate required fields
    $required = ['nama_lokasi', 'latitude', 'longitude', 'radius'];
    foreach ($required as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            throw new Exception("Field $field is required", 400);
        }
    }

    // Sanitize and validate input
    $namaLokasi = filter_var($input['nama_lokasi'], FILTER_SANITIZE_STRING);
    $latitude = filter_var($input['latitude'], FILTER_VALIDATE_FLOAT);
    $longitude = filter_var($input['longitude'], FILTER_VALIDATE_FLOAT);
    $radius = filter_var($input['radius'], FILTER_VALIDATE_INT);

    if (!validateCoordinates($latitude, $longitude)) {
        throw new Exception('Invalid coordinates', 400);
    }

    if ($radius < 10 || $radius > 1000) {
        throw new Exception('Radius must be between 10-1000 meters', 400);
    }

    // Determine if insert or update
    $isUpdate = isset($input['id']) && !empty($input['id']);
    
    if ($isUpdate) {
        $stmt = $conn->prepare("UPDATE lokasi_absen SET 
            nama_lokasi = ?, 
            latitude = ?, 
            longitude = ?, 
            radius = ?,
            updated_at = CURRENT_TIMESTAMP 
            WHERE id = ? AND deleted_at IS NULL");
            
        $stmt->bind_param("sddii", 
            $namaLokasi, 
            $latitude, 
            $longitude, 
            $radius,
            $input['id']
        );
    } else {
        $stmt = $conn->prepare("INSERT INTO lokasi_absen 
            (nama_lokasi, latitude, longitude, radius, created_at) 
            VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)");
            
        $stmt->bind_param("sddi", 
            $namaLokasi, 
            $latitude, 
            $longitude, 
            $radius
        );
    }

    if (!$stmt->execute()) {
        throw new Exception($stmt->error, 500);
    }

    echo json_encode([
        'success' => true,
        'message' => $isUpdate ? 'Lokasi berhasil diperbarui' : 'Lokasi berhasil ditambahkan'
    ]);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}