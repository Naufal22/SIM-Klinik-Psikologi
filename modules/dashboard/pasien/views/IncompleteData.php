<?php
$title = "Dashboard Pasien - Klinik";
$activePage = 'dashboard-pasien';

require '../../../includes/header-user.php';
require '../../../includes/navbar-user.php';
?>

<div class="content-wrapper container">
    <?php 
    require 'components/Welcome.php';
    renderWelcome($conn, $_SESSION['user_id']);
    
    require 'components/AppointmentCard.php';
    renderAppointmentCard();
    
    require 'components/DataCard.php';
    renderDataCard(false);
    
    require 'components/PsychologistList.php';
    renderPsychologistList($psychologists);
    
    require 'components/ProgressSection.php';
    renderProgressSection();
    ?>
</div>

<?php require '../../../includes/footer-user.php'; ?>