<?php

// Fungsi validasi login
function checkSession() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login.php');
        exit();
    }
}
?>
