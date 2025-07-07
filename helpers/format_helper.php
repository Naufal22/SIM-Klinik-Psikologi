<?php
if (!function_exists('formatCurrency')) {
    function formatCurrency($amount) {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}
