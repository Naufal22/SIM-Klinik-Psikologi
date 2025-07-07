<?php
header('Content-Type: application/json');
require_once '../../config/database.php';
require_once '../../auth/auth.php';
require_once 'services/TimeSlotService.php';

try {
    // Validate request
    if (!isset($_POST['psikolog_id']) || !isset($_POST['tanggal']) || !isset($_POST['durasi'])) {
        throw new Exception('Missing required parameters');
    }

    $psikolog_id = $_POST['psikolog_id'];
    $tanggal = $_POST['tanggal'];
    $durasi = $_POST['durasi'];

    // Validate date format
    if (!DateTime::createFromFormat('Y-m-d', $tanggal)) {
        throw new Exception('Invalid date format');
    }

    // Get available slots
    $timeSlotService = new TimeSlotService($conn);
    $slots = $timeSlotService->getAvailableSlots($psikolog_id, $tanggal, $durasi);

    echo json_encode($slots);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}