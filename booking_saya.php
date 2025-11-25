<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page_title = "Peminjaman Saya";
include 'includes/header.php';

try {
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT * FROM view_booking_lengkap WHERE user_id = ? ORDER BY waktu_pinjam DESC");
    $stmt->execute([$user_id]);
    $my_bookings = $stmt->fetchAll();
?>

<div data-aos="fade-up">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Status Peminjaman Saya</h1>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle table-modern">
            <thead>
                <tr>
                    <th>Alat</th>
                    <th>Waktu Pinjam</th>
                    <th>Waktu Kembali (Tenggat)</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($my_bookings)): ?>
                    <tr><td colspan="5" class="text-center">Anda belum pernah melakukan peminjaman.</td></tr>
                <?php else: ?>
                    <?php foreach ($my_bookings as $book): ?>
                    <tr>
                        <td>
                            <?php 
                            if (!empty($book['nama_alat'])) {
                                echo htmlspecialchars($book['nama_alat']) . " (" . htmlspecialchars($book['kode_alat']) . ")";
                            } else {
                                echo htmlspecialchars($book['tipe_alat_diminta']) . " (Menunggu alokasi)";
                            }
                            ?>
                        </td>
                        <td><?php echo date('d M Y, H:i', strtotime($book['waktu_pinjam'])); ?></td>
                        <td><?php echo date('d M Y, H:i', strtotime($book['waktu_kembali'])); ?></td>
                        <td>
                            <?php
                            $status = $book['status_booking'];
                            $bg = 'secondary';
                            if ($status == 'Pending') $bg = 'warning text-dark';
                            if ($status == 'Disetujui') $bg = 'success';
                            if ($status == 'Ditolak') $bg = 'danger';
                            if ($status == 'Selesai') $bg = 'primary'; 
                            if ($status == 'Dibatalkan') $bg = 'secondary';
                            echo "<span class='badge bg-{$bg}'>$status</span>";
                            ?>
                        </td>
                        <td>
                            <?php
                            if ($status == 'Pending'): ?>
                                <a href="#"
                                   data-href="process.php?action=cancel_booking&id=<?php echo $book['booking_id']; ?>"
                                   data-message="Apakah Anda yakin ingin membatalkan pengajuan alat '<?php echo htmlspecialchars($book['tipe_alat_diminta']); ?>'?"
                                   data-btn-text="Ya, Batalkan"
                                   data-bs-toggle="modal"
                                   data-bs-target="#confirmModal"
                                   class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-x-circle"></i> Batalkan
                                </a>
                            <?php elseif ($status == 'Disetujui'): ?>
                                <a href="#"
                                   data-href="process.php?action=return_booking&id=<?php echo $book['booking_id']; ?>"
                                   data-message="Apakah Anda yakin ingin mengembalikan '<?php echo htmlspecialchars($book['nama_alat']); ?>' sekarang?"
                                   data-btn-text="Ya, Kembalikan"
                                   data-bs-toggle="modal"
                                   data-bs-target="#confirmModal"
                                   class="btn btn-sm btn-success">
                                    <i class="bi bi-check2-circle"></i> Kembalikan Alat
                                </a>
                            <?php elseif ($status == 'Selesai'): ?>
                                <span class="text-primary small"><i class="bi bi-check-all"></i> Selesai</span>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
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