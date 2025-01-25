<?php
require_once '../config/session.php';
require_once '../db_config.php';

header('Content-Type: application/json');

try {
    $query = "SELECT DISTINCT unit_kerja FROM pers ORDER BY unit_kerja";
    $result = $conn->query($query);
    
    $units = [];
    while ($row = $result->fetch_assoc()) {
        $units[] = $row['unit_kerja'];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $units
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}