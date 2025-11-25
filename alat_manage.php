<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page_title = "Kelola Alat";
include 'includes/header.php';
require_admin();

try {
    $stmt = $pdo->query("SELECT * FROM tbl_alat ORDER BY nama_alat");
    $alat_list = $stmt->fetchAll();
?>

<div data-aos="fade-up">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Kelola Alat</h1>
        <a href="alat_form.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Alat Baru
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle table-modern">
            <thead>
                <tr>
                    <th>Foto</th>
                    <th>Kode</th>
                    <th>Nama Alat</th>
                    <th>Tipe Alat</th>
                    <th>Ketersediaan</th>
                    <th>Sterilisasi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($alat_list)): ?>
                    <tr><td colspan="7" class="text-center">Belum ada data alat.</td></tr>
                <?php endif; ?>
            
                <?php foreach ($alat_list as $index => $alat): ?>
                <tr>
                    <td><img src="<?php echo UPLOAD_URL . htmlspecialchars($alat['foto_alat']); ?>" alt="foto" style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;"></td>
                    <td><?php echo htmlspecialchars($alat['kode_alat']); ?></td>
                    <td><?php echo htmlspecialchars($alat['nama_alat']); ?></td>
                    <td><?php echo htmlspecialchars($alat['tipe_alat']); ?></td>
                    <td><?php echo htmlspecialchars($alat['status_ketersediaan']); ?></td>
                    <td><?php echo htmlspecialchars($alat['status_sterilisasi']); ?></td>
                    <td>
                        <a href="alat_form.php?id=<?php echo $alat['alat_id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <a href="#" 
                           data-href="process.php?action=delete_alat&id=<?php echo $alat['alat_id']; ?>" 
                           data-message="Apakah Anda yakin ingin menghapus alat '<?php echo htmlspecialchars($alat['nama_alat']); ?>' secara permanen?"
                           data-btn-text="Ya, Hapus"
                           data-bs-toggle="modal" 
                           data-bs-target="#confirmModal" 
                           class="btn btn-sm btn-danger" 
                           title="Hapus">
                            <i class="bi bi-trash"></i>
                        </a>
                        <div class="btn-group d-inline-block mt-1">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Update Status
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="process.php?action=update_status_alat&id=<?php echo $alat['alat_id']; ?>&status=Tersedia">Set 'Tersedia'</a></li>
                                <li><a class="dropdown-item" href="process.php?action=update_status_alat&id=<?php echo $alat['alat_id']; ?>&status=Perawatan">Set 'Perawatan'</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="process.php?action=update_status_steril&id=<?php echo $alat['alat_id']; ?>&status=Steril">Set 'Steril'</a></li>
                                <li><a class="dropdown-item" href="process.php?action=update_status_steril&id=<?php echo $alat['alat_id']; ?>&status=Non-Steril">Set 'Non-Steril'</a></li>
                            </ul>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
} catch (Throwable $e) {
    echo '<div data-aos="fade-up"><div class="alert alert-danger m-3"><strong>Error Database Fatal:</strong> ' . $e->getMessage() . 
         '<br><small>File: ' . $e->getFile() . ' | Baris: ' . $e->getLine() . '</small></div></div>';
}
include 'includes/footer.php'; 
?>