
<?php
require_once __DIR__ . '/../utils/UserUtil.php';

function renderWelcome($conn, $userId) {
    $userUtil = new UserUtil($conn);
    $displayName = $userUtil->getUserDisplayName($userId);
    ?>
    <div class="page-heading">
        <h2 class="mb-2 display-6 fw-bold">Hai, <?= htmlspecialchars($displayName) ?></h2>
        <p class="fs-5 text-secondary mb-0">Selamat datang di Assyifa Consulting</p>
    </div>
    <?php
}
