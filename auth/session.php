<?php
require_once __DIR__ . '/../config/database.php';

// Fungsi untuk mengecek login
function checkLogin() {
    global $main_url;
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . $main_url . "auth/login.php");
        exit();
    }
}

// Fungsi untuk mendapatkan ID user yang sedang login
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Fungsi untuk mendapatkan role user yang sedang login
function getCurrentUserRole() {
    return $_SESSION['role'] ?? null;
}

// Fungsi untuk mengecek apakah user adalah admin
function isAdmin() {
    return getCurrentUserRole() === 'admin';
}

// Fungsi untuk mengecek apakah user adalah psikolog
function isPsikolog() {
    return getCurrentUserRole() === 'psikolog';
}

// Fungsi untuk mengecek apakah user adalah pasien
function isPasien() {
    return getCurrentUserRole() === 'pasien';
}

// Fungsi untuk mengecek apakah user memiliki role yang diizinkan
function checkRole($allowed_roles) {
    global $main_url;
    if (!is_array($allowed_roles)) {
        $allowed_roles = [$allowed_roles];
    }
    
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
        $_SESSION['error_message'] = "Anda tidak memiliki akses ke halaman ini.";
        header("Location: " . $main_url . "auth/unauthorized.php");
        exit();
    }
}

// Fungsi untuk mendapatkan data user yang sedang login
function getCurrentUser() {
    global $conn;
    
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        return $result->fetch_assoc();
    }
    
    return null;
}

// Fungsi untuk mendapatkan data lengkap user (termasuk data psikolog/pasien)
function getUserData() {
    global $conn;
    
    $user = getCurrentUser();
    if (!$user) {
        return null;
    }
    
    $additionalData = [];
    
    if ($user['role'] === 'psikolog') {
        $stmt = $conn->prepare("SELECT * FROM psikolog WHERE id = ?");
        $stmt->bind_param("i", $user['reference_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $additionalData = $result->fetch_assoc();
        }
    } elseif ($user['role'] === 'pasien') {
        $stmt = $conn->prepare("SELECT * FROM pasien WHERE id = ?");
        $stmt->bind_param("i", $user['reference_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $additionalData = $result->fetch_assoc();
        }
    }
    
    return array_merge($user, $additionalData);
}
