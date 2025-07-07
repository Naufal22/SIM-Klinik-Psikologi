<?php
/**
 * File: test_koneksi.php
 * Description: File untuk mengecek koneksi database MySQLi
 */

// Include file database.php (karena dalam folder yang sama, gunakan ./)
require_once './database.php';

echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; }
    .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; }
    .info { background: #f5f5f5; padding: 10px; margin: 10px 0; border-radius: 5px; }
</style>";

echo "<h2>Test Koneksi Database</h2>";

// Test 1: Cek Koneksi Dasar
echo "<div class='info'><strong>Test 1:</strong> Cek Koneksi Database<br>";
if($conn) {
    echo "<div class='success'>✅ Koneksi berhasil!</div>";
} else {
    echo "<div class='error'>❌ Koneksi gagal!</div>";
}
echo "</div>";

// Test 2: Cek Ping
echo "<div class='info'><strong>Test 2:</strong> Cek Ping Database<br>";
if($conn->ping()) {
    echo "<div class='success'>✅ Database merespon dengan baik!</div>";
} else {
    echo "<div class='error'>❌ Database tidak merespon!</div>";
}
echo "</div>";

// Test 3: Cek Info Server
echo "<div class='info'><strong>Test 3:</strong> Informasi Server<br>";
echo "Server Info: " . $conn->server_info . "<br>";
echo "Server Version: " . $conn->server_version . "<br>";
echo "Host Info: " . $conn->host_info . "<br>";
echo "</div>";

// Test 4: Cek Database Terpilih
echo "<div class='info'><strong>Test 4:</strong> Database Terpilih<br>";
if($result = $conn->query("SELECT DATABASE()")) {
    $row = $result->fetch_row();
    echo "<div class='success'>✅ Database aktif: " . $row[0] . "</div>";
    $result->close();
} else {
    echo "<div class='error'>❌ Tidak dapat mengecek database aktif!</div>";
}
echo "</div>";

// Test 5: Coba Query Sederhana
echo "<div class='info'><strong>Test 5:</strong> Test Query Sederhana<br>";
if($result = $conn->query("SELECT 1")) {
    echo "<div class='success'>✅ Query test berhasil!</div>";
    $result->close();
} else {
    echo "<div class='error'>❌ Query test gagal!</div>";
}
echo "</div>";

// Tampilkan panduan troubleshooting jika ada error
if($conn->error) {
    echo "<div class='error'>
    <strong>Error terdeteksi!</strong><br>
    " . $conn->error . "
    <br><br>
    <strong>Troubleshooting:</strong><br>
    1. Pastikan XAMPP/MySQL sudah running<br>
    2. Cek kredensial di config/database.php:<br>
       - Host: " . DB_HOST . "<br>
       - User: " . DB_USER . "<br>
       - Database: " . DB_NAME . "<br>
    3. Pastikan database '" . DB_NAME . "' sudah dibuat di phpMyAdmin<br>
    4. Cek error log PHP untuk detail lebih lanjut
    </div>";
}

// Tutup koneksi
closeConnection();