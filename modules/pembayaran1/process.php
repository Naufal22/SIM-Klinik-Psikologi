<?php
session_start();
require_once '../../config/database.php';
require_once '../../auth/auth.php';
require_once 'functions.php';

requireRole(ROLE_ADMIN);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            try {
                // Ambil data dari form
                $janji_temu_id = $_POST['janji_temu_id'];
                $jumlah = $_POST['jumlah'];
                $metode_pembayaran = $_POST['metode_pembayaran'];
                $status = $_POST['status'];
                $catatan = $_POST['catatan'] ?? '';
                $bukti_pembayaran = null;
                
                // Handle file upload jika ada
                if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['size'] > 0) {
                    // Validasi file
                    $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
                    $maxSize = 2 * 1024 * 1024; // 2MB
                    
                    if ($_FILES['bukti_pembayaran']['size'] > $maxSize) {
                        throw new Exception("Ukuran file terlalu besar. Maksimal 2MB.");
                    }
                    
                    if (!in_array($_FILES['bukti_pembayaran']['type'], $allowedTypes)) {
                        throw new Exception("Format file tidak didukung. Gunakan JPG, PNG, atau PDF.");
                    }
                    
                    // Upload file
                    $uploadDir = '../../uploads/bukti_pembayaran/';
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    $fileName = uniqid() . '_' . basename($_FILES['bukti_pembayaran']['name']);
                    $targetPath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($_FILES['bukti_pembayaran']['tmp_name'], $targetPath)) {
                        $bukti_pembayaran = $fileName;
                    } else {
                        throw new Exception("Gagal mengupload file.");
                    }
                }
                
                // Generate nomor invoice
                $nomor_invoice = generateInvoiceNumber();
                
                // Insert pembayaran
                $query = "INSERT INTO pembayaran (janji_temu_id, nomor_invoice, jumlah, metode_pembayaran, status, bukti_pembayaran, catatan) 
                         VALUES (?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = mysqli_prepare($conn, $query);
                if (!$stmt) {
                    throw new Exception("Error preparing statement: " . mysqli_error($conn));
                }
                
                // Bind parameter dengan tipe data yang sesuai
                mysqli_stmt_bind_param(
                    $stmt, 
                    "isdssss",  // i = integer, s = string, d = double
                    $janji_temu_id,      // integer (i)
                    $nomor_invoice,      // string (s)
                    $jumlah,             // double (d)
                    $metode_pembayaran,  // string (s)
                    $status,             // string (s)
                    $bukti_pembayaran,   // string (s)
                    $catatan             // string (s)
                );
                
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Gagal menambahkan pembayaran: " . mysqli_stmt_error($stmt));
                }
                
                // Update status janji temu jika pembayaran lunas
                if ($status == 'Lunas') {
                    $updateQuery = "UPDATE janji_temu SET status = 'Selesai' WHERE id = ?";
                    $updateStmt = mysqli_prepare($conn, $updateQuery);
                    mysqli_stmt_bind_param($updateStmt, "i", $janji_temu_id);
                    mysqli_stmt_execute($updateStmt);
                    mysqli_stmt_close($updateStmt);
                }
                
                mysqli_stmt_close($stmt);
                $_SESSION['success'] = "Pembayaran berhasil ditambahkan.";
                header('Location: index.php');
                exit;
                
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                header('Location: create.php');
                exit;
            }
            break;
            
        // case 'update':
        //     try {
        //         $id = $_POST['id'];
        //         $status = $_POST['status'];
                
        //         // Update status pembayaran
        //         $query = "UPDATE pembayaran SET status = ? WHERE id = ?";
        //         $stmt = mysqli_prepare($conn, $query);
        //         if (!$stmt) {
        //             throw new Exception("Error preparing statement: " . mysqli_error($conn));
        //         }
                
        //         mysqli_stmt_bind_param($stmt, "si", $status, $id);
                
        //         if (!mysqli_stmt_execute($stmt)) {
        //             throw new Exception("Gagal memperbarui status pembayaran: " . mysqli_stmt_error($stmt));
        //         }
                
        //         if ($status == 'Lunas') {
        //             // Get janji_temu_id
        //             $getQuery = "SELECT janji_temu_id FROM pembayaran WHERE id = ?";
        //             $getStmt = mysqli_prepare($conn, $getQuery);
        //             mysqli_stmt_bind_param($getStmt, "i", $id);
        //             mysqli_stmt_execute($getStmt);
        //             $result = mysqli_stmt_get_result($getStmt);
        //             $row = mysqli_fetch_assoc($result);
        //             mysqli_stmt_close($getStmt);
                    
        //             // Update status janji temu
        //             if ($row) {
        //                 $updateQuery = "UPDATE janji_temu SET status = 'Selesai' WHERE id = ?";
        //                 $updateStmt = mysqli_prepare($conn, $updateQuery);
        //                 mysqli_stmt_bind_param($updateStmt, "i", $row['janji_temu_id']);
        //                 mysqli_stmt_execute($updateStmt);
        //                 mysqli_stmt_close($updateStmt);
        //             }
        //         }
                
        //         mysqli_stmt_close($stmt);
        //         $_SESSION['success'] = "Status pembayaran berhasil diperbarui.";
        //         header('Location: view.php?id=' . $id);
        //         exit;
                
        //     } catch (Exception $e) {
        //         $_SESSION['error'] = $e->getMessage();
        //         header('Location: view.php?id=' . $id);
        //         exit;
        //     }
        //     break;
        case 'update':
            try {
                $id = $_POST['id'];
                $status = $_POST['status'];
                
                // Prepare to update status and potentially the payment proof
                $query = "UPDATE pembayaran SET status = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                if (!$stmt) {
                    throw new Exception("Error preparing statement: " . mysqli_error($conn));
                }
                
                mysqli_stmt_bind_param($stmt, "si", $status, $id);
                
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Gagal memperbarui status pembayaran: " . mysqli_stmt_error($stmt));
                }
                
                // Handle file upload if a new file is provided
                if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['size'] > 0) {
                    // Validate file
                    $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
                    $maxSize = 2 * 1024 * 1024; // 2MB
                    
                    if ($_FILES['bukti_pembayaran']['size'] > $maxSize) {
                        throw new Exception("Ukuran file terlalu besar. Maksimal 2MB.");
                    }
                    
                    if (!in_array($_FILES['bukti_pembayaran']['type'], $allowedTypes)) {
                        throw new Exception("Format file tidak didukung. Gunakan JPG, PNG, atau PDF.");
                    }
                    
                    // Get the existing bukti_pembayaran to delete it later
                    $getQuery = "SELECT bukti_pembayaran FROM pembayaran WHERE id = ?";
                    $getStmt = mysqli_prepare($conn, $getQuery);
                    mysqli_stmt_bind_param($getStmt, "i", $id);
                    mysqli_stmt_execute($getStmt);
                    $result = mysqli_stmt_get_result($getStmt);
                    $row = mysqli_fetch_assoc($result);
                    mysqli_stmt_close($getStmt);
                    
                    // Define the upload directory
                    $uploadDir = '../../uploads/bukti_pembayaran/';
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    // Generate a new file name
                    $fileName = uniqid() . '_' . basename($_FILES['bukti_pembayaran']['name']);
                    $targetPath = $uploadDir . $fileName;
                    
                    // Move the uploaded file
                    if (move_uploaded_file($_FILES['bukti_pembayaran']['tmp_name'], $targetPath)) {
                        // If the old file exists, delete it
                        if ($row && $row['bukti_pembayaran']) {
                            $oldFilePath = $uploadDir . $row['bukti_pembayaran'];
                            if (file_exists($oldFilePath)) {
                                unlink($oldFilePath);
                            }
                        }
                        
                        // Update the payment record with the new bukti_pembayaran
                        $updateQuery = "UPDATE pembayaran SET bukti_pembayaran = ? WHERE id = ?";
                        $updateStmt = mysqli_prepare($conn, $updateQuery);
                        mysqli_stmt_bind_param($updateStmt, "si", $fileName, $id);
                        mysqli_stmt_execute($updateStmt);
                        mysqli_stmt_close($updateStmt);
                    } else {
                        throw new Exception("Gagal mengupload file.");
                    }
                }
                
                // If the status is 'Lunas', update the related appointment
                if ($status == 'Lunas') {
                    // Get janji_temu_id
                    $getQuery = "SELECT janji_temu_id FROM pembayaran WHERE id = ?";
                    $getStmt = mysqli_prepare($conn, $getQuery);
                    mysqli_stmt_bind_param($getStmt, "i", $id);
                    mysqli_stmt_execute($getStmt);
                    $result = mysqli_stmt_get_result($getStmt);
                    $row = mysqli_fetch_assoc($result);
                    mysqli_stmt_close($getStmt);
                    
                    // Update status janji temu
                    if ($row) {
                        $updateQuery = "UPDATE janji_temu SET status = 'Selesai' WHERE id = ?";
                        $updateStmt = mysqli_prepare($conn, $updateQuery);
                        mysqli_stmt_bind_param($updateStmt, "i", $row['janji_temu_id']);
                        mysqli_stmt_execute($updateStmt);
                        mysqli_stmt_close($updateStmt);
                    }
                }
                
                mysqli_stmt_close($stmt);
                $_SESSION['success'] = "Status pembayaran berhasil diperbarui.";
                header('Location: view.php?id=' . $id);
                exit;
                
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                header('Location: view.php?id=' . $id);
                exit;
            }
            break;
    }
}


// Handle GET requests (e.g., delete)
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $action = $_GET['action'] ?? '';
    $id = $_GET['id'] ?? 0;
    
    switch ($action) {
        case 'delete':
            try {
                // Get file name before deleting record
                $query = "SELECT bukti_pembayaran FROM pembayaran WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "i", $id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $row = mysqli_fetch_assoc($result);
                mysqli_stmt_close($stmt);
                
                // Delete file if exists
                if ($row && $row['bukti_pembayaran']) {
                    $filePath = '../../uploads/bukti_pembayaran/' . $row['bukti_pembayaran'];
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
                
                // Delete record
                $query = "DELETE FROM pembayaran WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "i", $id);
                
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Gagal menghapus pembayaran: " . mysqli_stmt_error($stmt));
                }
                
                mysqli_stmt_close($stmt);
                $_SESSION['success'] = "Pembayaran berhasil dihapus.";
                header('Location: index.php');
                exit;
                
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                header('Location: index.php');
                exit;
            }
            break;
    }
}
?>