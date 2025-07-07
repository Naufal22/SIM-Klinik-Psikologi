<?php
class AdminAppointmentService {
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
    
            // Validasi data
            $this->validateRequiredFields($data);
            $this->validatePatientExists($data['pasien_id']); 
            $layanan = $this->getServiceDetails($data['layanan_id']);
            
            // Calculate end time
            $jam_mulai = $data['jam_mulai'];
            $jam_selesai = $this->calculateEndTime($jam_mulai, $layanan['durasi_menit']);
    
            // Validate time slot
            $this->validateTimeSlot($data, $layanan['durasi_menit'], $jam_mulai);
    
            $this->conn->begin_transaction();
    
            try {
                $kode_janji = $this->generateAppointmentCode();
                // Gunakan pasien_id dari form, bukan user_id
                $janji_temu_id = $this->insertAppointmentRecord($kode_janji, $data, $data['pasien_id'], $jam_mulai, $jam_selesai);
                $this->insertAppointmentDetails($janji_temu_id, $data);
    
                $this->conn->commit();
    
                return [
                    'status' => 'success',
                    'message' => 'Janji temu berhasil dibuat oleh admin',
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

    public function updateAppointment($id, $data) {
        try {
            // Validasi data dasar
            $this->validateRequiredFields($data);
            $this->validateAppointmentExists($id);
            $layanan = $this->getServiceDetails($data['layanan_id']);
            
            // Get current appointment data
            $currentAppointment = $this->getAppointmentData($id);
            
            // Validasi status appointment
            if ($currentAppointment['status'] !== 'Terjadwal') {
                throw new Exception("Hanya janji temu dengan status 'Terjadwal' yang dapat diubah");
            }
            
            $jam_mulai = $data['jam_mulai'];
            $jam_selesai = $this->calculateEndTime($jam_mulai, $layanan['durasi_menit']);
            
            // Validasi perubahan jadwal
            $isScheduleChanged = 
                $currentAppointment['tanggal'] != $data['tanggal'] || 
                $currentAppointment['jam_mulai'] != $jam_mulai || 
                $currentAppointment['psikolog_id'] != $data['psikolog_id'];
            
            if ($isScheduleChanged) {
                $this->validateTimeSlot($data, $layanan['durasi_menit'], $jam_mulai, $id);
            }
            
            $this->conn->begin_transaction();
            
            try {
                // Update janji_temu
                $stmt = $this->conn->prepare("
                    UPDATE janji_temu 
                    SET psikolog_id = ?, 
                        layanan_id = ?, 
                        tanggal = ?, 
                        jam_mulai = ?, 
                        jam_selesai = ?, 
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = ? AND status = 'Terjadwal'
                ");
                
                $stmt->bind_param(
                    "iisssi",
                    $data['psikolog_id'],
                    $data['layanan_id'],
                    $data['tanggal'],
                    $jam_mulai,
                    $jam_selesai,
                    $id
                );
                
                if (!$stmt->execute()) {
                    throw new Exception("Gagal mengupdate janji temu");
                }
                
                if ($stmt->affected_rows === 0) {
                    throw new Exception("Janji temu tidak dapat diubah atau sudah tidak berstatus 'Terjadwal'");
                }
                
                // Update alasan_kunjungan
                $stmt = $this->conn->prepare("
                    UPDATE alasan_kunjungan 
                    SET keluhan_utama = ?, 
                        durasi_keluhan = ?, 
                        riwayat_pengobatan = ?, 
                        harapan_konsultasi = ?,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE janji_temu_id = ?
                ");
                
                $riwayat_pengobatan = $data['riwayat_pengobatan'] ?? '';
                
                $stmt->bind_param(
                    "ssssi",
                    $data['keluhan_utama'],
                    $data['durasi_keluhan'],
                    $riwayat_pengobatan,
                    $data['harapan_konsultasi'],
                    $id
                );
                
                if (!$stmt->execute()) {
                    throw new Exception("Gagal mengupdate alasan kunjungan");
                }
                
                // Log perubahan dengan detail yang lebih lengkap
                $changes = $this->getDetailedChanges($currentAppointment, $data);
                if (!empty($changes)) {
                    $this->logAppointmentUpdate($id, $changes);
                }
                
                $this->conn->commit();
                
                return [
                    'status' => 'success',
                    'message' => 'Janji temu berhasil diupdate',
                    'redirect' => 'view.php?id=' . $id
                ];
                
            } catch (Exception $e) {
                $this->conn->rollback();
                throw $e;
            }
        } catch (Exception $e) {
            throw new Exception("Gagal mengupdate janji temu: " . $e->getMessage());
        }
    }

    private function getDetailedChanges($oldData, $newData) {
        $changes = [];
        
        // Cek perubahan psikolog
        if ($oldData['psikolog_id'] != $newData['psikolog_id']) {
            $changes[] = "Perubahan psikolog";
        }
        
        // Cek perubahan jadwal
        if ($oldData['tanggal'] != $newData['tanggal']) {
            $changes[] = "Perubahan tanggal dari " . $oldData['tanggal'] . " ke " . $newData['tanggal'];
        }
        
        if ($oldData['jam_mulai'] != $newData['jam_mulai']) {
            $changes[] = "Perubahan jam dari " . $oldData['jam_mulai'] . " ke " . $newData['jam_mulai'];
        }
        
        // Cek perubahan layanan
        if ($oldData['layanan_id'] != $newData['layanan_id']) {
            $changes[] = "Perubahan layanan";
        }
        
        // Cek perubahan keluhan dan informasi tambahan
        if ($oldData['keluhan_utama'] != $newData['keluhan_utama']) {
            $changes[] = "Perubahan keluhan utama";
        }
        
        if ($oldData['durasi_keluhan'] != $newData['durasi_keluhan']) {
            $changes[] = "Perubahan durasi keluhan";
        }
        
        return $changes;
    }
    

    private function logAppointmentUpdate($id, $changes) {
        $stmt = $this->conn->prepare("
            INSERT INTO janji_temu_log 
            (janji_temu_id, status_lama, status_baru, catatan, created_by) 
            VALUES (?, 'Terjadwal', 'Terjadwal', ?, ?)
        ");
        
        $catatan = "Update janji temu: " . implode(", ", $changes);
        $created_by = $_SESSION['user_id'] ?? null;
        
        if (!$stmt->bind_param("isi", $id, $catatan, $created_by)) {
            throw new Exception("Gagal binding parameter log");
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Gagal menyimpan log perubahan");
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

    public function updateStatus($id, $newStatus, $userId) {
        try {
            $this->validateAppointmentExists($id);
            $this->validateStatus($newStatus);
            
            $this->conn->begin_transaction();
            
            try {
                // Get current status
                $currentAppointment = $this->getAppointmentData($id);
                if (!$currentAppointment) {
                    throw new Exception("Janji temu tidak ditemukan");
                }
                
                // Update status
                $stmt = $this->conn->prepare("
                    UPDATE janji_temu 
                    SET status = ?, 
                        updated_at = CURRENT_TIMESTAMP 
                    WHERE id = ?
                ");
                $stmt->bind_param("si", $newStatus, $id);
                
                if (!$stmt->execute()) {
                    throw new Exception("Gagal mengupdate status");
                }
                
                $this->conn->commit();
                
                return [
                    'status' => 'success',
                    'message' => 'Status janji temu berhasil diupdate'
                ];
                
            } catch (Exception $e) {
                $this->conn->rollback();
                throw $e;
            }
        } catch (Exception $e) {
            throw new Exception("Gagal mengupdate status: " . $e->getMessage());
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

    private function updateAppointmentStatus($appointmentId, $status) {
        $stmt = $this->conn->prepare("
            UPDATE janji_temu 
            SET status = ?, 
                updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        
        $stmt->bind_param("si", $status, $appointmentId);
        
        if (!$stmt->execute()) {
            throw new Exception("Gagal mengupdate status janji temu");
        }
    }

    private function logAppointmentCancellation($appointmentId, $userId) {
        $stmt = $this->conn->prepare("
            INSERT INTO janji_temu_log 
            (janji_temu_id, status_lama, status_baru, catatan, dibuat_oleh) 
            VALUES (?, 'Terjadwal', 'Dibatalkan', 'Dibatalkan oleh admin', ?)
        ");
        
        $stmt->bind_param("ii", $appointmentId, $userId);
        
        if (!$stmt->execute()) {
            throw new Exception("Gagal mencatat pembatalan janji temu");
        }
    }

    private function validateAppointmentExists($id) {
        $stmt = $this->conn->prepare("
            SELECT id FROM janji_temu WHERE id = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows === 0) {
            throw new Exception("Janji temu tidak ditemukan");
        }
    }

    private function validateStatus($status) {
        $validStatuses = ['Terjadwal', 'Check-in', 'Dalam_Konsultasi', 'Selesai', 'Dibatalkan', 'Tidak Hadir'];
        
        if (!in_array($status, $validStatuses)) {
            throw new Exception("Status tidak valid");
        }
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

    private function validatePatientExists($patientId) {
        $stmt = $this->conn->prepare("SELECT id FROM pasien WHERE id = ?");
        $stmt->bind_param("i", $patientId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Data pasien tidak ditemukan");
        }
    }
}