<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page_title = "Katalog Alat";
include 'includes/header.php';

try {
    // AMBIL DATA DIKELOMPOKKAN PER TIPE
    // Kita ambil MIN(alat_id) sebagai 'id_ref' agar bisa diklik untuk detail
    // Kita ambil MIN(foto_alat) agar mendapatkan foto pertama (bukan default) jika ada
    $stmt = $pdo->query("
        SELECT 
            tipe_alat,
            MIN(alat_id) as id_ref,
            MAX(deskripsi) as deskripsi,
            MIN(foto_alat) as foto_alat,
            COUNT(*) AS total_stok,
            SUM(CASE WHEN status_ketersediaan = 'Tersedia' THEN 1 ELSE 0 END) AS stok_tersedia
        FROM 
            tbl_alat
        GROUP BY 
            tipe_alat
        ORDER BY
            tipe_alat ASC
    ");
    $alat_types = $stmt->fetchAll();

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<div data-aos="fade-up">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-dark">Katalog Alat Laboratorium</h1>
    </div>

    <div class="row g-4">
        <?php if (empty($alat_types)): ?>
            <div class="col-12 text-center p-5">
                <h4 class="text-muted">Belum ada data alat.</h4>
            </div>
        <?php else: ?>
            <?php foreach ($alat_types as $tipe): ?>
            
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm border-0">
                    
                    <a href="alat_detail.php?id=<?php echo $tipe['id_ref']; ?>" class="text-decoration-none">
                        <div style="overflow: hidden; border-top-left-radius: 10px; border-top-right-radius: 10px;">
                            <img src="<?php echo UPLOAD_URL . htmlspecialchars($tipe['foto_alat']); ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($tipe['tipe_alat']); ?>" 
                                 style="height: 220px; object-fit: cover; transition: transform 0.3s;"
                                 onerror="this.src='assets/uploads/default_alat.png'">
                        </div>
                    </a>
                    
                    <div class="card-body">
                        <a href="alat_detail.php?id=<?php echo $tipe['id_ref']; ?>" class="text-decoration-none text-dark">
                            <h5 class="card-title fw-bold"><?php echo htmlspecialchars($tipe['tipe_alat']); ?></h5>
                        </a>

                        <?php if($tipe['stok_tersedia'] > 0): ?>
                            <span class="badge bg-success mb-2">Stok Tersedia: <?php echo $tipe['stok_tersedia']; ?> / <?php echo $tipe['total_stok']; ?></span>
                        <?php else: ?>
                            <span class="badge bg-danger mb-2">Stok Habis (0/<?php echo $tipe['total_stok']; ?>)</span>
                        <?php endif; ?>

                        <p class="card-text small text-muted mt-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            <?php echo nl2br(htmlspecialchars($tipe['deskripsi'] ?? '')); ?>
                        </p>
                    </div>
                    
                    <div class="card-footer bg-white border-0 p-3 d-flex gap-2">
                        <a href="alat_detail.php?id=<?php echo $tipe['id_ref']; ?>" class="btn btn-outline-primary w-50">
                            <i class="bi bi-eye"></i> Detail
                        </a>

                        <?php if ($tipe['stok_tersedia'] > 0): ?>
                            <a href="booking_form.php?tipe=<?php echo urlencode($tipe['tipe_alat']); ?>" class="btn btn-primary w-50">
                                <i class="bi bi-calendar-plus"></i> Pinjam
                            </a>
                        <?php else: ?>
                            <button class="btn btn-secondary w-50" disabled>Habis</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<style>
    .card:hover .card-img-top { transform: scale(1.05); }
</style>

<?php include 'includes/footer.php'; ?>