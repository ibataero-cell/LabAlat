<?php
require_once 'includes/config.php';
if (isset($_SESSION['user_id'])) {
    header("Location: ". BASE_URL ."dashboard.php");
    exit;
}
$page_title = "Login - Lab Alat";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <base href="<?php echo BASE_URL; ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-container">
    <div id="particles-js"></div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card login-card"> 
                    <div class="card-header">
                        <h3><i class="bi bi-beaker text-primary"></i> LabAlat</h3>
                        <p class="text-muted">Manajemen Alat Laboratorium</p>
                    </div>
                    <div class="card-body p-4">
                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success">Registrasi berhasil! Silakan login.</div>
                        <?php endif; ?>
                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger">Username atau password salah.</div>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['error_msg'])): ?>
                            <div class="alert alert-danger"><?php echo $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?></div>
                        <?php endif; ?>
                        <form action="process.php?action=login" method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>
                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">Login</button>
                            </div>
                        </form>
                        <div class="text-center mt-3 pt-3 border-top">
                            <p class="text-muted small">Belum punya akun?</p>
                            <a href="register.php" class="btn btn-outline-success">
                                <i class="bi bi-person-plus-fill"></i> Daftar di sini
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
    <script>
        particlesJS.load('particles-js', 'assets/js/particles-config.json');
    </script>
</body>
</html>