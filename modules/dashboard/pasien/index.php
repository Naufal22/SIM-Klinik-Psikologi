
<?php
session_start();
require_once '../../../auth/auth.php';
require_once '../../../config/database.php';
require_once 'functions/UserDataChecker.php';
require_once 'functions/AppointmentChecker.php';
require_once 'functions/DashboardData.php';

// Cek role dan session
requireRole(ROLE_PASIEN);
checkSessionTimeout();

// Inisialisasi services
$userChecker = new UserDataChecker($conn, $_SESSION['user_id']);
$appointmentChecker = new AppointmentChecker($conn, $_SESSION['user_id']);
$dashboardData = new DashboardData($conn, $_SESSION['user_id']);

// Ambil data user
$userData = $dashboardData->getUserData();
$psychologists = $dashboardData->getPsychologists();

// Tentukan view yang akan ditampilkan
if (!$userChecker->isDataComplete()) {
    require 'views/IncompleteData.php';
} elseif (!$appointmentChecker->hasActiveAppointment()) {
    require 'views/CompleteNoAppointment.php';
} else {
    require 'views/CompleteWithAppointment.php';
}
?>
