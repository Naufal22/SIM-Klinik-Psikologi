
<body>
    <script src="<?= $main_url ?>_dist/assets/static/js/initTheme.js"></script>
    <div id="app">
        <div id="main" class="layout-horizontal">
            <header class="mb-5">
                <nav class="navbar navbar-expand-lg navbar-custom">
                    <div class="container">
                        <!-- Logo -->
                        <a href="index.php" class="logo-mindful">
                            Assyifa
                        </a>

                        <!-- Burger button responsive -->
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                            <i class="bi bi-justify fs-3"></i>
                        </button>

                        <div class="collapse navbar-collapse" id="navbarContent">
                            <!-- Center Navigation -->
                            <ul class="navbar-nav nav-center">
                                <li class="nav-item">
                                    <a href="<?= $main_url ?>modules/dashboard/pasien/index.php" class="nav-link <?php if ($activePage == 'dashboard-pasien') { echo 'active'; } ?>">Home</a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?= $main_url ?>modules/psikolog/jadwal-user.php" class="nav-link <?php if ($activePage == 'jadwal-psikolog') { echo 'active'; } ?>">Jadwal Psikolog</a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?= $main_url ?>modules/pasien/edit.php" class="nav-link <?php if ($activePage == 'data-diri') { echo 'active'; } ?>">Data Diri</a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?= $main_url ?>modules/janji-temu/index-user.php" class="nav-link <?php if ($activePage == 'janji-temu') { echo 'active'; } ?>">Janji Temu</a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?= $main_url ?>modules/skrining.php" class="nav-link  <?php if ($activePage == 'skrining') { echo 'active'; } ?>">Skrining</a>
                                </li>
                            </ul>

                            <!-- Right Controls -->
                            <div class="user-controls">
                                <!-- Simple Theme Toggle -->



                                <!-- User Menu -->
                                <div class="dropdown">
                                    <a href="#" class="d-flex align-items-center dropdown-toggle" data-bs-toggle="dropdown">
                                        <div class="avatar avatar-md2">
                                            <img src="<?= $main_url ?>_dist/assets/compiled/jpg/1.jpg" alt="Avatar">
                                        </div>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <!-- <li><a class="dropdown-item" href="#">My Account</a></li>
                                        <li><a class="dropdown-item" href="#">Settings</a></li> -->
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="<?= $main_url ?>auth/logout.php">Logout</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </nav>
            </header>