<?php
class TimeSlotService {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAvailableSlots($psikolog_id, $tanggal, $durasi) {
        try {
            // Convert date to day name in Indonesian
            $hari = date('l', strtotime($tanggal));
            $hari_indo = [
                'Sunday' => 'Minggu',
                'Monday' => 'Senin',
                'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis',
                'Friday' => 'Jumat',
                'Saturday' => 'Sabtu'
            ];
            $hari = $hari_indo[$hari];

            // Get psychologist's schedule
            $query = "SELECT jam_mulai, jam_selesai 
                     FROM jadwal_psikolog 
                     WHERE psikolog_id = ? 
                     AND hari = ? 
                     AND status = 'Aktif'";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception('Database error: ' . $this->conn->error);
            }

            $stmt->bind_param("is", $psikolog_id, $hari);
            if (!$stmt->execute()) {
                throw new Exception('Execution error: ' . $stmt->error);
            }

            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                return [];
            }

            $jadwal = $result->fetch_assoc();
            
            // Get booked appointments
            $booked_slots = $this->getBookedSlots($psikolog_id, $tanggal);
            
            // Generate available time slots
            return $this->generateTimeSlots(
                $jadwal['jam_mulai'],
                $jadwal['jam_selesai'],
                $durasi,
                $booked_slots,
                $tanggal
            );
        } catch (Exception $e) {
            error_log("Error in getAvailableSlots: " . $e->getMessage());
            throw $e;
        }
    }

    private function getBookedSlots($psikolog_id, $tanggal) {
        $query = "SELECT jam_mulai, jam_selesai 
                 FROM janji_temu 
                 WHERE psikolog_id = ? 
                 AND tanggal = ? 
                 AND status NOT IN ('Dibatalkan', 'Tidak Hadir')";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            throw new Exception('Database error: ' . $this->conn->error);
        }

        $stmt->bind_param("is", $psikolog_id, $tanggal);
        if (!$stmt->execute()) {
            throw new Exception('Execution error: ' . $stmt->error);
        }

        $result = $stmt->get_result();
        $booked_slots = [];
        
        while ($row = $result->fetch_assoc()) {
            $booked_slots[] = [
                'start' => strtotime($row['jam_mulai']),
                'end' => strtotime($row['jam_selesai'])
            ];
        }

        return $booked_slots;
    }

    private function generateTimeSlots($start_time, $end_time, $duration, $booked_slots, $date) {
        $slots = [];
        $current = strtotime($start_time);
        $end = strtotime($end_time);
        $slot_duration = $duration * 60; // Convert to seconds

        while ($current + $slot_duration <= $end) {
            $slot_end = $current + $slot_duration;
            
            if ($this->isSlotAvailable($current, $slot_end, $booked_slots, $date)) {
                $slots[] = [
                    'start' => date('H:i', $current),
                    'end' => date('H:i', $slot_end)
                ];
            }
            
            $current += $slot_duration;
        }

        return $slots;
    }

    private function isSlotAvailable($start, $end, $booked_slots, $date) {
        // Check if slot is in the past
        if (date('Y-m-d') == $date && $start <= time()) {
            return false;
        }

        // Check for overlapping appointments
        foreach ($booked_slots as $booked) {
            if ($start < $booked['end'] && $end > $booked['start']) {
                return false;
            }
        }

        return true;
    }
}