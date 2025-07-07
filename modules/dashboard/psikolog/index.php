<?php
session_start();

require_once '../../../auth/auth.php';
require_once '../../../config/database.php';
require_once 'functions/statistics.php';
require_once 'functions/formatters.php';

requireRole(ROLE_PSIKOLOG);
checkSessionTimeout();

$title = "Dashboard Psikolog - Klinik";
$activePage = 'dashboard-psikolog';

// Get current user data
$currentUser = getCurrentUser($conn);
if (!$currentUser || !$currentUser['reference_id']) {
    $_SESSION['error_message'] = "Data psikolog tidak ditemukan.";
    header("Location: " . $main_url . "auth/logout.php");
    exit();
}

// Get psikolog_id from user's reference_id
$psikolog_id = $currentUser['reference_id'];

// Get statistics
$activePatients = getActivePatients($psikolog_id);
$todayAppointments = getTodayAppointments($psikolog_id);
$todayCount = getTodayAppointmentCount($psikolog_id);
$pendingNotes = getPendingNotesCount($psikolog_id);
$followUps = getFollowUpReminders($psikolog_id);
$recentNotes = getRecentConsultationNotes($psikolog_id);

// Get psikolog details
$stmt = $conn->prepare("SELECT * FROM psikolog WHERE id = ?");
$stmt->bind_param("i", $psikolog_id);
$stmt->execute();
$psikologData = $stmt->get_result()->fetch_assoc();

require '../../../includes/header.php';
require '../../../includes/sidebar.php';
require '../../../includes/navbar.php';

// Include styles
require 'components/styles.php';
?>

<div id="main-content">
    <div class="page-heading">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Dashboard Psikolog</h3>
                <p class="text-subtitle text-muted">Selamat datang, <?= htmlspecialchars($psikologData['nama']) ?>! Berikut ringkasan aktivitas Anda hari ini.</p>
            </div>
        </div>
    </div>

    <div class="page-content">
        <section class="row">
            <?php 
            // Include components
            require 'components/sections/stats_cards.php';
            renderStatsCards($activePatients, $todayCount, $pendingNotes, mysqli_num_rows($followUps));
            
            // Include main content sections
            require 'components/sections/appointments.php';
            renderTodayAppointments($todayAppointments);
            
            echo '<div class="col-12 col-lg-4">';
            require 'components/sections/quick_actions.php';
            renderQuickActions();
            
            require 'components/sections/follow_up.php';
            renderFollowUp($followUps);
            echo '</div>';
            
            require 'components/sections/recent_notes.php';
            renderRecentNotes($recentNotes);
            ?>
        </section>
    </div>

    <?php
    require '../../../includes/footer.php';
    require 'components/scripts.php';
    ?>
</div>
</body>
</html>