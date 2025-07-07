<?php
session_start();
require_once '../config/database.php';
require_once '../auth/auth.php';

requireRole(ROLE_PASIEN);
checkSessionTimeout();

$title = "Skrining - Klinik";
$activePage = 'skrining';


require '../includes/header-user.php';
require '../includes/navbar-user.php';
?>

<div id="main-content">
    <div class="row justify-content-center mt-5">
        <div class="col-md-6 text-center">
            <h1 class="display-4">Coming Soon</h1>
            <h2>Fitur Masih Dalam Pengembangan</h2>
            <p>Maaf, fitur ini masih dalam tahap pengembangan. Kami sedang berusaha memberikan yang terbaik untuk Anda.</p>
            <a href="<?php echo getDashboardUrl($_SESSION['role']); ?>" class="btn btn-primary">Kembali ke Beranda</a>
        </div>
    </div>
</div>





<?php require '../includes/footer-user.php'; ?>