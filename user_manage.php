<?php
$page_title = "Kelola User";
include 'includes/header.php';
require_admin(); // Wajib Admin

// Ambil semua user
try {
    $stmt = $pdo->query("SELECT * FROM tbl_users ORDER BY role ASC, full_name ASC");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
    $users = [];
}
?>

<div data-aos="fade-up">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Kelola User</h1>
        <a href="user_form.php" class="btn btn-primary">
            <i class="bi bi-person-plus-fill"></i> Tambah User Baru
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle table-modern">
            <thead>
                <tr>
                    <th>Nama Lengkap</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="5" class="text-center">Tidak ada data user.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <i class="bi bi-person text-secondary"></i>
                                </div>
                                <strong><?php echo htmlspecialchars($u['full_name']); ?></strong>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($u['username']); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td>
                            <?php if($u['role'] == 'admin'): ?>
                                <span class="badge bg-primary">Admin</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Staff</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="user_form.php?id=<?php echo $u['user_id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            
                            <?php if($u['user_id'] != $_SESSION['user_id']): ?>
                                <a href="#" 
                                   data-href="process.php?action=delete_user&id=<?php echo $u['user_id']; ?>" 
                                   data-message="Yakin ingin menghapus user '<?php echo htmlspecialchars($u['username']); ?>'?"
                                   data-btn-text="Ya, Hapus"
                                   data-bs-toggle="modal" 
                                   data-bs-target="#confirmModal" 
                                   class="btn btn-sm btn-danger" 
                                   title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </a>
                            <?php else: ?>
                                <button class="btn btn-sm btn-secondary" disabled title="Tidak bisa hapus diri sendiri"><i class="bi bi-trash"></i></button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>