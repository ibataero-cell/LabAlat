<?php require_once 'config.php'; require_once 'functions.php'; require_login(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LabAlat</title>
    <base href="<?php echo BASE_URL; ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<nav class="navbar main-navbar-gradient navbar-expand-lg navbar-dark shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="dashboard.php"><i class="bi bi-beaker me-2"></i>LabAlat</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="alat.php">Katalog</a></li>
                <li class="nav-item"><a class="nav-link" href="booking_saya.php">Pinjaman Saya</a></li>
                <?php if ($_SESSION['role'] == 'admin'): ?>
                <li class="nav-item"><a class="nav-link" href="alat_manage.php">Kelola Alat</a></li>
                <li class="nav-item"><a class="nav-link" href="user_manage.php">Kelola User</a></li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav ms-auto align-items-center">
                
                <li class="nav-item dropdown me-3">
                    <a class="nav-link position-relative" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-bell-fill fs-5"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notifBadge" style="display:none">0</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end notification-dropdown" id="notifList">
                        <li><span class="dropdown-item text-muted text-center small">Memuat...</span></li>
                    </ul>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown">
                         <img src="<?php echo UPLOAD_URL . ($_SESSION['foto_user'] ?? 'default_user.png'); ?>" 
                             class="rounded-circle border border-2 border-white me-2"
                             style="width: 32px; height: 32px; object-fit: cover;">
                        <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php">Profil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
<main class="container main-content">
<?php display_alert(); ?>