<?php
require_once '../../config/database.php';
require_once 'functions.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID tidak valid']);
    exit;
}

$jadwal = getJadwalById($_GET['id']);

if (!$jadwal) {
    echo json_encode(['status' => 'error', 'message' => 'Jadwal tidak ditemukan']);
    exit;
}

echo json_encode($jadwal);
?>