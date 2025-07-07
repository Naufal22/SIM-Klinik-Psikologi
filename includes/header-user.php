<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mindful</title>
    
    <link rel="shortcut icon" href="<?= $main_url ?>_dist/assets/compiled/svg/favicon.svg" type="image/x-icon">
    <link rel="stylesheet" href="<?= $main_url ?>_dist/assets/compiled/css/app.css">
    <link rel="stylesheet" href="<?= $main_url ?>_dist/assets/compiled/css/app-dark.css">
    <link rel="stylesheet" href="<?= $main_url ?>_dist/assets/compiled/css/iconly.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .navbar-custom {
            padding: 1rem 0;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        
        [data-bs-theme="dark"] .navbar-custom {
            background: #1e1e2d;
            border-bottom-color: #2d2d3f;
        }

        .navbar-nav {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .nav-center {
            flex: 1;
            justify-content: center;
        }

        .nav-link {
            color: #1e293b;
            font-weight: 500;
            transition: color 0.2s ease;
            position: relative;
            padding: 0.5rem 0;
        }

        [data-bs-theme="dark"] .nav-link {
            color: #cbd5e1 !important;
            font-weight: 600;
        }

        .nav-link:hover {
            color: #3b82f6;
        }

        .nav-link.active {
            color: #3b82f6;
        }

        [data-bs-theme="dark"] .nav-link:hover,
        [data-bs-theme="dark"] .nav-link.active {
            color: #60a5fa !important;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: #3b82f6;
            transition: width 0.3s ease;
        }

        [data-bs-theme="dark"] .nav-link::after {
            background-color: #60a5fa;
        }

        .nav-link:hover::after,
        .nav-link.active::after {
            width: 100%;
        }

        .logo-mindful {
            font-size: 1.75rem;
            font-weight: 700;
            color: #3b82f6;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        [data-bs-theme="dark"] .logo-mindful {
            color: #60a5fa;
        }

        .user-controls {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        [data-bs-theme="dark"] .navbar-toggler {
            color: #e2e8f0;
            border-color: rgba(255, 255, 255, 0.15);
        }

        [data-bs-theme="dark"] .navbar-toggler-icon {
            filter: invert(1);
        }

        /* Dropdown improvements for dark mode */
        [data-bs-theme="dark"] .dropdown-menu {
            background-color: #1e1e2d;
            border-color: #2d2d3f;
        }

        [data-bs-theme="dark"] .dropdown-item {
            color: #e2e8f0;
        }

        [data-bs-theme="dark"] .dropdown-item:hover {
            background-color: #2d2d3f;
            color: #60a5fa;
        }

        [data-bs-theme="dark"] .dropdown-divider {
            border-color: #2d2d3f;
        }

        
    </style>
</head>