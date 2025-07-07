    <?php
    function renderRecentNotes($recentNotes) {
    ?>
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>Catatan Konsultasi Terbaru</h4>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($recentNotes) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Pasien</th>
                                        <th>Layanan</th>
                                        <th>Diagnosa</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($note = mysqli_fetch_assoc($recentNotes)): ?>
                                        <tr>
                                            <td><?= formatPsikologDate($note['tanggal']) ?></td>
                                            <td><?= htmlspecialchars($note['nama_pasien']) ?></td>
                                            <td><?= htmlspecialchars($note['nama_layanan']) ?></td>
                                            <td>
                                                <?php 
                                                $diagnosa = htmlspecialchars($note['diagnosa']);
                                                echo strlen($diagnosa) > 50 ? substr($diagnosa, 0, 50) . '...' : $diagnosa;
                                                ?>
                                            </td>
                                            <td>
                                                <a href="../../konsultasi/view.php?id=<?= $note['janji_temu_id'] ?>" 
                                                class="btn btn-sm btn-outline-primary">
                                                    Detail
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <p class="text-muted mb-0">Belum ada catatan konsultasi</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php
    }