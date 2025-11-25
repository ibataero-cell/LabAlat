<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page_title = "Form Pengajuan";
include 'includes/header.php';

try {
    if (!isset($_GET['tipe']) || empty($_GET['tipe'])) {
        throw new Exception("Tipe alat tidak valid atau tidak dipilih.");
    }
    $tipe_alat_diminta = $_GET['tipe'];
    $stmt = $pdo->prepare("SELECT foto_alat, deskripsi FROM tbl_alat WHERE tipe_alat = ? LIMIT 1");
    $stmt->execute([$tipe_alat_diminta]);
    $alat_info = $stmt->fetch();
    if (!$alat_info) {
        throw new Exception("Tipe alat tidak ditemukan di database.");
    }
?>

<div data-aos="fade-up">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Form Pengajuan Peminjaman</h1>
        <a href="alat.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>
    <div class="card">
        <div class="card-body p-4">
            <div class="row">
                <div class="col-md-4">
                    <img src="<?php echo UPLOAD_URL . htmlspecialchars($alat_info['foto_alat']); ?>" class="img-fluid rounded" alt="Foto Alat">
                    <h4 class="mt-3"><?php echo htmlspecialchars($tipe_alat_diminta); ?></h4>
                </div>
                <div class="col-md-8">
                    <h4>Detail Peminjaman</h4>
                    <form action="process.php?action=request_booking" method="POST">
                        <input type="hidden" name="tipe_alat_diminta" value="<?php echo htmlspecialchars($tipe_alat_diminta); ?>">
                        <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                        <div class="mb-3">
                            <label for="waktu_pinjam" class="form-label">Waktu Pinjam Diminta</label>
                            <input type="datetime-local" class="form-control" id="waktu_pinjam" name="waktu_pinjam" required>
                        </div>
                        <div class="mb-3">
                            <label for="waktu_kembali" class="form-label">Waktu Kembali Diminta</label>
                            <input type="datetime-local" class="form-control" id="waktu_kembali" name="waktu_kembali" required>
                        </div>
                        <div class="alert alert-warning small">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            Anda meminta 1 unit dari tipe alat ini. Admin akan menentukan unit spesifik yang akan dipinjamkan.
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-send"></i> Kirim Pengajuan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div> 

<?php
} catch (Throwable $e) {
    echo '<div data-aos="fade-up"><div class="alert alert-danger m-3"><strong>Error:</strong> ' . $e->getMessage() . 
         '<br><a href="alat.php" class="btn btn-primary mt-2">Kembali ke Katalog</a></div></div>';
}
include 'includes/footer.php'; 
?>