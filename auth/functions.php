<?php
/**
 * Authentication related functions
 */

// Pastikan koneksi database tersedia
require_once __DIR__ . '/../config/database.php';

// Role constants
define('ROLE_ADMIN', 'admin');
define('ROLE_PSIKOLOG', 'psikolog');
define('ROLE_PASIEN', 'pasien');
define('ROLE_MASTER', 'master');

function getDashboardUrl($role) {
    global $main_url;
    switch ($role) {
        case ROLE_ADMIN:
            return $main_url . "modules/dashboard/admin/index.php";
        case ROLE_PSIKOLOG:
            return $main_url . "modules/dashboard/psikolog/index.php";
        case ROLE_PASIEN:
            return $main_url . "modules/dashboard/pasien/index.php";
        case ROLE_MASTER:
            return $main_url . "modules/master-data/staff.php";
    }
}
/**
 * Check if user has required role
 */
function checkRole($requiredRole) {
    if (!isset($_SESSION['role'])) {
        return false;
    }
    return $_SESSION['role'] === $requiredRole;
}

/**
 * Middleware to protect routes based on role
 */
function requireRole($allowedRoles) {
    global $main_url;
    if (!isLoggedIn()) {
        $_SESSION['error_message'] = "Silakan login terlebih dahulu.";
        header("Location: " . $main_url . "auth/login.php");
        exit();
    }

    if (!is_array($allowedRoles)) {
        $allowedRoles = [$allowedRoles];
    }

    if (!in_array($_SESSION['role'], $allowedRoles)) {
        $_SESSION['error_message'] = "Anda tidak memiliki akses ke halaman ini.";
        // Redirect to appropriate dashboard based on user's role
        header("Location: " . getDashboardUrl($_SESSION['role']));
        exit();
    }
}

function authenticateUser($conn, $username, $password) {
    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password_hash'])) {
                return $user;
            }
        }
        return false;
    } catch (Exception $e) {
        error_log("Authentication error: " . $e->getMessage());
        return false;
    }
}

function updateLastLogin($conn, $userId) {
    try {
        $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Update last login error: " . $e->getMessage());
        return false;
    }
}

function logActivity($conn, $userId, $activity, $detail, $ip, $userAgent) {
    try {
        $stmt = $conn->prepare("INSERT INTO activity_log (user_id, aktivitas, detail, ip_address, user_agent) 
                               VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $userId, $activity, $detail, $ip, $userAgent);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Activity log error: " . $e->getMessage());
        return false;
    }
}

function redirectBasedOnRole($role) {
    switch($role) {
        case 'admin':
            header("Location: ../modules/dashboard/admin/index.php");
            break;
        case 'psikolog':
            header("Location: ../modules/dashboard/psikolog/index.php");
            break;
        case 'pasien':
            header("Location: ../modules/dashboard/index.php");
            break;
        case 'master':
            header("Location: ../modules/master-data/staff.php");
            break;
        default:
            header("Location: ../dashboard/index.php");
    }
    exit();
}

function setUserSession($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['nama'] = $user['nama'];
    $_SESSION['last_activity'] = time();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function validateLoginInput($username, $password) {
    if (empty($username) || empty($password)) {
        return "Username dan password harus diisi!";
    }
    return "";
}


function registerPasien($data) {
    global $conn;
    
    try {
        // Log awal proses
        error_log("Mulai proses registrasi untuk user: " . $data['username']);
        
        // Mulai transaksi
        $conn->begin_transaction();
        
        // Insert ke tabel users
        $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, 'pasien')");
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt->bind_param("sss", 
            $data['username'], 
            $data['email'], 
            $hashedPassword
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Gagal insert ke tabel users: " . $stmt->error);
        }
        
        // Dapatkan ID user yang baru dibuat
        $userId = $conn->insert_id;
        
        // Insert ke tabel pasien
        $stmt = $conn->prepare("INSERT INTO pasien (nama_lengkap, email) VALUES (?, ?)");
        $stmt->bind_param("ss",
            $data['nama_lengkap'], 
            $data['email']
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Gagal insert ke tabel pasien: " . $stmt->error);
        }
        
        // Dapatkan ID pasien yang baru dibuat
        $pasienId = $conn->insert_id;
        
        // Update reference_id di tabel users
        $stmt = $conn->prepare("UPDATE users SET reference_id = ? WHERE id = ?");
        $stmt->bind_param("ii", $pasienId, $userId);
        
        if (!$stmt->execute()) {
            throw new Exception("Gagal update reference_id: " . $stmt->error);
        }
        
        // Commit transaksi
        $conn->commit();
        
        error_log("Registrasi berhasil untuk user: " . $data['username']);
        return true;
        
    } catch (Exception $e) {
        // Rollback transaksi jika gagal
        $conn->rollback();
        error_log("Detail error registrasi: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        return false;
    }
}



function checkUserExists($username, $email) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['count'] > 0;
    } catch (Exception $e) {
        error_log("Check user exists error: " . $e->getMessage());
        return false;
    }
}

/**
 * Check session timeout
 */
function checkSessionTimeout() {
    $timeout = 30 * 60; // 30 minutes
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
        logout();
        $_SESSION['error_message'] = "Sesi Anda telah berakhir. Silakan login kembali.";
        header("Location: /auth/login.php");
        exit();
    }
    $_SESSION['last_activity'] = time();
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