<?php
require_once '../../config/database.php';

/**
 * Get available time slots for a specific psychologist on a given date
 */
function getAvailableSlots($psikolog_id, $tanggal, $durasi = 30) {
    global $conn;
    
    // Get day of week (in Indonesian)
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

    // Get psychologist's schedule for that day
    $query = "SELECT jam_mulai, jam_selesai 
              FROM jadwal_psikolog 
              WHERE psikolog_id = ? 
              AND hari = ? 
              AND status = 'Aktif'";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        return ['error' => 'Database error: ' . $conn->error];
    }

    $stmt->bind_param("is", $psikolog_id, $hari);
    if (!$stmt->execute()) {
        return ['error' => 'Execution error: ' . $stmt->error];
    }

    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return ['error' => 'Tidak ada jadwal praktek untuk hari ini'];
    }

    $jadwal = $result->fetch_assoc();
    $slots = [];
    
    // Get existing appointments
    $query = "SELECT jam_mulai, jam_selesai 
              FROM janji_temu 
              WHERE psikolog_id = ? 
              AND tanggal = ? 
              AND status NOT IN ('Dibatalkan', 'Tidak Hadir')";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        return ['error' => 'Database error: ' . $conn->error];
    }

    $stmt->bind_param("is", $psikolog_id, $tanggal);
    if (!$stmt->execute()) {
        return ['error' => 'Execution error: ' . $stmt->error];
    }

    $result = $stmt->get_result();
    
    $booked_slots = [];
    while ($row = $result->fetch_assoc()) {
        $booked_slots[] = [
            'start' => strtotime($row['jam_mulai']),
            'end' => strtotime($row['jam_selesai'])
        ];
    }

    // Generate available slots based on service duration
    $start_time = strtotime($jadwal['jam_mulai']);
    $end_time = strtotime($jadwal['jam_selesai']);
    $slot_duration = $durasi * 60; // Convert minutes to seconds
    
    while ($start_time + $slot_duration <= $end_time) {
        $slot_end = $start_time + $slot_duration;
        
        // Check if slot overlaps with any booked appointment
        $is_available = true;
        foreach ($booked_slots as $booked) {
            if ($start_time < $booked['end'] && $slot_end > $booked['start']) {
                $is_available = false;
                break;
            }
        }
        
        // Check if slot is in the future (for today's appointments)
        if (date('Y-m-d') == $tanggal) {
            $current_time = time();
            if ($start_time <= $current_time) {
                $is_available = false;
            }
        }
        
        if ($is_available) {
            $slots[] = [
                'start' => date('H:i', $start_time),
                'end' => date('H:i', $slot_end)
            ];
        }
        
        $start_time += $slot_duration;
    }
    
    return $slots;
}

/**
 * Validate appointment time slot
 */
function validateTimeSlot($psikolog_id, $tanggal, $jam_mulai, $jam_selesai) {
    global $conn;
    
    // Check if slot is within psychologist's schedule
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

    $query = "SELECT COUNT(*) as count 
              FROM jadwal_psikolog 
              WHERE psikolog_id = ? 
              AND hari = ? 
              AND ? BETWEEN jam_mulai AND jam_selesai 
              AND ? BETWEEN jam_mulai AND jam_selesai";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isss", $psikolog_id, $hari, $jam_mulai, $jam_selesai);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        return ['valid' => false, 'message' => 'Waktu yang dipilih di luar jadwal praktek psikolog'];
    }

    // Check for overlapping appointments
    $query = "SELECT COUNT(*) as count 
              FROM janji_temu 
              WHERE psikolog_id = ? 
              AND tanggal = ? 
              AND status NOT IN ('Dibatalkan')
              AND (
                  (? BETWEEN jam_mulai AND jam_selesai)
                  OR (? BETWEEN jam_mulai AND jam_selesai)
                  OR (jam_mulai BETWEEN ? AND ?)
              )";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isssss", $psikolog_id, $tanggal, $jam_mulai, $jam_selesai, $jam_mulai, $jam_selesai);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        return ['valid' => false, 'message' => 'Waktu yang dipilih sudah terisi'];
    }

    return ['valid' => true];
}

/**
 * Create appointment reminder
 */
function createReminder($janji_temu_id, $tanggal, $jam_mulai) {
    global $conn;
    
    // Create reminder for 1 day before appointment
    $reminder_date = date('Y-m-d H:i:s', strtotime("$tanggal $jam_mulai -1 day"));
    
    $query = "INSERT INTO reminder_janji_temu (janji_temu_id, tipe_reminder, waktu_kirim, pesan) 
              VALUES (?, 'WhatsApp', ?, 'Reminder: Anda memiliki janji temu besok')";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $janji_temu_id, $reminder_date);
    return $stmt->execute();
}

/**
 * Create system notification
 */
function createNotification($user_id, $judul, $pesan, $tipe = 'janji_temu') {
    global $conn;
    
    $query = "INSERT INTO notifikasi (user_id, judul, pesan, tipe) 
              VALUES (?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isss", $user_id, $judul, $pesan, $tipe);
    return $stmt->execute();
}