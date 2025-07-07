<?php
session_start();
require_once '../../../auth/session_check.php';
checkSession();
require_once '../../../config/database.php';
require_once './functions/statistics.php';
require_once './functions/formatters.php';
require_once './functions/queries.php';
require_once '../../../auth/auth.php';
// require_once '../../../auth/functions.php';

checkSessionTimeout();


$title = "Dashboard Admin - Klinik";
$activePage = 'dashboard';

// Get all required data
$totalCounts = getTotalCounts($conn);
$appointmentStats = getAppointmentStats($conn);
$visitTrend = getVisitTrend($conn);
$todayAppointments = getTodayAppointments($conn);
$activePsychologists = getActivePsychologists($conn);
$weeklyStats = getWeeklyStats($conn);
$serviceStats = getServiceStats($conn);
$recentActivities = getRecentActivities($conn);

require '../../../includes/header.php';
require '../../../includes/sidebar.php';
require '../../../includes/navbar.php';

// Include component files
require './components/styles.php';
require './components/main_content.php';
require './components/scripts.php';


require '../../../includes/footer.php';
?>