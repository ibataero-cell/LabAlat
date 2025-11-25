<?php
$page_title = "Profil Saya";
include 'includes/header.php';

try {
    $stmt = $pdo->prepare("SELECT * FROM tbl_users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<div data-aos="fade-up">
    <div class="row justify-content-center">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm border-0">
                <div class="card-body p-5">
                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="h3 fw-bold text-dark mb-0">Edit Profil</h2>
                        <span class="badge bg-primary rounded-pill px-3 py-2"><?php echo ucfirst($user['role']); ?></span>
                    </div>

                    <form action="process.php?action=update_profile" method="POST" enctype="multipart/form-data">
                        
                        <div class="text-center mb-5">
                            <div class="position-relative d-inline-block">
                                <img src="<?php echo UPLOAD_URL . ($user['foto_user'] ? $user['foto_user'] : 'default_user.png'); ?>" 
                                     alt="Foto Profil" 
                                     class="rounded-circle shadow-sm"
                                     style="width: 150px; height: 150px; object-fit: cover; border: 4px solid #fff;"
                                     id="previewFoto">
                                
                                <label for="foto_user" class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle p-2 shadow-sm" style="cursor: pointer; transform: translate(10%, 10%);">
                                    <i class="bi bi-camera-fill fs-5"></i>
                                </label>
                                <input type="file" id="foto_user" name="foto_user" class="d-none" accept="image/*" onchange="previewImage(event)">
                                <input type="hidden" name="old_photo" value="<?php echo $user['foto_user']; ?>">
                            </div>
                            <div class="mt-2 text-muted small">Klik ikon kamera untuk mengganti foto</div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Nama Lengkap</label>
                                <input type="text" class="form-control py-2" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email</label>
                                <input type="email" class="form-control py-2" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Username</label>
                                <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                                <div class="form-text">Username tidak dapat diubah.</div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h5 class="fw-bold mb-3"><i class="bi bi-shield-lock me-2"></i>Ganti Password</h5>
                        <div class="alert alert-light border mb-3 small text-muted">
                            Kosongkan bagian ini jika Anda tidak ingin mengubah password.
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password Saat Ini</label>
                            <input type="password" class="form-control" name="current_password" placeholder="Masukkan password lama untuk verifikasi">
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Password Baru</label>
                                <input type="password" class="form-control" name="new_password" placeholder="Minimal 6 karakter">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" class="form-control" name="confirm_password" placeholder="Ketik ulang password baru">
                            </div>
                        </div>

                        <div class="d-grid mt-5">
                            <button type="submit" class="btn btn-primary btn-lg fw-bold py-3 shadow-sm">Simpan Perubahan</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Script Preview Gambar
function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function() {
        const output = document.getElementById('previewFoto');
        output.src = reader.result;
    };
    if(event.target.files[0]){
        reader.readAsDataURL(event.target.files[0]);
    }
}
</script>

<?php include 'includes/footer.php'; ?>