<?php
class AppointmentServiceFactory {
    public static function create($conn, $role) {
        switch ($role) {
            case ROLE_PASIEN:
                return new PatientAppointmentService($conn);
            case ROLE_ADMIN:
                return new AdminAppointmentService($conn);
            default:
                throw new Exception('Role tidak valid');
        }
    }
}
?>