<?php
class ValidationUtils {
    public static function validateDate($date) {
        if (!DateTime::createFromFormat('Y-m-d', $date)) {
            throw new Exception('Format tanggal tidak valid');
        }
        
        $today = new DateTime();
        $inputDate = new DateTime($date);
        
        if ($inputDate < $today) {
            throw new Exception('Tanggal tidak boleh kurang dari hari ini');
        }
    }

    public static function validateTime($time) {
        if (!DateTime::createFromFormat('H:i:s', $time)) {
            throw new Exception('Format waktu tidak valid');
        }
    }

    public static function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeInput'], $input);
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}