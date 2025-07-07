<?php
class PatientAppointmentService {
    private $conn;
    private $timeSlotService;
    private $notificationService;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->timeSlotService = new TimeSlotService($conn);
        $this->notificationService = new NotificationService($conn);
    }

    public function createAppointment($data, $userId) {
        try {
            // Debug data
            error_log("Creating appointment with data: " . print_r($data, true));
            error_log("User ID: " . $userId);
            
            // Dapatkan pasien_id dari tabel users dan pasien
            $stmt = $this->conn->prepare("
                SELECT p.id as pasien_id 
                FROM pasien p 
                INNER JOIN users u ON p.id = u.reference_id 
                WHERE u.id = ? AND u.role = 'pasien'
            ");
            
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception("Anda belum terdaftar sebagai pasien. Silakan lengkapi data pasien terlebih dahulu.");
            }
            
            $row = $result->fetch_assoc();
            $pasienId = $row['pasien_id'];
            
            error_log("Pasien ID: " . $pasienId);

            // Validasi data
            $this->validateRequiredFields($data);
            $layanan = $this->getServiceDetails($data['layanan_id']);
            
            // Hitung waktu selesai
            $jam_mulai = $data['jam_mulai'];
            $jam_selesai = $this->calculateEndTime($jam_mulai, $layanan['durasi_menit']);
    
            // Validate time slot
            $this->validateTimeSlot($data, $layanan['durasi_menit'], $jam_mulai);
    
            $this->conn->begin_transaction();
    
            try {
                $kode_janji = $this->generateAppointmentCode();
                
                // Insert janji temu dengan pasien_id yang benar
                $stmt = $this->conn->prepare("
                    INSERT INTO janji_temu (
                        kode_janji, pasien_id, psikolog_id, layanan_id,
                        tanggal, jam_mulai, jam_selesai, status
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, 'Terjadwal')
                ");

                $stmt->bind_param(
                    "siissss",
                    $kode_janji,
                    $pasienId,
                    $data['psikolog_id'],
                    $data['layanan_id'],
                    $data['tanggal'],
                    $jam_mulai,
                    $jam_selesai
                );

                if (!$stmt->execute()) {
                    throw new Exception("Gagal menyimpan janji temu: " . $stmt->error);
                }

                $janji_temu_id = $stmt->insert_id;
                $this->insertAppointmentDetails($janji_temu_id, $data);
                
                $this->conn->commit();
                
                // Buat notifikasi
                $this->notificationService->createAppointmentNotification($userId, $janji_temu_id);
                
                return [
                    'status' => 'success',
                    'message' => 'Janji temu berhasil dibuat',
                    'appointment_id' => $janji_temu_id,
                    'redirect' => 'index.php'
                ];
    
            } catch (Exception $e) {
                $this->conn->rollback();
                error_log("Database error: " . $e->getMessage());
                throw new Exception("Gagal menyimpan janji temu: " . $e->getMessage());
            }
        } catch (Exception $e) {
            error_log("Validation error: " . $e->getMessage());
            throw $e;
        }
    }
    

    private function getPasienIdFromUserId($userId) {
        $stmt = $this->conn->prepare("
        SELECT p.id 
        FROM pasien p
        INNER JOIN users u ON p.id = u.reference_id 
        WHERE u.id = ? AND u.role = 'pasien'
    ");
    
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return null;
    }
    
    $pasien = $result->fetch_assoc();
    return $pasien['id'];
}




    private function validateRequiredFields($data) {
        $required_fields = [
            'psikolog_id', 
            'layanan_id', 
            'tanggal', 
            'jam_mulai',
            'keluhan_utama', 
            'durasi_keluhan', 
            'harapan_konsultasi'
        ];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new Exception("Field $field harus diisi");
            }
        }
    }

    private function getServiceDetails($layananId) {
        $stmt = $this->conn->prepare("SELECT durasi_menit FROM jenis_layanan WHERE id = ?");
        $stmt->bind_param("i", $layananId);
        $stmt->execute();
        $result = $stmt->get_result();
        $layanan = $result->fetch_assoc();

        if (!$layanan) {
            throw new Exception('Layanan tidak valid');
        }

        return $layanan;
    }

    private function calculateEndTime($startTime, $duration) {
        return date('H:i:s', strtotime($startTime . ' + ' . $duration . ' minutes'));
    }

    private function validateTimeSlot($data, $duration, $startTime) {
        $available_slots = $this->timeSlotService->getAvailableSlots(
            $data['psikolog_id'],
            $data['tanggal'],
            $duration
        );

        $slot_found = false;
        foreach ($available_slots as $slot) {
            if ($slot['start'] === date('H:i', strtotime($startTime))) {
                $slot_found = true;
                break;
            }
        }

        if (!$slot_found) {
            throw new Exception('Jadwal yang dipilih tidak tersedia');
        }
    }

    private function generateAppointmentCode() {
        return 'JT' . date('ymd') . rand(1000, 9999);
    }

    private function insertAppointmentRecord($kodeJanji, $data, $userId, $jamMulai, $jamSelesai) {
        $stmt = $this->conn->prepare("
            INSERT INTO janji_temu (
                kode_janji, pasien_id, psikolog_id, layanan_id,
                tanggal, jam_mulai, jam_selesai, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 'Terjadwal')
        ");

        $stmt->bind_param(
            "siissss",
            $kodeJanji,
            $userId,
            $data['psikolog_id'],
            $data['layanan_id'],
            $data['tanggal'],
            $jamMulai,
            $jamSelesai
        );

        if (!$stmt->execute()) {
            throw new Exception('Gagal membuat janji temu');
        }

        return $stmt->insert_id;
    }

    private function insertAppointmentDetails($janjiTemuId, $data) {
        $stmt = $this->conn->prepare("
            INSERT INTO alasan_kunjungan (
                janji_temu_id, keluhan_utama, durasi_keluhan, harapan_konsultasi
            ) VALUES (?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "isss",
            $janjiTemuId,
            $data['keluhan_utama'],
            $data['durasi_keluhan'],
            $data['harapan_konsultasi']
        );

        if (!$stmt->execute()) {
            throw new Exception('Gagal menyimpan detail janji temu');
        }
    }

    // Di AppointmentService.php, tambahkan validasi:
    private function validatePatientExists($patientId) {
        $stmt = $this->conn->prepare("SELECT id FROM pasien WHERE id = ?");
        $stmt->bind_param("i", $patientId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Data pasien tidak ditemukan");
        }
    }


    public function cancelAppointment($appointmentId, $userId) {
        try {
            // Validate appointment exists and can be cancelled
            $this->validateAppointmentForCancellation($appointmentId);
            
            $this->conn->begin_transaction();
            
            try {
                // Get current status
                $currentAppointment = $this->getAppointmentData($appointmentId);
                
                // Update appointment status
                $stmt = $this->conn->prepare("
                    UPDATE janji_temu 
                    SET status = 'Dibatalkan', 
                        updated_at = CURRENT_TIMESTAMP 
                    WHERE id = ?
                ");
                
                $stmt->bind_param("i", $appointmentId);
                
                if (!$stmt->execute()) {
                    throw new Exception("Gagal mengupdate status janji temu");
                }
                
                // Log the cancellation
                $stmt = $this->conn->prepare("
                    INSERT INTO janji_temu_log 
                    (janji_temu_id, status_lama, status_baru, catatan, created_by) 
                    VALUES (?, ?, 'Dibatalkan', 'Dibatalkan oleh admin', ?)
                ");
                
                $stmt->bind_param(
                    "isi", 
                    $appointmentId, 
                    $currentAppointment['status'], 
                    $userId
                );
                
                if (!$stmt->execute()) {
                    throw new Exception("Gagal mencatat pembatalan janji temu");
                }
                
                $this->conn->commit();
                
                return [
                    'status' => 'success',
                    'message' => 'Janji temu berhasil dibatalkan',
                    'redirect' => 'index.php'
                ];
                
            } catch (Exception $e) {
                $this->conn->rollback();
                throw $e;
            }
        } catch (Exception $e) {
            throw new Exception("Gagal membatalkan janji temu: " . $e->getMessage());
        }
    }
    
    private function validateAppointmentForCancellation($appointmentId) {
        $stmt = $this->conn->prepare("
            SELECT status 
            FROM janji_temu 
            WHERE id = ?
        ");
        
        $stmt->bind_param("i", $appointmentId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Janji temu tidak ditemukan");
        }
        
        $appointment = $result->fetch_assoc();
        $nonCancellableStatuses = ['Selesai', 'Dibatalkan', 'Tidak Hadir'];
        
        if (in_array($appointment['status'], $nonCancellableStatuses)) {
            throw new Exception("Janji temu tidak dapat dibatalkan karena status sudah " . $appointment['status']);
        }
    }


    private function getAppointmentData($id) {
        $stmt = $this->conn->prepare("
            SELECT jt.*, ak.keluhan_utama, ak.durasi_keluhan
            FROM janji_temu jt
            LEFT JOIN alasan_kunjungan ak ON jt.id = ak.janji_temu_id
            WHERE jt.id = ?
        ");
        
        if (!$stmt->bind_param("i", $id)) {
            throw new Exception("Gagal binding parameter");
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Gagal mengambil data janji temu");
        }
        
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}