<?php
namespace Tests;

use App\NotificationSystem;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    private $notificationSystem;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Inisialisasi sistem notifikasi
        $this->notificationSystem = new NotificationSystem($this->conn);
        
        // Bersihkan data test
        $this->cleanTestData();
    }
    
    private function cleanTestData(): void
    {
        $this->conn->query("DELETE FROM reminder_janji_temu WHERE janji_temu_id IN (SELECT id FROM janji_temu WHERE kode_janji LIKE 'TEST%')");
        $this->conn->query("DELETE FROM janji_temu WHERE kode_janji LIKE 'TEST%'");
    }
    
    public function testCreateReminder(): void
    {
        // Buat janji temu test
        $appointmentId = $this->createTestAppointment();
        
        // Test pembuatan reminder email
        $resultEmail = $this->notificationSystem->createReminder($appointmentId, 'Email');
        $this->assertTrue($resultEmail, 'Gagal membuat reminder');
        
        // Verifikasi reminder telah dibuat
        $reminder = $this->getReminder($appointmentId, 'Email');
        $this->assertNotNull($reminder, 'Reminder tidak ditemukan');
        $this->assertEquals('Pending', $reminder['status'], 'Status reminder tidak sesuai');
    }
    
    public function testProcessPendingReminders(): void
    {
        // Buat janji temu dan reminder test
        $appointmentId = $this->createTestAppointment();
        $this->notificationSystem->createReminder($appointmentId, 'Email');
        
        // Proses reminder
        $result = $this->notificationSystem->processPendingReminders();
        $this->assertTrue($result, 'Gagal memproses reminder');
        
        // Verifikasi status reminder telah diupdate
        $reminder = $this->getReminder($appointmentId, 'Email');
        $this->assertNotNull($reminder, 'Reminder tidak ditemukan');
        $this->assertEquals('Terkirim', $reminder['status'], 'Status reminder tidak terupdate');
    }
    
    public function testSendStatusUpdateNotification(): void
    {
        // Buat janji temu test
        $appointmentId = $this->createTestAppointment();
        
        // Test pengiriman notifikasi update status
        $result = $this->notificationSystem->sendStatusUpdateNotification(
            $appointmentId, 
            'Check-in'
        );
        
        $this->assertTrue($result, 'Gagal mengirim notifikasi update status');
    }
    
    private function createTestAppointment(): int
    {
        $sql = "INSERT INTO janji_temu (
            kode_janji, 
            pasien_id,
            psikolog_id,
            layanan_id,
            tanggal,
            jam_mulai,
            jam_selesai,
            keluhan_awal,
            status,
            created_at
        ) VALUES (
            'TEST123',
            19,
            12,
            1,
            DATE_ADD(CURDATE(), INTERVAL 1 DAY),
            '09:00:00',
            '10:00:00',
            'Test appointment',
            'Terjadwal',
            NOW()
        )";
        
        $this->conn->query($sql);
        return $this->conn->insert_id;
    }
    
    private function getReminder($appointmentId, $type): ?array
    {
        $sql = "SELECT * FROM reminder_janji_temu 
                WHERE janji_temu_id = ? AND tipe_reminder = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('is', $appointmentId, $type);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    protected function tearDown(): void
    {
        // Bersihkan data test
        $this->cleanTestData();
        parent::tearDown();
    }
}