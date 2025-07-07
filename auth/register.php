<?php
session_start();
include_once '../config/database.php';
include_once 'functions.php';

$errors = [];
$main_url = "../";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi input
    $nama_lengkap = filter_input(INPUT_POST, 'nama_lengkap', FILTER_SANITIZE_STRING);
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    // Validasi input
    if (empty($nama_lengkap)) {
        $errors[] = "Nama lengkap harus diisi";
    }

    if (empty($username)) {
        $errors[] = "Username harus diisi";
    }

    if (!$email) {
        $errors[] = "Email tidak valid";
    }

    if (empty($password)) {
        $errors[] = "Password harus diisi";
    } else if (strlen($password) < 6) {
        $errors[] = "Password minimal 6 karakter";
    }

    if ($password !== $konfirmasi_password) {
        $errors[] = "Konfirmasi password tidak cocok";
    }

    // Cek apakah username atau email sudah ada
    if (checkUserExists($username, $email)) {
        $errors[] = "Username atau email sudah terdaftar";
    }

    // Jika tidak ada error, lakukan registrasi
    if (empty($errors)) {
        try {
            $registrasi = registerPasien([
                'nama_lengkap' => $nama_lengkap,
                'username' => $username,
                'email' => $email,
                'password' => $password
            ]);

            if ($registrasi) {
                $_SESSION['success_message'] = "Registrasi berhasil. Silakan login.";
                header("Location: login.php");
                exit();
            } else {
                throw new Exception("Proses registrasi gagal");
            }
        } catch (Exception $e) {
            $errors[] = "Registrasi gagal. Silakan coba lagi.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Sistem Klinik Psikologi</title>
    <link rel="shortcut icon" href="<?= $main_url ?>_dist/assets/compiled/svg/favicon.svg" type="image/x-icon">
    <link rel="stylesheet" href="<?= $main_url ?>_dist/assets/compiled/css/app.css">
    <link rel="stylesheet" href="<?= $main_url ?>_dist/assets/compiled/css/app-dark.css">
    <link rel="stylesheet" href="<?= $main_url ?>_dist/assets/compiled/css/auth.css">
    <style>
        #auth {
            background: #435ebe;
            min-height: 100vh;
        }

        .row {
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .col-lg-5 {
            width: 100%;
            max-width: 800px;
            padding: 2rem;
        }

        #auth-left {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 8px; /* Reduced border radius for subtlety */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1), 0 1px 3px rgba(0, 0, 0, 0.08); /* Enhanced shadow */
            padding: 2rem 4rem; /* Adjusted top padding */
            margin: 1rem auto;
        }
        
        .auth-logo {
            text-align: center;
            margin-bottom: 0.5rem; /* Reduced space after logo */
        }
        
        .auth-logo img {
            max-height: 80px;
            transition: transform 0.3s ease;
        }
        
        .auth-logo img:hover {
            transform: scale(1.05);
        }
        
        .auth-title {
            color: #2c3e8f;
            font-weight: 700;
            font-size: 2.2rem;
            margin-bottom: 0.25rem; /* Reduced space after title */
            text-align: center;
        }
        
        .auth-subtitle {
            color: #6c757d;
            font-size: 1rem;
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .form-control {
            padding: 0.8rem 1rem 0.8rem 3rem;
            border-radius: 8px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #435ebe;
            box-shadow: 0 0 0 0.2rem rgba(67, 94, 190, 0.25);
        }
        
        .form-control-icon {
            left: 1rem;
        }
        
        .btn-primary {
            background-color: #435ebe;
            border-color: #435ebe;
            padding: 0.8rem;
            font-size: 1.1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background-color: #2c3e8f;
            border-color: #2c3e8f;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(67, 94, 190, 0.3);
        }
        
        .alert {
            border-radius: 8px;
            margin-bottom: 1.5rem;
            padding: 1rem;
        }
        
        .alert-danger {
            background-color: #fff2f2;
            border-color: #ffcfcf;
            color: #dc3545;
        }
        
        .password-requirements {
            color: #6c757d;
            font-size: 0.8rem;
            margin-top: 0.25rem;
            padding-left: 1rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .text-center a {
            color: #435ebe;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        
        .text-center a:hover {
            color: #2c3e8f;
            text-decoration: underline;
        }
    </style>
</head>

<body>
<script src="<?= $main_url ?>_dist/assets/static/js/initTheme.js"></script>
    <div id="auth">
        <div class="row h-100 justify-content-center align-items-center">
            <div class="col-lg-5 col-12">
                <div id="auth-left">
                    <div class="auth-logo">
                        <a href="index.html">
                            <img src="<?= $main_url ?>_dist/gambar/logo-assyifa-2.jpeg" alt="Logo">
                        </a>
                    </div>
                    <h1 class="auth-title">Sign Up</h1>
                    <p class="auth-subtitle">Silakan lengkapi data untuk mendaftar sebagai pasien.</p>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error): ?>
                                <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST" id="registerForm">
                        <div class="form-group position-relative has-icon-left">
                            <input type="text" 
                                   class="form-control form-control-xl" 
                                   name="nama_lengkap" 
                                   placeholder="Nama Lengkap"
                                   value="<?php echo isset($nama_lengkap) ? htmlspecialchars($nama_lengkap) : ''; ?>"
                                   required>
                            <div class="form-control-icon">
                                <i class="bi bi-person"></i>
                            </div>
                        </div>
                        <div class="form-group position-relative has-icon-left">
                            <input type="text" 
                                   class="form-control form-control-xl" 
                                   name="username" 
                                   placeholder="Username"
                                   value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>"
                                   required>
                            <div class="form-control-icon">
                                <i class="bi bi-person"></i>
                            </div>
                        </div>
                        <div class="form-group position-relative has-icon-left">
                            <input type="email" 
                                   class="form-control form-control-xl" 
                                   name="email" 
                                   placeholder="Email"
                                   value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
                                   required>
                            <div class="form-control-icon">
                                <i class="bi bi-envelope"></i>
                            </div>
                        </div>
                        <div class="form-group position-relative has-icon-left">
                            <input type="password" 
                                   class="form-control form-control-xl" 
                                   name="password" 
                                   placeholder="Password"
                                   required>
                            <div class="form-control-icon">
                                <i class="bi bi-shield-lock"></i>
                            </div>
                            <div class="password-requirements">
                                Password minimal 6 karakter
                            </div>
                        </div>
                        <div class="form-group position-relative has-icon-left">
                            <input type="password" 
                                   class="form-control form-control-xl" 
                                   name="konfirmasi_password" 
                                   placeholder="Konfirmasi Password"
                                   required>
                            <div class="form-control-icon">
                                <i class="bi bi-shield-lock"></i>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block btn-lg shadow-lg mt-4">Daftar</button>
                    </form>
                    <div class="text-center mt-4">
                        <p class="text-gray-600">Sudah punya akun? 
                            <a href="login.php" class="font-bold">Login</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        const password = document.querySelector('input[name="password"]').value;
        const konfirmasi = document.querySelector('input[name="konfirmasi_password"]').value;
        
        if (password.length < 6) {
            e.preventDefault();
            alert('Password harus minimal 6 karakter!');
            return false;
        }
        
        if (password !== konfirmasi) {
            e.preventDefault();
            alert('Konfirmasi password tidak cocok!');
            return false;
        }
    });
    </script>
</body>
</html>