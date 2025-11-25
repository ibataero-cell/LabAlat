<?php
$page_title = "Detail Alat";
include 'includes/header.php';

// 1. Cek Parameter URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>window.location='alat.php';</script>";
    exit;
}

$id_ref = $_GET['id'];

try {
    // 2. Cari TIPE ALAT dari ID yang diklik (dan bersihkan spasi dengan TRIM)
    $stmt_find = $pdo->prepare("SELECT TRIM(tipe_alat) as tipe_bersih FROM tbl_alat WHERE alat_id = ?");
    $stmt_find->execute([$id_ref]);
    $ref_data = $stmt_find->fetch(PDO::FETCH_ASSOC);

    if (!$ref_data) {
        echo "<div class='container mt-4'><div class='alert alert-danger'>Alat tidak ditemukan.</div><a href='alat.php' class='btn btn-secondary'>Kembali</a></div>";
        include 'includes/footer.php';
        exit;
    }

    $TIPE_TARGET = $ref_data['tipe_bersih'];

    // 3. Ambil Info Umum (Gambar & Deskripsi)
    $stmt_info = $pdo->prepare("SELECT * FROM tbl_alat WHERE TRIM(tipe_alat) = ? LIMIT 1");
    $stmt_info->execute([$TIPE_TARGET]);
    $info = $stmt_info->fetch(PDO::FETCH_ASSOC);

    // 4. Hitung Statistik (Filter Ketat dengan TRIM)
    $stmt_count = $pdo->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status_ketersediaan = 'Tersedia' THEN 1 ELSE 0 END) as tersedia,
            SUM(CASE WHEN status_ketersediaan = 'Dipinjam' THEN 1 ELSE 0 END) as dipinjam,
            SUM(CASE WHEN status_ketersediaan = 'Perawatan' THEN 1 ELSE 0 END) as perawatan
        FROM tbl_alat 
        WHERE TRIM(tipe_alat) = ?
    ");
    $stmt_count->execute([$TIPE_TARGET]);
    $stats = $stmt_count->fetch(PDO::FETCH_ASSOC);

    // 5. Ambil Daftar Unit Individu (HANYA TIPE INI)
    // Menggunakan TRIM(tipe_alat) = ? untuk mencocokkan string bersih
    $stmt_units = $pdo->prepare("SELECT * FROM tbl_alat WHERE TRIM(tipe_alat) = ? ORDER BY nama_alat ASC");
    $stmt_units->execute([$TIPE_TARGET]);
    $units = $stmt_units->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    echo "<div class='alert alert-danger m-3'>Error Database: " . $e->getMessage() . "</div>";
    exit;
}
?>

<div data-aos="fade-up">
    
    <div class="d-flex justify-content-between align-items-center mb-4 pt-3 border-bottom pb-2">
        <h2 class="text-dark mb-0">Detail: <span class="text-primary"><?php echo htmlspecialchars($TIPE_TARGET); ?></span></h2>
        <a href="alat.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="p-2">
                    <img src="<?php echo UPLOAD_URL . htmlspecialchars($info['foto_alat']); ?>" 
                         class="img-fluid rounded" 
                         alt="Foto Alat" 
                         style="width: 100%; height: 300px; object-fit: cover;"
                         onerror="this.src='assets/uploads/default_alat.png'">
                </div>
                <div class="card-body">
                    <h5 class="fw-bold text-dark">Deskripsi</h5>
                    <p class="text-muted"><?php echo nl2br(htmlspecialchars($info['deskripsi'] ?? 'Tidak ada deskripsi.')); ?></p>
                    
                    <hr>
                    
                    <div class="d-grid gap-2 mt-3">
                        <?php if ($stats['tersedia'] > 0): ?>
                            <a href="booking_form.php?tipe=<?php echo urlencode($TIPE_TARGET); ?>" class="btn btn-primary btn-lg">
                                <i class="bi bi-calendar-plus"></i> Ajukan Peminjaman
                            </a>
                        <?php else: ?>
                            <button class="btn btn-secondary btn-lg" disabled>
                                <i class="bi bi-x-circle"></i> Stok Habis
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            
            <div class="row g-3 mb-4">
                <div class="col-4"><div class="card bg-success text-white text-center p-3 shadow-sm border-0"><h2 class="mb-0"><?php echo $stats['tersedia']; ?></h2><small>Unit Tersedia</small></div></div>
                <div class="col-4"><div class="card bg-warning text-white text-center p-3 shadow-sm border-0"><h2 class="mb-0"><?php echo $stats['dipinjam']; ?></h2><small>Dipinjam</small></div></div>
                <div class="col-4"><div class="card bg-danger text-white text-center p-3 shadow-sm border-0"><h2 class="mb-0"><?php echo $stats['perawatan']; ?></h2><small>Perawatan</small></div></div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 text-dark"><i class="bi bi-list-ul"></i> Daftar Unit Fisik (<?php echo htmlspecialchars($TIPE_TARGET); ?>)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Nama Unit</th>
                                    <th>Kode Aset</th>
                                    <th>Lokasi</th>
                                    <th>Status</th>
                                    <th>Kondisi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($units)): ?>
                                    <tr><td colspan="5" class="text-center p-3 text-muted">Tidak ada unit ditemukan untuk tipe ini.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($units as $unit): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold text-dark"><?php echo htmlspecialchars($unit['nama_alat']); ?></td>
                                        <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($unit['kode_alat']); ?></span></td>
                                        <td><i class="bi bi-geo-alt text-danger"></i> <?php echo htmlspecialchars($unit['lokasi']); ?></td>
                                        <td>
                                            <?php 
                                            if($unit['status_ketersediaan'] == 'Tersedia') echo '<span class="badge bg-success">Tersedia</span>';
                                            elseif($unit['status_ketersediaan'] == 'Dipinjam') echo '<span class="badge bg-warning text-dark">Dipinjam</span>';
                                            else echo '<span class="badge bg-danger">Perawatan</span>';
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                            if($unit['status_sterilisasi'] == 'Steril') echo '<span class="badge bg-info text-dark"><i class="bi bi-stars"></i> Steril</span>';
                                            elseif($unit['status_sterilisasi'] == 'Non-Steril') echo '<span class="badge bg-secondary">Non-Steril</span>';
                                            else echo '<span class="text-muted">-</span>';
                                            ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>