<?php
if (!isset($pdo)) {
    // Gunakan __DIR__ agar path selalu benar relatif terhadap file ini
    require_once __DIR__ . '/config.php';
}

// 1. Cek Login (Wajib Login)
function require_login() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error_msg'] = "Anda harus login untuk mengakses halaman ini.";
        header("Location: " . BASE_URL . "index.php");
        exit;
    }
}

// 2. Cek Admin (Wajib Role Admin)
function require_admin() {
    require_login();
    if ($_SESSION['role'] !== 'admin') {
        $_SESSION['error_msg'] = "Anda tidak memiliki hak akses untuk halaman ini.";
        header("Location: " . BASE_URL . "dashboard.php");
        exit;
    }
}

// 3. Tampilkan Alert (Success/Error)
function display_alert() {
    if (isset($_SESSION['error_msg'])) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert" data-aos="fade-up">
                ' . $_SESSION['error_msg'] . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
        unset($_SESSION['error_msg']);
    }
    if (isset($_SESSION['success_msg'])) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert" data-aos="fade-up">
                ' . $_SESSION['success_msg'] . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
        unset($_SESSION['success_msg']);
    }
}

// 4. Format Waktu (Contoh: "5 menit yang lalu")
function format_time_ago($datetime) {
    try {
        $time = strtotime($datetime);
        $diff = time() - $time;

        if ($diff < 60) return 'baru saja';
        if ($diff < 3600) return floor($diff / 60) . ' menit lalu';
        if ($diff < 86400) return floor($diff / 3600) . ' jam lalu';
        if ($diff < 604800) return floor($diff / 86400) . ' hari lalu';
        return date('d M Y', $time);
    } catch (Exception $e) {
        return 'beberapa waktu lalu';
    }
}

// ===============================================
// ===== FUNGSI NOTIFIKASI DATABASE =====
// ===============================================

// Kirim notifikasi ke SATU user tertentu
function create_notification($pdo, $uid, $msg, $link = '#') {
    try {
        $stmt = $pdo->prepare("INSERT INTO tbl_notifikasi (user_id, message, link) VALUES (?, ?, ?)");
        $stmt->execute([$uid, $msg, $link]);
    } catch (Exception $e) {
        // Silent error: Jangan hentikan aplikasi jika notif gagal
        error_log("Gagal buat notif: " . $e->getMessage());
    }
}

// Kirim notifikasi ke SEMUA admin
function create_notification_for_admins($pdo, $msg, $link = '#') {
    try {
        // Ambil semua ID admin
        $stmt = $pdo->query("SELECT user_id FROM tbl_users WHERE role = 'admin'");
        $admins = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $insert = $pdo->prepare("INSERT INTO tbl_notifikasi (user_id, message, link) VALUES (?, ?, ?)");
        
        foreach ($admins as $admin_id) {
            $insert->execute([$admin_id, $msg, $link]);
        }
    } catch (Exception $e) {
        error_log("Gagal buat notif admin: " . $e->getMessage());
    }
}
?>