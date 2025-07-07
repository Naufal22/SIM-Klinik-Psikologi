<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $response = [];
    
    switch($action) {
        case 'add':
            // Validasi field yang diperlukan
            $required_fields = ['nama_lengkap', 'tanggal_lahir', 'jenis_kelamin', 'alamat', 'no_telepon', 'kontak_darurat_nama', 'kontak_darurat_telepon'];
            $missing_fields = [];
            
            foreach($required_fields as $field) {
                if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                    $missing_fields[] = $field;
                }
            }
            
            if (!empty($missing_fields)) {
                $response = [
                    'status' => 'error',
                    'message' => 'Mohon lengkapi semua field yang wajib diisi'
                ];
                break;
            }

            // Generate nomor rekam medis
            $year = date('Y');
            $query = "SELECT MAX(CAST(SUBSTRING(nomor_rekam_medis, 8) AS UNSIGNED)) as last_number 
                     FROM pasien 
                     WHERE nomor_rekam_medis LIKE 'RM-{$year}-%'";
            $result = $conn->query($query);
            $row = $result->fetch_assoc();
            $last_number = $row['last_number'] ?? 0;
            $new_number = str_pad($last_number + 1, 4, '0', STR_PAD_LEFT);
            $nomor_rm = "RM-{$year}-{$new_number}";

            // Mulai transaksi
            $conn->begin_transaction();

            try {
                // Insert ke tabel pasien
                $stmt = $conn->prepare("INSERT INTO pasien (
                    nomor_rekam_medis, nama_lengkap, tanggal_lahir, jenis_kelamin, 
                    alamat, no_telepon, email, kontak_darurat_nama, kontak_darurat_telepon
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

                $stmt->bind_param("sssssssss",
                    $nomor_rm,
                    $_POST['nama_lengkap'],
                    $_POST['tanggal_lahir'],
                    $_POST['jenis_kelamin'],
                    $_POST['alamat'],
                    $_POST['no_telepon'],
                    $_POST['email'],
                    $_POST['kontak_darurat_nama'],
                    $_POST['kontak_darurat_telepon']
                );

                $stmt->execute();
                $pasien_id = $conn->insert_id;

                // Jika yang login adalah pasien, update reference_id di tabel users
                if (isset($_SESSION['role']) && $_SESSION['role'] == 'pasien') {
                    $user_id = $_SESSION['user_id'];
                    $update_user = $conn->prepare("UPDATE users SET reference_id = ? WHERE id = ?");
                    $update_user->bind_param("ii", $pasien_id, $user_id);
                    $update_user->execute();
                    $update_user->close();
                }

                $conn->commit();
                
                // Set response berdasarkan role
                if (isset($_SESSION['role']) && $_SESSION['role'] == 'pasien') {
                    $response = [
                        'status' => 'success',
                        'message' => 'Data berhasil disimpan',
                        'redirect' => $main_url . 'modules/dashboard/pasien/index.php'
                    ];
                } else {
                    $response = [
                        'status' => 'success',
                        'message' => 'Data berhasil disimpan'
                    ];
                }

            } catch (Exception $e) {
                $conn->rollback();
                $response = [
                    'status' => 'error',
                    'message' => 'Gagal menyimpan data: ' . $e->getMessage()
                ];
            }

            $stmt->close();
            break;


        case 'delete':
            if (!isset($_POST['id'])) {
                $response = [
                    'status' => 'error',
                    'message' => 'ID pasien tidak ditemukan'
                ];
                break;
            }

            $id = (int)$_POST['id'];
            $stmt = $conn->prepare("DELETE FROM pasien WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $response = [
                    'status' => 'success',
                    'message' => 'Data pasien berhasil dihapus'
                ];
            } else {
                $response = [
                    'status' => 'error',
                    'message' => 'Gagal menghapus data pasien: ' . $conn->error
                ];
            }
            
            $stmt->close();
            break;

            case 'edit':
                // Validate required fields
                $required_fields = ['alamat', 'no_telepon', 'kontak_darurat_nama', 'kontak_darurat_telepon'];
                $missing_fields = [];
                
                foreach($required_fields as $field) {
                    if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                        $missing_fields[] = $field;
                    }
                }
                
                if (!empty($missing_fields)) {
                    $response = [
                        'status' => 'error',
                        'message' => 'Mohon lengkapi semua field yang wajib diisi'
                    ];
                    break;
                }
            
                if (!isset($_POST['id'])) {
                    $response = [
                        'status' => 'error',
                        'message' => 'ID pasien tidak valid'
                    ];
                    break;
                }
            
                // Use the updatePasien function from functions.php
                require_once 'functions.php';
                $id = (int)$_POST['id'];
                $update_result = updatePasien($id, $_POST);
            
                if ($update_result) {
                    $response = [
                        'status' => 'success',
                        'message' => 'Data pasien berhasil diupdate'
                    ];
                } else {
                    $response = [
                        'status' => 'error',
                        'message' => 'Gagal mengupdate data pasien'
                    ];
                }
                break;
        
        default:
            $response = [
                'status' => 'error',
                'message' => 'Invalid action'
            ];
            break;
    }
    
    // Send JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}