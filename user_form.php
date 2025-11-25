<?php
$page_title = "Form User";
include 'includes/header.php';
require_admin();

$mode = 'add';
$user = [
    'user_id' => '', 'username' => '', 'full_name' => '', 
    'email' => '', 'role' => 'staff'
];

if (isset($_GET['id'])) {
    $mode = 'edit';
    try {
        $stmt = $pdo->prepare("SELECT * FROM tbl_users WHERE user_id = ?");
        $stmt->execute([$_GET['id']]);
        $user = $stmt->fetch();
        if (!$user) {
            $_SESSION['error_msg'] = "User tidak ditemukan.";
            header("Location: user_manage.php");
            exit;
        }
        $page_title = "Edit User";
    } catch (Exception $e) {
        $_SESSION['error_msg'] = "Error: " . $e->getMessage();
    }
} else {
    $page_title = "Tambah User Baru";
}
?>

<div data-aos="fade-up">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?php echo $page_title; ?></h1>
        <a href="user_manage.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>

    <div class="card">
        <div class="card-body p-4">
            <form action="process.php?action=save_user" method="POST">
                <input type="hidden" name="mode" value="<?php echo $mode; ?>">
                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="full_name" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="role" class="form-label">Role (Hak Akses)</label>
                        <select class="form-select" id="role" name="role">
                            <option value="staff" <?php echo ($user['role'] == 'staff') ? 'selected' : ''; ?>>Staff (Peminjam)</option>
                            <option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>>Admin (Pengelola)</option>
                        </select>
                    </div>
                </div>

                <hr class="my-3">
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="<?php echo ($mode == 'edit') ? 'Kosongkan jika tidak ingin mengubah password' : 'Wajib diisi untuk user baru'; ?>" <?php echo ($mode == 'add') ? 'required' : ''; ?>>
                    <?php if($mode == 'edit'): ?>
                        <small class="text-muted">Isi hanya jika Anda ingin mereset password user ini.</small>
                    <?php endif; ?>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-save"></i> Simpan Data User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>