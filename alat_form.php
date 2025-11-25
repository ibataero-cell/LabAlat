<?php
$page_title = "Form Alat";
include 'includes/header.php';
require_admin();

$mode = 'add';
$alat = [
    'alat_id' => '', 
    'kode_alat' => '', 
    'nama_alat' => '', 
    'tipe_alat' => '', // Kolom Baru
    'deskripsi' => '', 
    'lokasi' => '', 
    'foto_alat' => 'default_alat.png',
    'status_ketersediaan' => 'Tersedia', 
    'status_sterilisasi' => 'Tidak Perlu'
];

if (isset($_GET['id'])) {
    $mode = 'edit';
    try {
        $stmt = $pdo->prepare("SELECT * FROM tbl_alat WHERE alat_id = ?");
        $stmt->execute([$_GET['id']]);
        $alat = $stmt->fetch();
        if (!$alat) {
            $_SESSION['error_msg'] = "Alat tidak ditemukan.";
            header("Location: alat_manage.php");
            exit;
        }
        $page_title = "Edit Alat";
    } catch (Exception $e) {
        $_SESSION['error_msg'] = "Error: " . $e->getMessage();
    }
} else {
    $page_title = "Tambah Alat Baru";
}
?>

<div data-aos="fade-up">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?php echo $page_title; ?></h1>
        <a href="alat_manage.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>

    <div class="card">
        <div class="card-body p-4">
            <form action="process.php?action=save_alat" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="mode" value="<?php echo $mode; ?>">
                <input type="hidden" name="alat_id" value="<?php echo $alat['alat_id']; ?>">
                <input type="hidden" name="old_photo" value="<?php echo $alat['foto_alat']; ?>">

                <div class="row">
                    <div class="col-md-8">
                        
                        <div class="mb-3">
                            <label for="nama_alat" class="form-label">Nama Alat (Unit Spesifik)</label>
                            <input type="text" class="form-control" id="nama_alat" name="nama_alat" value="<?php echo htmlspecialchars($alat['nama_alat']); ?>" required placeholder="Contoh: Mikroskop Binokuler #1">
                        </div>

                        <div class="mb-3">
                            <label for="tipe_alat" class="form-label">Tipe Alat (Kategori/Grup)</label>
                            <input type="text" class="form-control" id="tipe_alat" name="tipe_alat" value="<?php echo htmlspecialchars($alat['tipe_alat']); ?>" required placeholder="Contoh: Mikroskop Binokuler">
                            <small class="text-muted">Gunakan nama yang SAMA untuk alat sejenis agar stoknya tergabung di Katalog.</small>
                        </div>
                        <div class="mb-3">
                            <label for="kode_alat" class="form-label">Kode Alat</label>
                            <input type="text" class="form-control" id="kode_alat" name="kode_alat" value="<?php echo htmlspecialchars($alat['kode_alat']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="lokasi" class="form-label">Lokasi/Rak</label>
                            <input type="text" class="form-control" id="lokasi" name="lokasi" value="<?php echo htmlspecialchars($alat['lokasi']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?php echo htmlspecialchars($alat['deskripsi']); ?></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="status_ketersediaan" class="form-label">Status Ketersediaan</label>
                                <select class="form-select" id="status_ketersediaan" name="status_ketersediaan">
                                    <option value="Tersedia" <?php echo ($alat['status_ketersediaan'] == 'Tersedia') ? 'selected' : ''; ?>>Tersedia</option>
                                    <option value="Dipinjam" <?php echo ($alat['status_ketersediaan'] == 'Dipinjam') ? 'selected' : ''; ?>>Dipinjam</option>
                                    <option value="Perawatan" <?php echo ($alat['status_ketersediaan'] == 'Perawatan') ? 'selected' : ''; ?>>Perawatan</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="status_sterilisasi" class="form-label">Status Sterilisasi</label>
                                <select class="form-select" id="status_sterilisasi" name="status_sterilisasi">
                                    <option value="Steril" <?php echo ($alat['status_sterilisasi'] == 'Steril') ? 'selected' : ''; ?>>Steril</option>
                                    <option value="Non-Steril" <?php echo ($alat['status_sterilisasi'] == 'Non-Steril') ? 'selected' : ''; ?>>Non-Steril</option>
                                    <option value="Tidak Perlu" <?php echo ($alat['status_sterilisasi'] == 'Tidak Perlu') ? 'selected' : ''; ?>>Tidak Perlu</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <label class="form-label">Foto Alat</label>
                        <div class="mb-2">
                            <img src="<?php echo UPLOAD_URL . htmlspecialchars($alat['foto_alat']); ?>" alt="Foto" id="photo-preview" style="width: 100%; height: auto; object-fit: cover; border: 1px solid #ddd; border-radius: 5px;">
                        </div>
                        <label for="photo" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-upload"></i> Ganti Foto...
                        </label>
                        <input type="file" class="form-control d-none" id="photo" name="photo" accept="image/*">
                        <small class="d-block text-muted mt-2">Maks. 2MB</small>
                    </div>
                </div>

                <hr>
                <div class="text-end">
                    <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-save"></i> Simpan Data Alat</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>