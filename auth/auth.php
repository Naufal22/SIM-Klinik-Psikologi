<?php
require_once __DIR__ . '/../config/database.php';

// Konstanta Role
define('ROLE_ADMIN', 'admin');
define('ROLE_PSIKOLOG', 'psikolog');
define('ROLE_PASIEN', 'pasien');
define('ROLE_MASTER', 'master');

function setUserSession($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    
    // Perbaikan logika pengambilan nama
    if ($user['role'] === 'psikolog') {
        $nama = $user['psikolog_nama'] ?? $user['username'];
    } elseif ($user['role'] === 'pasien') {
        $nama = $user['pasien_nama'] ?? $user['username'];
    } elseif ($user['role'] === 'admin') {
        $nama = 'Administrator';
    } elseif ($user['role'] === 'master') {
        $nama = 'Master Admin';
    } else {
        $nama = $user['username'];
    }
    
    $_SESSION['nama'] = $nama;
    $_SESSION['last_activity'] = time();
}

function authenticateUser($conn, $username, $password) {
    try {
        // Modifikasi query untuk mengambil nama dengan alias yang benar
        $stmt = $conn->prepare("
            SELECT u.*, 
                   p.nama as psikolog_nama,
                   pas.nama_lengkap as pasien_nama
            FROM users u
            LEFT JOIN psikolog p ON u.reference_id = p.id AND u.role = 'psikolog'
            LEFT JOIN pasien pas ON u.reference_id = pas.id AND u.role = 'pasien'
            WHERE u.username = ? OR u.email = ?
        ");
        
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password_hash'])) {
                // Hapus penugasan nama yang redundan
                setUserSession($user);
                updateLastLogin($conn, $user['id']);
                return $user;
            }
        }
        return false;
    } catch (Exception $e) {
        error_log("Error autentikasi: " . $e->getMessage());
        return false;
    }
}

function getCurrentUser($conn) {
    if (!isLoggedIn()) return null;
    
    try {
        $stmt = $conn->prepare("
            SELECT u.*, 
                   p.nama as psikolog_nama,
                   pas.nama_lengkap as pasien_nama,
                    p.foto as psikolog_foto
            FROM users u
            LEFT JOIN psikolog p ON u.reference_id = p.id AND u.role = 'psikolog'
            LEFT JOIN pasien pas ON u.reference_id = pas.id AND u.role = 'pasien'
            WHERE u.id = ?
        ");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        
        // Update session dengan nama terbaru
        if ($user) {
            if ($user['role'] === 'psikolog') {
                $_SESSION['nama'] = $user['psikolog_nama'] ?? $user['username'];
                $_SESSION['foto'] = $user['psikolog_foto'] ?? 'default.jpg'; // Tambahkan ini
            } elseif ($user['role'] === 'pasien') {
                $_SESSION['nama'] = $user['pasien_nama'] ?? $user['username'];
            }
        }
        
        return $user;
    } catch (Exception $e) {
        error_log("Error mengambil data user: " . $e->getMessage());
        return null;
    }
}

function checkSessionTimeout() {
    $timeout = 30 * 60;
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
        logout();
        $_SESSION['error_message'] = "Sesi Anda telah berakhir. Silakan login kembali.";
        return false;
    }
    $_SESSION['last_activity'] = time();
    return true;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function logout() {
    global $main_url;
    session_unset();
    session_destroy();
    session_start();
    $_SESSION['success_message'] = "Anda telah berhasil logout.";
    header("Location: " . $main_url . "auth/login.php");
    exit();
}

function getDashboardUrl($role) {
    global $main_url;
    $dashboards = [
        ROLE_ADMIN => "modules/dashboard/admin/index.php",
        ROLE_PSIKOLOG => "modules/dashboard/psikolog/index.php",
        ROLE_PASIEN => "modules/dashboard/pasien/index.php",
        ROLE_MASTER => "modules/master-data/staff.php"
    ];
    return $main_url . ($dashboards[$role] ?? "dashboard/index.php");
}

function requireRole($allowedRoles) {
    global $main_url;
    if (!is_array($allowedRoles)) {
        $allowedRoles = [$allowedRoles];
    }
    
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowedRoles)) {
        $_SESSION['error_message'] = "Anda tidak memiliki akses ke halaman ini.";
        header("Location: " . $main_url . "auth/unauthorized.php");
        exit();
    }
}

function getCurrentUserRole() {
    return $_SESSION['role'] ?? null;
}

function isAdmin() {
    return getCurrentUserRole() === ROLE_ADMIN;
}

function isPsikolog() {
    return getCurrentUserRole() === ROLE_PSIKOLOG;
}

function isPasien() {
    return getCurrentUserRole() === ROLE_PASIEN;
}

function updateLastLogin($conn, $userId) {
    try {
        $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Error update last login: " . $e->getMessage());
        return false;
    }
}

function checkUserExists($conn, $username, $email) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['count'] > 0;
}

function validateLoginInput($username, $password) {
    if (empty($username) || empty($password)) {
        return "Username dan password harus diisi!";
    }
    return "";
}


function redirectBasedOnRole($role) {
    global $main_url;
    switch ($role) {
        case ROLE_ADMIN:
            $redirect_url = $main_url . "modules/dashboard/admin/index.php";
            break;
        case ROLE_PSIKOLOG:
            $redirect_url = $main_url . "modules/dashboard/psikolog/index.php";
            break;
        case ROLE_PASIEN:
            $redirect_url = $main_url . "modules/dashboard/pasien/index.php";
            break;
        case ROLE_MASTER:
            $redirect_url = $main_url . "modules/master-data/staff.php";
            break;
        default:
            $redirect_url = $main_url . "dashboard/index.php";
    }
    header("Location: " . $redirect_url);
    exit();
}