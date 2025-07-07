<?php
/**
 * File: config/database.php
 * Description: Database configuration supporting both MySQLi and PDO
 * Last Modified: 2024-11-08
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'psikologi-klinis');

// Create MySQLi connection (for legacy support)
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check MySQLi connection
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Set charset for MySQLi
$conn->set_charset("utf8mb4");

// PDO connection function (for new features)
function getConnection() {
    try {
        static $pdo = null;
        if ($pdo === null) {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        }
        return $pdo;
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Function untuk test koneksi
function testConnection() {
    global $conn;
    if ($conn->ping()) {
        echo "Koneksi database OK ✅";
        return true;
    } else {
        echo "Koneksi database ERROR ❌";
        return false;
    }
}

// Function untuk membersihkan input
function cleanInput($data) {
    global $conn;
    return $conn->real_escape_string($data);
}

// Function untuk menutup koneksi
function closeConnection() {
    global $conn;
    if($conn) {
        $conn->close();
    }
}

// Function untuk menjalankan query dengan prepared statement
function query($sql, $params = []) {
    global $conn;
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    return $result;
}

// Function untuk mendapatkan error terakhir
function getLastError() {
    global $conn;
    return $conn->error;
}

// Function untuk mendapatkan ID terakhir yang di-insert
function getLastInsertId() {
    global $conn;
    return $conn->insert_id;
}

// Function untuk menghitung jumlah rows
function getNumRows($result) {
    return $result->num_rows;
}

// Helper functions for formatting

function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

function generateCode($prefix, $number) {
    return $prefix . '-' . str_pad($number, 6, '0', STR_PAD_LEFT);
}

// Base URL configuration
$main_url = 'http://localhost/psikologi-app/';