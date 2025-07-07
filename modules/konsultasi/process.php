<?php
require_once '../../config/database.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

$action = $_POST['action'] ?? '';

// Set current user ID for logging
$current_user_id = $_SESSION['user_id'] ?? null;
if (!$current_user_id) {
    $_SESSION['error'] = "Sesi telah berakhir. Silakan login kembali.";
    header('Location: ../../auth/login.php');
    exit();
}

// Set global variable for triggers
mysqli_query($conn, "SET @current_user_id = " . $current_user_id);

switch ($action) {
    case 'add':
        $janji_temu_id = $_POST['janji_temu_id'];
        $diagnosa = $_POST['diagnosa'];
        $rekomendasi = $_POST['rekomendasi'];
        $catatan_privat = $_POST['catatan_privat'];

        // Start transaction
        mysqli_begin_transaction($conn);

        try {
            // Get psikolog_id from janji_temu
            $get_psikolog_query = "SELECT psikolog_id FROM janji_temu WHERE id = ?";
            $stmt = mysqli_prepare($conn, $get_psikolog_query);
            mysqli_stmt_bind_param($stmt, "i", $janji_temu_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $janji_data = mysqli_fetch_assoc($result);
            
            if (!$janji_data) {
                throw new Exception("Data janji temu tidak ditemukan");
            }

            $psikolog_id = $janji_data['psikolog_id'];

            // Insert consultation notes
            $query = "INSERT INTO catatan_konsultasi (janji_temu_id, psikolog_id, diagnosa, rekomendasi, catatan_privat) 
                     VALUES (?, ?, ?, ?, ?)";
            
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "iisss", $janji_temu_id, $psikolog_id, $diagnosa, $rekomendasi, $catatan_privat);
            mysqli_stmt_execute($stmt);

            // Update appointment status
            $query = "UPDATE janji_temu SET status = 'Selesai' WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "i", $janji_temu_id);
            mysqli_stmt_execute($stmt);

            // Log activity
            $activity_query = "INSERT INTO activity_log (user_id, aktivitas, detail) VALUES (?, 'Menambah catatan konsultasi', ?)";
            $detail = "Menambah catatan konsultasi untuk janji temu #" . $janji_temu_id;
            $stmt = mysqli_prepare($conn, $activity_query);
            mysqli_stmt_bind_param($stmt, "is", $current_user_id, $detail);
            mysqli_stmt_execute($stmt);

            mysqli_commit($conn);
            
            $_SESSION['success'] = "Catatan konsultasi berhasil disimpan.";
            header('Location: view.php?id=' . $janji_temu_id);
            exit();

        } catch (Exception $e) {
            mysqli_rollback($conn);
            $_SESSION['error'] = "Terjadi kesalahan saat menyimpan catatan konsultasi: " . $e->getMessage();
            header('Location: add.php?id=' . $janji_temu_id);
            exit();
        }
        break;

    case 'edit':
        $catatan_id = $_POST['catatan_id'];
        $diagnosa = $_POST['diagnosa'];
        $rekomendasi = $_POST['rekomendasi'];
        $catatan_privat = $_POST['catatan_privat'];

        try {
            $query = "UPDATE catatan_konsultasi 
                     SET diagnosa = ?, rekomendasi = ?, catatan_privat = ? 
                     WHERE id = ?";
            
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "sssi", $diagnosa, $rekomendasi, $catatan_privat, $catatan_id);
            mysqli_stmt_execute($stmt);

            // Log activity
            $activity_query = "INSERT INTO activity_log (user_id, aktivitas, detail) VALUES (?, 'Mengubah catatan konsultasi', ?)";
            $detail = "Mengubah catatan konsultasi #" . $catatan_id;
            $stmt = mysqli_prepare($conn, $activity_query);
            mysqli_stmt_bind_param($stmt, "is", $current_user_id, $detail);
            mysqli_stmt_execute($stmt);

            $_SESSION['success'] = "Catatan konsultasi berhasil diperbarui.";
            header('Location: view.php?id=' . $_POST['janji_temu_id']);
            exit();

        } catch (Exception $e) {
            $_SESSION['error'] = "Terjadi kesalahan saat memperbarui catatan konsultasi: " . $e->getMessage();
            header('Location: edit.php?id=' . $catatan_id);
            exit();
        }
        break;

    default:
        header('Location: index.php');
        exit();
}
?>