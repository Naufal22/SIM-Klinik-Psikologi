<?php
require_once __DIR__ . '/format_helper.php';

function generateInvoiceNumber() {
    global $conn;
    
    $query = "SELECT MAX(CAST(SUBSTRING(nomor_invoice, 5) AS UNSIGNED)) as last_number FROM pembayaran";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    
    $nextNumber = ($row['last_number'] ?? 0) + 1;
    return 'INV-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
}

function getPaymentStatus($status) {
    switch ($status) {
        case 'Lunas':
            return '<span class="badge bg-success">Lunas</span>';
        case 'Pending':
            return '<span class="badge bg-warning">Pending</span>';
        case 'Dibatalkan':
            return '<span class="badge bg-danger">Dibatalkan</span>';
        default:
            return '<span class="badge bg-secondary">Unknown</span>';
    }
}

function createPaymentNotification($user_id, $payment_id, $status) {
    global $conn;
    
    $title = '';
    $message = '';
    
    switch ($status) {
        case 'Lunas':
            $title = 'Pembayaran Berhasil';
            $message = 'Pembayaran Anda telah dikonfirmasi.';
            break;
        case 'Pending':
            $title = 'Menunggu Pembayaran';
            $message = 'Silakan selesaikan pembayaran Anda.';
            break;
        case 'Dibatalkan':
            $title = 'Pembayaran Dibatalkan';
            $message = 'Pembayaran Anda telah dibatalkan.';
            break;
    }
    
    $query = "INSERT INTO notifikasi (user_id, judul, pesan, tipe, status) 
              VALUES (?, ?, ?, 'pembayaran', 'belum_dibaca')";
              
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "iss", $user_id, $title, $message);
    mysqli_stmt_execute($stmt);
}

function validatePayment($data) {
    $errors = [];
    
    if (empty($data['janji_temu_id'])) {
        $errors[] = 'Janji temu harus dipilih';
    }
    
    if (empty($data['jumlah']) || $data['jumlah'] <= 0) {
        $errors[] = 'Jumlah pembayaran tidak valid';
    }
    
    if (empty($data['metode_pembayaran'])) {
        $errors[] = 'Metode pembayaran harus dipilih';
    }
    
    return $errors;
}

function getPaymentReport($startDate, $endDate) {
    global $conn;
    
    $query = "SELECT 
                DATE(p.tanggal_pembayaran) as tanggal,
                p.metode_pembayaran,
                COUNT(*) as jumlah_transaksi,
                SUM(p.jumlah) as total_pembayaran
              FROM pembayaran p
              WHERE p.status = 'Lunas'
                AND DATE(p.tanggal_pembayaran) BETWEEN ? AND ?
              GROUP BY DATE(p.tanggal_pembayaran), p.metode_pembayaran
              ORDER BY tanggal DESC";
              
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ss", $startDate, $endDate);
    mysqli_stmt_execute($stmt);
    
    return mysqli_stmt_get_result($stmt);
}
