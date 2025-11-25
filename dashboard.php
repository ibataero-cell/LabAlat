<?php
// Tampilkan error jika ada masalah
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page_title = "Dashboard";
include 'includes/header.php'; 

try {
    $is_admin = ($_SESSION['role'] == 'admin');
    $user_id = $_SESSION['user_id'];

    // ==========================================
    // DATA UNTUK ADMIN
    // ==========================================
    if ($is_admin) {
        // 1. Ambil Statistik Angka Admin
        $stats = $pdo->query("SELECT 
            (SELECT COUNT(*) FROM tbl_alat) as total,
            (SELECT COUNT(*) FROM tbl_alat WHERE status_ketersediaan='Dipinjam') as dipinjam,
            (SELECT COUNT(*) FROM tbl_alat WHERE status_ketersediaan='Tersedia') as tersedia,
            (SELECT COUNT(*) FROM tbl_alat WHERE status_ketersediaan='Perawatan') as perawatan,
            (SELECT COUNT(*) FROM tbl_booking WHERE status_booking='Pending') as pending
        ")->fetch(PDO::FETCH_ASSOC);

        if(!$stats) $stats = ['total'=>0, 'dipinjam'=>0, 'tersedia'=>0, 'perawatan'=>0, 'pending'=>0];

        // 2. Data Grafik Pie
        $stmt_pie = $pdo->query("SELECT status_ketersediaan as l, COUNT(*) as c FROM tbl_alat GROUP BY status_ketersediaan");
        $pie_data_raw = $stmt_pie->fetchAll(PDO::FETCH_KEY_PAIR);
        $pie_values = [$pie_data_raw['Tersedia']??0, $pie_data_raw['Dipinjam']??0, $pie_data_raw['Perawatan']??0];
        $pie_labels = ['Tersedia', 'Dipinjam', 'Perawatan'];

        // 3. Data Grafik Line
        $line_labels = []; $line_values = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $line_labels[] = date('d M', strtotime($date));
            $stmt_line = $pdo->prepare("SELECT COUNT(*) FROM tbl_booking WHERE DATE(waktu_pengajuan) = ?");
            $stmt_line->execute([$date]);
            $line_values[] = $stmt_line->fetchColumn();
        }
    } 
    
    // ==========================================
    // DATA UNTUK USER (STAFF) - UPGRADE BARU
    // ==========================================
    else {
        // 1. Statistik Pribadi User
        $user_stats = $pdo->prepare("SELECT 
            (SELECT COUNT(*) FROM tbl_booking WHERE user_id = ? AND status_booking = 'Disetujui') as aktif,
            (SELECT COUNT(*) FROM tbl_booking WHERE user_id = ? AND status_booking = 'Pending') as pending,
            (SELECT COUNT(*) FROM tbl_booking WHERE user_id = ? AND status_booking = 'Selesai') as riwayat
        ");
        $user_stats->execute([$user_id, $user_id, $user_id]);
        $u_stats = $user_stats->fetch(PDO::FETCH_ASSOC);

        // 2. Ambil Alat yang Sedang Dipinjam (Aktif)
        $stmt_active = $pdo->prepare("SELECT * FROM view_booking_lengkap WHERE user_id = ? AND status_booking = 'Disetujui' ORDER BY waktu_kembali ASC");
        $stmt_active->execute([$user_id]);
        $active_loans = $stmt_active->fetchAll();

        // 3. Ambil Status Pengajuan Terakhir (5 Terakhir)
        $stmt_history = $pdo->prepare("SELECT * FROM view_booking_lengkap WHERE user_id = ? ORDER BY waktu_pengajuan DESC LIMIT 5");
        $stmt_history->execute([$user_id]);
        $recent_history = $stmt_history->fetchAll();
    }

} catch (Exception $e) {
    echo '<div class="alert alert-danger m-3">Error Database: ' . $e->getMessage() . '</div>';
    exit;
}
?>

<div data-aos="fade-up">
    
    <?php if ($is_admin): ?>
        <h2 class="mb-4 fw-bold text-dark">Dashboard Admin</h2>
        
        <div class="row g-4">
            <div class="col-lg-3 col-md-6"><div class="card stat-card shadow-sm border-primary"><div class="card-body"><h6 class="text-muted">TOTAL ALAT</h6><div class="stat-number"><?php echo $stats['total']; ?></div></div></div></div>
            <div class="col-lg-3 col-md-6"><div class="card stat-card shadow-sm border-warning"><div class="card-body"><h6 class="text-muted">DIPINJAM</h6><div class="stat-number"><?php echo $stats['dipinjam']; ?></div></div></div></div>
            <div class="col-lg-3 col-md-6"><div class="card stat-card shadow-sm border-success"><div class="card-body"><h6 class="text-muted">TERSEDIA</h6><div class="stat-number"><?php echo $stats['tersedia']; ?></div></div></div></div>
            <div class="col-lg-3 col-md-6"><div class="card stat-card shadow-sm border-danger"><div class="card-body"><h6 class="text-muted">PERAWATAN</h6><div class="stat-number"><?php echo $stats['perawatan']; ?></div></div></div></div>
        </div>

        <div class="row g-4 mt-4">
            <div class="col-lg-8">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-white fw-bold">Tren Peminjaman (7 Hari Terakhir)</div>
                    <div class="card-body"><canvas id="lineChart" style="max-height: 300px;"></canvas></div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-white fw-bold">Proporsi Status Alat</div>
                    <div class="card-body"><canvas id="pieChart" style="max-height: 300px;"></canvas></div>
                </div>
            </div>
        </div>

        <div class="row g-4 mt-4">
            <div class="col-lg-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-white fw-bold"><i class="bi bi-hourglass-split me-2"></i>Pengajuan Pending</div>
                    <div class="card-body p-0">
                        <?php 
                        $pend = $pdo->query("SELECT * FROM view_booking_lengkap WHERE status_booking='Pending' ORDER BY waktu_pinjam ASC LIMIT 5")->fetchAll();
                        if(!$pend): echo "<div class='p-4 text-center text-muted'>Tidak ada pengajuan.</div>"; else: ?>
                        <table class="table table-hover mb-0">
                            <?php foreach($pend as $p): ?>
                            <tr>
                                <td class="p-3"><strong><?php echo htmlspecialchars($p['peminjam']); ?></strong><br><small><?php echo htmlspecialchars($p['tipe_alat_diminta']); ?></small></td>
                                <td class="text-end p-3">
                                    <a href="process.php?action=approve_booking&id=<?php echo $p['booking_id']; ?>" class="btn btn-sm btn-success">Setujui</a>
                                    <a href="process.php?action=reject_booking&id=<?php echo $p['booking_id']; ?>" class="btn btn-sm btn-danger">Tolak</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-warning text-dark fw-bold"><i class="bi bi-alarm-fill me-2"></i>Telat / Kembali Hari Ini</div>
                    <div class="card-body p-0">
                        <?php
                        $today = date('Y-m-d H:i:s');
                        $due = $pdo->prepare("SELECT * FROM view_booking_lengkap WHERE status_booking='Disetujui' AND waktu_kembali <= ? ORDER BY waktu_kembali ASC LIMIT 5");
                        $due->execute([$today]);
                        $dues = $due->fetchAll();
                        if(!$dues): echo "<div class='p-4 text-center text-muted'>Tidak ada alat telat.</div>"; else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach($dues as $d): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div><strong><?php echo htmlspecialchars($d['nama_alat']); ?></strong><br><small><?php echo htmlspecialchars($d['peminjam']); ?></small></div>
                                <div class="text-end text-danger fw-bold"><?php echo date('d M H:i', strtotime($d['waktu_kembali'])); ?></div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark mb-0">Halo, <?php echo htmlspecialchars(explode(' ', $_SESSION['full_name'])[0]); ?>! 👋</h2>
                <p class="text-muted">Selamat datang kembali di LabAlat Dashboard.</p>
            </div>
            <a href="alat.php" class="btn btn-primary shadow-sm"><i class="bi bi-plus-lg me-2"></i>Pinjam Alat Baru</a>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100" style="border-left: 5px solid #ffc107;">
                    <div class="card-body d-flex align-items-center">
                        <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                            <i class="bi bi-box-seam text-warning fs-3"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0">Sedang Dipinjam</h6>
                            <h2 class="mb-0 fw-bold"><?php echo $u_stats['aktif']; ?></h2>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100" style="border-left: 5px solid #0dcaf0;">
                    <div class="card-body d-flex align-items-center">
                        <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                            <i class="bi bi-hourglass-split text-info fs-3"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0">Menunggu Persetujuan</h6>
                            <h2 class="mb-0 fw-bold"><?php echo $u_stats['pending']; ?></h2>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100" style="border-left: 5px solid #198754;">
                    <div class="card-body d-flex align-items-center">
                        <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                            <i class="bi bi-check-circle text-success fs-3"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0">Riwayat Selesai</h6>
                            <h2 class="mb-0 fw-bold"><?php echo $u_stats['riwayat']; ?></h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-box-arrow-right me-2 text-primary"></i>Alat yang Sedang Anda Pinjam</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if(empty($active_loans)): ?>
                            <div class="text-center p-5">
                                <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" alt="Empty" style="width: 80px; opacity: 0.5;" class="mb-3">
                                <p class="text-muted">Tidak ada alat yang sedang dipinjam.</p>
                                <a href="alat.php" class="btn btn-outline-primary btn-sm">Lihat Katalog</a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-4">Alat</th>
                                            <th>Kode</th>
                                            <th>Tenggat Waktu</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($active_loans as $loan): 
                                            // Hitung sisa waktu
                                            $due = strtotime($loan['waktu_kembali']);
                                            $now = time();
                                            $is_late = $now > $due;
                                            $class = $is_late ? 'text-danger fw-bold' : 'text-dark';
                                        ?>
                                        <tr>
                                            <td class="ps-4 fw-bold"><?php echo htmlspecialchars($loan['nama_alat']); ?></td>
                                            <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($loan['kode_alat']); ?></span></td>
                                            <td class="<?php echo $class; ?>">
                                                <?php echo date('d M Y, H:i', $due); ?>
                                                <?php if($is_late) echo '<br><span class="badge bg-danger">Terlambat</span>'; ?>
                                            </td>
                                            <td>
                                                <a href="process.php?action=return_booking&id=<?php echo $loan['booking_id']; ?>" class="btn btn-success btn-sm" onclick="return confirm('Kembalikan alat ini sekarang?')">
                                                    <i class="bi bi-arrow-counterclockwise me-1"></i>Kembalikan
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-info"></i>Status Terkini</h5>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <?php if(empty($recent_history)): ?>
                                <li class="list-group-item text-center text-muted p-4">Belum ada riwayat.</li>
                            <?php else: ?>
                                <?php foreach($recent_history as $hist): 
                                    $st = $hist['status_booking'];
                                    $badge = ($st=='Disetujui')?'success':(($st=='Ditolak')?'danger':(($st=='Pending')?'warning':'secondary'));
                                    $icon = ($st=='Disetujui')?'check-circle':(($st=='Ditolak')?'x-circle':(($st=='Pending')?'hourglass-split':'archive'));
                                ?>
                                <li class="list-group-item p-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <span class="badge bg-<?php echo $badge; ?> mb-1"><i class="bi bi-<?php echo $icon; ?> me-1"></i><?php echo $st; ?></span>
                                            <div class="fw-bold small"><?php echo htmlspecialchars($hist['tipe_alat_diminta']); ?></div>
                                        </div>
                                        <small class="text-muted" style="font-size: 11px;">
                                            <?php echo date('d M', strtotime($hist['waktu_pengajuan'])); ?>
                                        </small>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                        <div class="card-footer bg-white text-center border-0">
                            <a href="booking_saya.php" class="text-decoration-none small fw-bold">Lihat Semua Riwayat &rarr;</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php endif; ?>
</div>

<?php if ($is_admin): ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctxPie = document.getElementById('pieChart');
    if(ctxPie) {
        new Chart(ctxPie, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($pie_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($pie_values); ?>,
                    backgroundColor: ['#198754', '#ffc107', '#dc3545'],
                    borderWidth: 0
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    }

    const ctxLine = document.getElementById('lineChart');
    if(ctxLine) {
        new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($line_labels); ?>,
                datasets: [{
                    label: 'Peminjaman',
                    data: <?php echo json_encode($line_values); ?>,
                    borderColor: '#673ab7', backgroundColor: 'rgba(103, 58, 183, 0.1)', fill: true, tension: 0.4
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }, plugins: { legend: { display: false } } }
        });
    }
});
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>