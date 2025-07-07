<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once '../../config/database.php';
require_once '../../auth/auth.php';
require_once 'services/TimeSlotService.php';
require_once 'services/PatientAppointmentService.php';
require_once 'services/AdminAppointmentService.php';
require_once 'services/NotificationService.php';
require_once 'factories/AppointmentServiceFactory.php';
require_once 'utils/ValidationUtils.php';

// Pastikan tidak ada output sebelumnya
ob_clean();

// Set header JSON
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Silakan login terlebih dahulu'
    ]);
    exit;
}

// Set user_id sebagai variabel global MySQL untuk trigger
$conn->query("SET @current_user_id = " . $_SESSION['user_id']);


try {
    // Validasi method request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Metode request tidak valid');
    }

    // Validasi action
    $action = $_POST['action'] ?? '';
    if (empty($action)) {
        throw new Exception('Action tidak boleh kosong');
    }

    // Initialize service
    $appointmentService = AppointmentServiceFactory::create($conn, $_SESSION['role']);

    // Handle different actions
    switch ($action) {
        case 'create':
            $result = $appointmentService->createAppointment($_POST, $_SESSION['user_id']);
            break;

        case 'update':
            if (!isset($_POST['id'])) {
                throw new Exception('ID janji temu tidak ditemukan');
            }
            $result = $appointmentService->updateAppointment($_POST['id'], $_POST);
            break;

        case 'updateStatus':
            if (!isset($_POST['id']) || !isset($_POST['status'])) {
                throw new Exception('Data tidak lengkap untuk update status');
            }
            $result = $appointmentService->updateStatus($_POST['id'], $_POST['status'], $_SESSION['user_id']);
            break;

        case 'cancel':
            if (!isset($_POST['id'])) {
                throw new Exception('ID janji temu tidak ditemukan');
            }
            $result = $appointmentService->cancelAppointment($_POST['id'], $_SESSION['user_id']);
            break;

        default:
            throw new Exception('Action tidak valid');
    }

    // Jika berhasil dan action adalah create, buat notifikasi
    if ($result['status'] === 'success' && $action === 'create') {
        $notificationService = new NotificationService($conn);
        $notificationService->createAppointmentNotification(
            $_SESSION['user_id'],
            $result['appointment_id'] ?? null
        );
    }

    // Kirim response
    echo json_encode($result);

} catch (Exception $e) {
    error_log("Error in process.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}