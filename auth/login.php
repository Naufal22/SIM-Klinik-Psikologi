<?php
session_start();
require_once '../config/database.php';
require_once 'auth.php';
// require_once 'functions.php';

$error = '';
$main_url = "../";

// Jika user sudah login, redirect ke halaman sesuai role
if (isset($_SESSION['user_id'])) {
    redirectBasedOnRole($_SESSION['role']);
}

// Proses login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = $_POST['password'];
    
    // Validasi input
    $error = validateLoginInput($username, $password);
    
    if (empty($error)) {
        $user = authenticateUser($conn, $username, $password);
        
        if ($user) {
            // Debug jika diperlukan (hapus atau comment saat production)
            /*
            echo "Data User:";
            var_dump($user);
            echo "Session Data:";
            var_dump($_SESSION);
            exit;
            */
            
            // Update last login sudah dilakukan di authenticateUser()
            
            // Log aktivitas jika fungsi tersedia
            if (function_exists('logActivity')) {
                logActivity(
                    $conn,
                    $user['id'],
                    'login',
                    'Login berhasil',
                    $_SERVER['REMOTE_ADDR'],
                    $_SERVER['HTTP_USER_AGENT']
                );
            }
            
            // Redirect berdasarkan role
            redirectBasedOnRole($_SESSION['role']);
        } else {
            $error = "Username atau password salah!";
        }
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Klinik Psikologi</title>
    <link rel="shortcut icon" href="<?= $main_url ?>_dist/assets/compiled/svg/favicon.svg" type="image/x-icon">
    <link rel="stylesheet" href="<?= $main_url ?>_dist/assets/compiled/css/app.css">
    <link rel="stylesheet" href="<?= $main_url ?>_dist/assets/compiled/css/app-dark.css">
    <link rel="stylesheet" href="<?= $main_url ?>_dist/assets/compiled/css/auth.css">
    <style>
        #auth {
            background: #435ebe;
            min-height: 100vh;
        }
    </style>
</head>
<body>
    <script src="<?= $main_url ?>_dist/assets/static/js/initTheme.js"></script>
    <div id="auth">
        <div class="row h-100 justify-content-center align-items-center">
            <div class="col-lg-5 col-12">
                <div id="auth-left" class="bg-white p-5 rounded shadow">
                    <div class="auth-logo text-center mb-4">
                        <a href="index.html">
                            <img src="<?= $main_url ?>_dist/gambar/logo-assyifa-2.jpeg" alt="Logo">
                        </a>
                    </div>
                    <h1 class="auth-title text-center">Log in.</h1>
                    <p class="auth-subtitle mb-5 text-center">Silakan login dengan akun Anda.</p>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success">
                            <?php 
                            echo htmlspecialchars($_SESSION['success_message']);
                            unset($_SESSION['success_message']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="text" 
                                   class="form-control form-control-xl" 
                                   name="username" 
                                   placeholder="Username atau Email"
                                   value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>"
                                   required>
                            <div class="form-control-icon">
                                <i class="bi bi-person"></i>
                            </div>
                        </div>
                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="password" 
                                   class="form-control form-control-xl" 
                                   name="password" 
                                   placeholder="Password"
                                   required>
                            <div class="form-control-icon">
                                <i class="bi bi-shield-lock"></i>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block btn-lg shadow-lg mt-5">Log in</button>
                    </form>
                    <div class="text-center mt-5 text-lg fs-4">
                        <p class="text-gray-600">Belum punya akun? 
                            <a href="register.php" class="font-bold">Daftar</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>