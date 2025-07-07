<?php
session_start();
require_once '../../auth/functions.php';


requireRole([ROLE_ADMIN, ROLE_PSIKOLOG]);
checkSessionTimeout();
?>


<div class="sidebar-menu">
                    <ul class="menu">
                        <li class="sidebar-title">Menu</li>

                        <li class="sidebar-item <?php if ($activePage == 'dashboard') { echo 'active'; } ?>">
                                <a href="index.html" class="sidebar-link">
                                    <i class="bi bi-grid-fill"></i>
                                    <span>Dashboard</span>
                                </a>
                            </li>

                            <li class="sidebar-item <?php if ($activePage == 'pasien') { echo 'active'; } ?>">
                                <a href="<?= $main_url ?>modules/pasien/" class="sidebar-link">
                                    <i class="bi bi-grid-fill"></i>
                                    <span>Pasien</span>
                                </a>
                            </li>

                            <li class="sidebar-item <?php if ($activePage == 'psikolog') { echo 'active'; } ?>">
                                <a href="<?= $main_url ?>modules/psikolog/" class="sidebar-link">
                                    <i class="bi bi-grid-fill"></i>
                                    <span>Psikolog</span>
                                </a>
                            </li>

                            <li class="sidebar-item <?php if ($activePage == 'layanan') { echo 'active'; } ?>">
                                <a href="<?= $main_url ?>modules/layanan/" class="sidebar-link">
                                    <i class="bi bi-grid-fill"></i>
                                    <span>Layanan</span>
                                </a>
                            </li>

                            <li class="sidebar-item <?php if ($activePage == 'janji-temu') { echo 'active'; } ?>">
                                <a href="<?= $main_url ?>modules/janji-temu/" class="sidebar-link">
                                    <i class="bi bi-grid-fill"></i>
                                    <span>Janji Temu</span>
                                </a>
                            </li>

                            <li class="sidebar-item <?php if ($activePage == 'konsultasi') { echo 'active'; } ?>">
                                <a href="<?= $main_url ?>modules/konsultasi/" class="sidebar-link">
                                    <i class="bi bi-grid-fill"></i>
                                    <span>Konsultasi</span>
                                </a>
                            </li>

                            <li class="sidebar-item <?php if ($activePage == 'pembayaran') { echo 'active'; } ?>">
                                <a href="<?= $main_url ?>modules/pembayaran/" class="sidebar-link">
                                    <i class="bi bi-grid-fill"></i>
                                    <span>Pembayaran</span>
                                </a>
                            </li>



                        <li class="sidebar-item has-sub">
                            <a href="#" class="sidebar-link">
                                <i class="bi bi-grid-1x2-fill"></i>
                                <span>Master Data</span>
                            </a>

                            <ul class="submenu">
                                <li class="submenu-item" <?php if ($activePage == 'master-data-staff') { echo 'active'; } ?>">
                                    <a href="<?= $main_url ?>modules/master-data/staff.php"  class="submenu-link">Account Staff</a>
                                </li>

                                <li class="submenu-item"<?php if ($activePage == 'master-data-pasien') { echo 'active'; } ?>">
                                    <a href="<?= $main_url ?>modules/master-data/pasien.php"  class="submenu-link">Account Pasien</a>
                                </li>

                            </ul>
                        </li>
                        <li class="sidebar-item has-sub">
                            <a href="#" class="sidebar-link">
                                <i class="bi bi-grid-1x2-fill"></i>
                                <span>Layouts</span>
                            </a>

                            <ul class="submenu active">
                                <li class="submenu-item">
                                    <a href="layout-default.html" class="submenu-link">Default Layout</a>
                                </li>

                                <li class="submenu-item">
                                    <a href="layout-vertical-1-column.html" class="submenu-link">1 Column</a>
                                </li>

                                <li class="submenu-item active">
                                    <a href="layout-vertical-navbar.html" class="submenu-link">Vertical Navbar</a>
                                </li>

                                <li class="submenu-item">
                                    <a href="layout-rtl.html" class="submenu-link">RTL Layout</a>
                                </li>

                                <li class="submenu-item">
                                    <a href="layout-horizontal.html" class="submenu-link">Horizontal Menu</a>
                                </li>
                            </ul>
                        </li>

                    </ul>
                </div>
            </div>
        </div>