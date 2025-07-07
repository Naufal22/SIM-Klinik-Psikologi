<div id="main" class="layout-navbar navbar-fixed">
    <header>
        <nav class="navbar navbar-expand navbar-light navbar-top">
            <div class="container-fluid">
                <a href="#" class="burger-btn d-block">
                    <i class="bi bi-justify fs-3"></i>
                </a>

                <button
                    class="navbar-toggler"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent"
                    aria-controls="navbarSupportedContent"
                    aria-expanded="false"
                    aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ms-auto mb-lg-0">
                        <li class="nav-item dropdown me-1">
                        </li>
                        <li class="nav-item dropdown me-3">
                        </li>
                    </ul>
                    <div class="dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="user-menu d-flex">
                                <div class="user-name text-end me-3">
                                    <h6 class="mb-0 text-gray-600"><?= htmlspecialchars($_SESSION['nama'] ?? 'Pengguna') ?></h6>
                                    <p class="mb-0 text-sm text-gray-600"><?= ucfirst(htmlspecialchars($_SESSION['role'] ?? '')) ?></p>
                                </div>
                                <div class="user-img d-flex align-items-center">
                                    <div class="avatar avatar-md">
                                        <?php if (isPsikolog()): ?>
                                            <img src="<?= $main_url ?>uploads/psikolog/<?= $_SESSION['foto'] ?>"  alt="Foto Profil" />
                                        <?php else: ?>
                                            <img src="<?= $main_url ?>_dist/assets/compiled/jpg/1.jpg" alt="Foto Default" />
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <ul
                            class="dropdown-menu dropdown-menu-end"
                            aria-labelledby="dropdownMenuButton"
                            style="min-width: 11rem">
                            <li>
                                <h6 class="dropdown-header">Hello, <?= htmlspecialchars($_SESSION['nama'] ?? 'Pengguna') ?></h6>
                            </li>
                            <li>
                                <hr class="dropdown-divider" />
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?= $main_url ?>auth/logout.php"><i class="icon-mid bi bi-box-arrow-left me-2"></i>
                                    Logout</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
    </header>