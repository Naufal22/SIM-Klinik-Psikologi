<?php
http_response_code(403);
require_once '../config/database.php';
require_once 'auth.php';

// Pastikan session sudah dimulai
session_start();

// Jika pengguna tidak memiliki role, redirect ke halaman login atau beranda
if (!isset($_SESSION['role'])) {
    header("Location: ../index.php");
    exit();
}

// Ambil role pengguna
$userRole = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak</title>
    <link rel="stylesheet" href="../_dist/assets/compiled/css/app.css">
    <script>
        function redirectToDashboard() {
            // Redirect berdasarkan role
            var role = "<?php echo $userRole; ?>";
            var dashboardUrl;

            switch (role) {
                case 'admin':
                    dashboardUrl = '../modules/dashboard/admin/index.php';
                    break;
                case 'psikolog':
                    dashboardUrl = '../modules/dashboard/psikolog/index.php';
                    break;
                case 'pasien':
                    dashboardUrl = '../modules/dashboard/pasien/index.php';
                    break;
                case 'master':
                    dashboardUrl = '../modules/master-data/staff.php';
                    break;
                default:
                    dashboardUrl = '../index.php'; // Fallback ke beranda
            }

            window.location.href = dashboardUrl;
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6 text-center">
                <h1 class="display-4">403</h1>
                <h2>Akses Ditolak</h2>
                <p>Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.</p>
                <button class="btn btn-primary" onclick="redirectToDashboard()">Kembali ke Beranda</button>
            </div>
        </div>
    </div>
</body>
</html>