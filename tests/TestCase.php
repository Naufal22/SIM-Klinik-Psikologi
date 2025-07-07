<?php
namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use mysqli;

class TestCase extends BaseTestCase
{
    protected $conn;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Koneksi ke database test
        $this->conn = new mysqli(
            getenv('DB_HOST') ?: 'localhost',
            getenv('DB_USER') ?: 'root',
            getenv('DB_PASS') ?: '',
            getenv('DB_NAME') ?: 'clinic_system_test'
        );

        if ($this->conn->connect_error) {
            throw new \Exception('Koneksi database gagal: ' . $this->conn->connect_error);
        }
    }

    protected function tearDown(): void
    {
        if ($this->conn) {
            $this->conn->close();
        }
        parent::tearDown();
    }
}