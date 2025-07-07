<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_GET['role'])) {
    echo json_encode(['error' => 'Role not specified']);
    exit;
}

$role = $_GET['role'];
$references = [];

try {
    switch ($role) {
        case 'psikolog':
            $query = "SELECT id, nama as nama FROM psikolog WHERE status = 'Aktif'";
            break;
            
        case 'pasien':
            $query = "SELECT id, nama_lengkap as nama FROM pasien WHERE status = 'Aktif'";
            break;
            
        default:
            echo json_encode(['error' => 'Invalid role']);
            exit;
    }

    $result = query($query);
    while ($row = $result->fetch_assoc()) {
        $references[] = $row;
    }

    echo json_encode($references);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}