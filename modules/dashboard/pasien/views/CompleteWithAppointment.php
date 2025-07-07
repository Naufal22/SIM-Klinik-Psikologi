<?php
$title = "Dashboard Pasien - Klinik";
$activePage = 'dashboard-pasien';

require '../../../includes/header-user.php';
require '../../../includes/navbar-user.php';

// Get next appointment
$nextAppointment = $appointmentChecker->getNextAppointment();
$consultationHistory = $dashboardData->getConsultationHistory();
?>

<div class="content-wrapper container">
    <?php 
    require 'components/Welcome.php';
    renderWelcome($conn, $_SESSION['user_id']);
    
    require 'components/AppointmentCard.php';
    renderAppointmentCard($nextAppointment);
    
    require 'components/DataCard.php';
    renderDataCard(true);
    
    require 'components/PsychologistList.php';
    renderPsychologistList($psychologists);
    
    require 'components/ProgressSection.php';
    renderProgressSection($consultationHistory);
    ?>
</div>

<?php require '../../../includes/footer-user.php'; ?>