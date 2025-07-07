<?php
function getVisitTrend($conn) {
    return mysqli_query($conn, "
        WITH RECURSIVE dates AS (
            SELECT CURDATE() - INTERVAL 6 DAY as date
            UNION ALL
            SELECT date + INTERVAL 1 DAY
            FROM dates
            WHERE date < CURDATE()
        )
        SELECT 
            dates.date,
            COUNT(j.id) as total_visits
        FROM dates
        LEFT JOIN janji_temu j ON DATE(j.tanggal) = dates.date
        GROUP BY dates.date
        ORDER BY dates.date
    ");
}

function getTodayAppointments($conn) {
    return mysqli_query($conn, "
        SELECT 
            j.id,
            j.kode_janji,
            j.jam_mulai,
            j.jam_selesai,
            j.status,
            p.nama_lengkap as nama_pasien,
            ps.nama as nama_psikolog,
            jl.nama_layanan
        FROM janji_temu j
        JOIN pasien p ON j.pasien_id = p.id
        JOIN psikolog ps ON j.psikolog_id = ps.id
        JOIN jenis_layanan jl ON j.layanan_id = jl.id
        WHERE DATE(j.tanggal) = CURDATE()
        ORDER BY j.jam_mulai
    ");
}

function getActivePsychologists($conn) {
    return mysqli_query($conn, "
        SELECT DISTINCT
            p.nama,
            p.spesialisasi,
            p.foto,
            COUNT(j.id) as sessions_today,
            MAX(CASE 
                WHEN j.status = 'Dalam_Konsultasi' THEN 'Konsultasi'
                WHEN j.status = 'Terjadwal' THEN 'Tersedia'
                ELSE 'Selesai'
            END) as current_status
        FROM psikolog p
        LEFT JOIN janji_temu j ON p.id = j.psikolog_id AND DATE(j.tanggal) = CURDATE()
        WHERE p.status = 'Aktif'
        GROUP BY p.id, p.nama, p.spesialisasi, p.foto
    ");
}

function getServiceStats($conn) {
    return mysqli_query($conn, "
        SELECT 
            jl.nama_layanan,
            COUNT(j.id) as total_sessions,
            COUNT(j.id) * 100.0 / (
                SELECT COUNT(*) FROM janji_temu 
                WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            ) as percentage
        FROM jenis_layanan jl
        LEFT JOIN janji_temu j ON jl.id = j.layanan_id 
            AND j.tanggal >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        WHERE jl.status = 'Aktif'
        GROUP BY jl.id, jl.nama_layanan
        ORDER BY total_sessions DESC
        LIMIT 5
    ");
}

function getRecentActivities($conn) {
    return mysqli_query($conn, "
        SELECT 
            j.status,
            j.updated_at,
            p.nama_lengkap as nama_pasien,
            ps.nama as nama_psikolog
        FROM janji_temu j
        JOIN pasien p ON j.pasien_id = p.id
        JOIN psikolog ps ON j.psikolog_id = ps.id
        WHERE j.updated_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ORDER BY j.updated_at DESC
        LIMIT 5
    ");
}