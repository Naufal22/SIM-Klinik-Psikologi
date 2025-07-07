<?php




$menuConfig = [
    'dashboard' => [ROLE_ADMIN, ROLE_PSIKOLOG, ROLE_PASIEN],
    'pasien' => [ROLE_ADMIN, ROLE_PSIKOLOG],
    'psikolog' => [ROLE_ADMIN],
    'jadwal_psikolog' => [ROLE_PSIKOLOG],
    'layanan' => [ROLE_ADMIN],
    'janji-temu' => [ROLE_ADMIN, ROLE_PSIKOLOG],
    'konsultasi' => [ROLE_PSIKOLOG],
    'pembayaran' => [ROLE_ADMIN],
    'master-data' => [ROLE_MASTER],
    'master-data-staff' => [ROLE_MASTER ,ROLE_ADMIN]
];

function canAccessMenu($menuKey, $userRole) {
    global $menuConfig;
    return in_array($userRole, $menuConfig[$menuKey] ?? []);
}
?>

<div class="sidebar-menu">
                    <ul class="menu">
                        <li class="sidebar-title">Menu</li>

                            <?php if (canAccessMenu('dashboard', $_SESSION['role'])): ?>
                            <li class="sidebar-item <?php if ($activePage == 'dashboard') { echo 'active'; } ?>">
                                <a href="<?= $main_url ?>modules/dashboard/<?=$_SESSION['role']?>/index.php" class="sidebar-link">
                                    <i class="bi bi-grid-fill"></i>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                            <?php endif; ?>

                            <?php if (canAccessMenu('pasien', $_SESSION['role'])): ?>
                            <li class="sidebar-item <?php if ($activePage == 'pasien') { echo 'active'; } ?>">
                                <a href="<?= $main_url ?>modules/pasien/" class="sidebar-link">
                                    <i class="bi bi-grid-fill"></i>
                                    <span>Pasien</span>
                                </a>
                            </li>
                            <?php endif; ?>

                            <?php if (canAccessMenu('psikolog', $_SESSION['role'])): ?>
                            <li class="sidebar-item <?php if ($activePage == 'psikolog') { echo 'active'; } ?>">
                                <a href="<?= $main_url ?>modules/psikolog/" class="sidebar-link">
                                    <i class="bi bi-grid-fill"></i>
                                    <span>Psikolog</span>
                                </a>
                            </li>
                            <?php endif; ?>

                            <?php if (canAccessMenu('jadwal_psikolog', $_SESSION['role'])): ?>
                            <li class="sidebar-item <?php if ($activePage == 'jadwal_psikolog') { echo 'active'; } ?>">
                                <a href="<?= $main_url ?>modules/psikolog/jadwal.php" class="sidebar-link">
                                    <i class="bi bi-grid-fill"></i>
                                    <span>Jadwal Praktek</span>
                                </a>
                            </li>
                            <?php endif; ?>

                            <?php if (canAccessMenu('layanan', $_SESSION['role'])): ?>
                            <li class="sidebar-item <?php if ($activePage == 'layanan') { echo 'active'; } ?>">
                                <a href="<?= $main_url ?>modules/layanan/" class="sidebar-link">
                                    <i class="bi bi-grid-fill"></i>
                                    <span>Layanan</span>
                                </a>
                            </li>
                            <?php endif; ?>

                            <?php if (canAccessMenu('janji-temu', $_SESSION['role'])): ?>
                            <li class="sidebar-item <?php if ($activePage == 'janji-temu') { echo 'active'; } ?>">
                                <a href="<?= $main_url ?>modules/janji-temu/" class="sidebar-link">
                                    <i class="bi bi-grid-fill"></i>
                                    <span>Janji Temu</span>
                                </a>
                            </li>
                            <?php endif; ?>

                            <?php if (canAccessMenu('konsultasi', $_SESSION['role'])): ?>
                            <li class="sidebar-item <?php if ($activePage == 'konsultasi') { echo 'active'; } ?>">
                                <a href="<?= $main_url ?>modules/konsultasi/" class="sidebar-link">
                                    <i class="bi bi-grid-fill"></i>
                                    <span>Konsultasi</span>
                                </a>
                            </li>
                            <?php endif; ?>

                            <?php if (canAccessMenu('pembayaran', $_SESSION['role'])): ?>
                            <li class="sidebar-item <?php if ($activePage == 'pembayaran') { echo 'active'; } ?>">
                                <a href="<?= $main_url ?>modules/pembayaran1/" class="sidebar-link">
                                    <i class="bi bi-grid-fill"></i>
                                    <span>Pembayaran</span>
                                </a>
                            </li>
                            <?php endif; ?>


                        <?php if (canAccessMenu('master-data-staff', $_SESSION['role'])): ?>
                        <li class="sidebar-item has-sub">
                            <a href="#" class="sidebar-link">
                                <i class="bi bi-grid-1x2-fill"></i>
                                <span>Master Data</span>
                            </a>

                            <ul class="submenu">
                                <li class="submenu-item" <?php if ($activePage == 'master-data-staff') { echo 'active'; } ?>">
                                    <a href="<?= $main_url ?>modules/master-data/staff.php"  class="submenu-link">Account Staff</a>
                                </li>
                                <?php if (canAccessMenu('master-data', $_SESSION['role'])): ?>
                                <li class="submenu-item"<?php if ($activePage == 'master-data-pasien') { echo 'active'; } ?>">
                                    <a href="<?= $main_url ?>modules/master-data/pasien.php"  class="submenu-link">Account Pasien</a>
                                </li>
                                <?php endif; ?>


                            </ul>
                        </li>
                        <?php endif; ?>

                    </ul>
                </div>
            </div>
        </div>