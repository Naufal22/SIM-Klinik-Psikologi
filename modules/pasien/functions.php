<?php
require_once '../../config/database.php';

function getAllPasien() {
    global $conn;
    $sql = "SELECT * FROM pasien ORDER BY created_at DESC";
    return $conn->query($sql);
}

function getPasienById($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM pasien WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function updatePasien($id, $data) {
    global $conn;
    
    try {
        $conn->begin_transaction();

        $stmt = $conn->prepare("UPDATE pasien SET 
            nama_lengkap = ?,
            tanggal_lahir = ?,
            jenis_kelamin = ?,
            alamat = ?,
            no_telepon = ?,
            email = ?,
            kontak_darurat_nama = ?,
            kontak_darurat_telepon = ?,
            status = ?,
            updated_at = CURRENT_TIMESTAMP
            WHERE id = ?");
            
        $stmt->bind_param("sssssssssi",
            $data['nama_lengkap'],
            $data['tanggal_lahir'],
            $data['jenis_kelamin'],
            $data['alamat'],
            $data['no_telepon'],
            $data['email'],
            $data['kontak_darurat_nama'],
            $data['kontak_darurat_telepon'],
            $data['status'],
            $id
        );
        
        $result = $stmt->execute();
        
        if ($result) {
            $conn->commit();
            return true;
        } else {
            throw new Exception($stmt->error);
        }
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

function deletePasien($id) {
    global $conn;
    
    try {
        $conn->begin_transaction();

        // Check if patient exists and is active
        $stmt = $conn->prepare("SELECT id FROM pasien WHERE id = ? AND status = 'Aktif'");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception('Data pasien tidak ditemukan atau sudah dinonaktifkan');
        }

        // Perform soft delete
        $stmt = $conn->prepare("UPDATE pasien SET status = 'Tidak Aktif', deleted_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $conn->commit();
            return true;
        } else {
            throw new Exception($stmt->error);
        }
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}


function getPasienByPsikolog($psikologId) {
    global $conn;
    $query = "SELECT DISTINCT p.* 
              FROM pasien p 
              INNER JOIN jadwal_konsultasi jk ON p.id = jk.pasien_id 
              WHERE jk.psikolog_id = ?
              ORDER BY p.created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $psikologId);
    $stmt->execute();
    return $stmt->get_result();
}
