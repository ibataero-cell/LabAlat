<?php
// Matikan output error di layar agar header redirect tidak rusak
ini_set('display_errors', 0);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

// Cek keberadaan file email helper (agar tidak fatal error jika file hilang)
if (file_exists(__DIR__ . '/includes/mail_helper.php')) {
    require_once __DIR__ . '/includes/mail_helper.php';
}

// Validasi Akses
if (!isset($_GET['action'])) {
    header("Location: dashboard.php");
    exit;
}
$action = $_GET['action'];

try {
    // ============================================================
    // 1. LOGIKA AUTH (LOGIN & REGISTER)
    // ============================================================
    
    if ($action == 'login') {
        $stmt = $pdo->prepare("SELECT * FROM tbl_users WHERE username = ?");
        $stmt->execute([$_POST['username']]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($_POST['password'], $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['foto_user'] = $user['foto_user']; // Simpan foto ke session
            
            header("Location: dashboard.php");
        } else {
            header("Location: index.php?error=1");
        }
        exit;
    }

    if ($action == 'register') {
        $full_name = $_POST['full_name'];
        $email = $_POST['email'];
        $username = $_POST['username'];
        $password = $_POST['password'];
        $confirm = $_POST['password_confirm'];

        if ($password !== $confirm) throw new Exception("Password konfirmasi tidak cocok.");
        
        // Cek duplikat
        $check = $pdo->prepare("SELECT user_id FROM tbl_users WHERE username=? OR email=?");
        $check->execute([$username, $email]);
        if ($check->fetch()) throw new Exception("Username atau Email sudah terdaftar.");

        // Simpan user baru (default staff, default foto)
        $sql = "INSERT INTO tbl_users (username, password, full_name, email, role, foto_user) VALUES (?, ?, ?, ?, 'staff', 'default_user.png')";
        $pdo->prepare($sql)->execute([$username, password_hash($password, PASSWORD_DEFAULT), $full_name, $email]);
        
        header("Location: index.php?success=1");
        exit;
    }

    // ============================================================
    // MULAI TRANSAKSI DATABASE UNTUK AKSI LAINNYA
    // ============================================================
    $pdo->beginTransaction();

    switch ($action) {

        // --- A. MANAJEMEN ALAT (Dengan Update Foto Massal) ---
        case 'save_alat':
            require_admin();
            $mode = $_POST['mode'];
            $alat_id = $_POST['alat_id'];
            
            // Data Form
            $kode = $_POST['kode_alat'];
            $nama = $_POST['nama_alat'];
            $tipe_alat = $_POST['tipe_alat'];
            $lokasi = $_POST['lokasi'];
            $deskripsi = $_POST['deskripsi'];
            $status_k = $_POST['status_ketersediaan'];
            $status_s = $_POST['status_sterilisasi'];
            
            $old_photo = $_POST['old_photo'] ?? 'default_alat.png';
            $foto_final = $old_photo;
            $is_new_photo = false;

            // Upload Foto
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
                $file = $_FILES['photo'];
                $allowed = ['image/jpeg', 'image/png', 'image/jpg'];
                if (!in_array($file['type'], $allowed)) throw new Exception("Format file harus JPG/PNG.");
                if ($file['size'] > 2000000) throw new Exception("Ukuran maks 2MB.");

                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $new_name = 'alat_' . time() . '_' . uniqid() . '.' . $ext;

                if (move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $new_name)) {
                    $foto_final = $new_name;
                    $is_new_photo = true;
                    // Hapus foto lama jika bukan default
                    if ($old_photo != 'default_alat.png' && file_exists(UPLOAD_DIR . $old_photo)) {
                        @unlink(UPLOAD_DIR . $old_photo);
                    }
                }
            }

            // Query Insert/Update
            if ($mode == 'add') {
                $sql = "INSERT INTO tbl_alat (kode_alat, nama_alat, tipe_alat, lokasi, deskripsi, status_ketersediaan, status_sterilisasi, foto_alat) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $pdo->prepare($sql)->execute([$kode, $nama, $tipe_alat, $lokasi, $deskripsi, $status_k, $status_s, $foto_final]);
                $_SESSION['success_msg'] = "Alat baru ditambahkan.";
            } else {
                $sql = "UPDATE tbl_alat SET kode_alat=?, nama_alat=?, tipe_alat=?, lokasi=?, deskripsi=?, status_ketersediaan=?, status_sterilisasi=?, foto_alat=? WHERE alat_id=?";
                $pdo->prepare($sql)->execute([$kode, $nama, $tipe_alat, $lokasi, $deskripsi, $status_k, $status_s, $foto_final, $alat_id]);
                $_SESSION['success_msg'] = "Data alat diperbarui.";
            }

            // FITUR MASS UPDATE: Samakan foto & deskripsi untuk tipe yang sama
            if ($is_new_photo) {
                $pdo->prepare("UPDATE tbl_alat SET foto_alat = ? WHERE tipe_alat = ?")->execute([$foto_final, $tipe_alat]);
            }
            if (!empty($deskripsi)) {
                $pdo->prepare("UPDATE tbl_alat SET deskripsi = ? WHERE tipe_alat = ?")->execute([$deskripsi, $tipe_alat]);
            }
            break;

        case 'delete_alat':
            require_admin();
            $pdo->prepare("DELETE FROM tbl_alat WHERE alat_id=?")->execute([$_GET['id']]);
            $_SESSION['success_msg'] = "Alat dihapus.";
            break;

        // --- B. MANAJEMEN BOOKING (Dengan Notif & Email) ---
        case 'request_booking':
            require_login();
            $tipe = $_POST['tipe_alat_diminta'];
            $waktu_pinjam = $_POST['waktu_pinjam'];
            
            $pdo->prepare("INSERT INTO tbl_booking (user_id, tipe_alat_diminta, waktu_pinjam, waktu_kembali, status_booking) VALUES (?,?,?,?,'Pending')")
                ->execute([$_SESSION['user_id'], $tipe, $waktu_pinjam, $_POST['waktu_kembali']]);
            
            // Notifikasi Lonceng Admin
            if (function_exists('create_notification_for_admins')) {
                create_notification_for_admins($pdo, "🔔 Pengajuan: $tipe oleh {$_SESSION['full_name']}", "dashboard.php");
            }
            
            // Email ke Admin
            if (function_exists('kirim_email')) {
                try {
                    $stmt_admin = $pdo->query("SELECT email, full_name FROM tbl_users WHERE role='admin' LIMIT 1");
                    $admin = $stmt_admin->fetch();
                    if($admin && $admin['email']) {
                        $waktu = date('d M Y, H:i', strtotime($_POST['waktu_pinjam']));
                        
                        $msg = "
                        <div style='font-family: Arial, sans-serif; border: 1px solid #ccc; padding: 20px;'>
                            <h3 style='color: #0d6efd;'>🔔 Pengajuan Peminjaman Baru</h3>
                            <p>Halo Admin,</p>
                            <p>User <strong>{$_SESSION['full_name']}</strong> baru saja mengajukan peminjaman.</p>
                            <table border='1' cellpadding='8' cellspacing='0' style='width: 100%; border-collapse: collapse; border-color: #eee;'>
                                <tr><td style='background:#f9f9f9'><strong>Alat</strong></td><td>$tipe</td></tr>
                                <tr><td style='background:#f9f9f9'><strong>Waktu</strong></td><td>$waktu</td></tr>
                            </table>
                            <p style='margin-top: 15px;'>Segera login ke dashboard untuk menyetujui atau menolak.</p>
                        </div>";

                        kirim_email($admin['email'], $admin['full_name'], "[LabAlat] 🔔 Pengajuan Baru: $tipe", $msg);
                    }
                } catch (Exception $e) { }
            }
            $_SESSION['success_msg'] = "Pengajuan terkirim.";
            break;

        case 'approve_booking':
            require_admin();
            $bid = $_GET['id'];
            // Ambil data booking & user
            $b = $pdo->query("SELECT b.*, u.email, u.full_name FROM tbl_booking b JOIN tbl_users u ON b.user_id=u.user_id WHERE booking_id=$bid")->fetch();
            
            // Cek stok dan kunci baris
            $alat = $pdo->query("SELECT alat_id, nama_alat FROM tbl_alat WHERE tipe_alat='{$b['tipe_alat_diminta']}' AND status_ketersediaan='Tersedia' LIMIT 1 FOR UPDATE")->fetch();
            
            if (!$alat) throw new Exception("Stok habis! Tidak bisa menyetujui.");
            
            // Update DB
            $pdo->prepare("UPDATE tbl_booking SET status_booking='Disetujui', alat_terpilih_id=? WHERE booking_id=?")->execute([$alat['alat_id'], $bid]);
            $pdo->prepare("UPDATE tbl_alat SET status_ketersediaan='Dipinjam' WHERE alat_id=?")->execute([$alat['alat_id']]);
            
            // Notifikasi User
            if (function_exists('create_notification')) {
                create_notification($pdo, $b['user_id'], "✅ Disetujui: {$alat['nama_alat']}", "booking_saya.php");
            }
            
            // Email User (HTML Custom)
            if (function_exists('kirim_email') && $b['email']) {
                try {
                    $tanggal_ambil = date('d M Y, H:i', strtotime($b['waktu_pinjam']));
                    $tanggal_kembali = date('d M Y, H:i', strtotime($b['waktu_kembali']));
                    
                    $msg = "
                    <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; border: 1px solid #ddd; padding: 20px; border-radius: 10px;'>
                        <h2 style='color: #198754; border-bottom: 2px solid #198754; padding-bottom: 10px;'>✅ Peminjaman Disetujui</h2>
                        <p>Halo <strong>{$b['full_name']}</strong>,</p>
                        <p>Selamat! Pengajuan peminjaman alat laboratorium Anda telah <strong>DISETUJUI</strong>.</p>
                        
                        <div style='background: #f0fff4; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                            <h3 style='margin-top: 0; color: #198754;'>Detail Peminjaman</h3>
                            <ul style='list-style: none; padding: 0;'>
                                <li>📦 <strong>Alat:</strong> {$alat['nama_alat']}</li>
                                <li>📅 <strong>Ambil:</strong> $tanggal_ambil</li>
                                <li>⏳ <strong>Kembali:</strong> $tanggal_kembali</li>
                            </ul>
                        </div>
                        <p>Silakan datang ke laboratorium untuk mengambil alat tersebut.</p>
                        <p style='font-size: 12px; color: #888;'>Harap kembalikan tepat waktu.</p>
                    </div>";

                    kirim_email($b['email'], $b['full_name'], "[LabAlat] ✅ Disetujui: {$alat['nama_alat']}", $msg);
                } catch (Exception $e) { }
            }
            break;

        case 'reject_booking':
            require_admin();
            $bid = $_GET['id'];
            $b = $pdo->query("SELECT b.*, u.email, u.full_name FROM tbl_booking b JOIN tbl_users u ON b.user_id=u.user_id WHERE booking_id=$bid")->fetch();
            
            $pdo->prepare("UPDATE tbl_booking SET status_booking='Ditolak' WHERE booking_id=?")->execute([$bid]);
            
            // Notif & Email User
            if (function_exists('kirim_email') && $b['email']) {
                try {
                    $msg = "
                    <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; border: 1px solid #ddd; padding: 20px; border-radius: 10px;'>
                        <h2 style='color: #dc3545; border-bottom: 2px solid #dc3545; padding-bottom: 10px;'>❌ Pengajuan Ditolak</h2>
                        <p>Halo <strong>{$b['full_name']}</strong>,</p>
                        <p>Mohon maaf, pengajuan Anda untuk alat <strong>{$b['tipe_alat_diminta']}</strong> saat ini <span style='color:red'>TIDAK DAPAT DISETUJUI</span>.</p>
                        
                        <p><strong>Kemungkinan penyebab:</strong></p>
                        <ul>
                            <li>Stok alat sedang kosong/rusak.</li>
                            <li>Jadwal bentrok dengan peminjam lain.</li>
                            <li>Data pengajuan tidak lengkap.</li>
                        </ul>
                        <p>Silakan coba ajukan di waktu lain.</p>
                    </div>";
                    
                    kirim_email($b['email'], $b['full_name'], "[LabAlat] ❌ Ditolak", $msg);
                } catch (Exception $e) { }
            }
            break;

        case 'return_booking':
            require_login();
            $bid = $_GET['id'];
            $b = $pdo->query("SELECT alat_terpilih_id, tipe_alat_diminta FROM tbl_booking WHERE booking_id=$bid")->fetch();
            
            $pdo->prepare("UPDATE tbl_booking SET status_booking='Selesai' WHERE booking_id=?")->execute([$bid]);
            $pdo->prepare("UPDATE tbl_alat SET status_ketersediaan='Tersedia' WHERE alat_id=?")->execute([$b['alat_terpilih_id']]);
            
            if (function_exists('create_notification_for_admins')) {
                create_notification_for_admins($pdo, "📦 Dikembalikan: {$b['tipe_alat_diminta']} oleh {$_SESSION['full_name']}", "alat_manage.php");
            }
            $_SESSION['success_msg'] = "Alat dikembalikan.";
            break;

        case 'cancel_booking':
            require_login();
            $pdo->prepare("UPDATE tbl_booking SET status_booking='Dibatalkan' WHERE booking_id=? AND user_id=?")->execute([$_GET['id'], $_SESSION['user_id']]);
            $_SESSION['success_msg'] = "Pengajuan dibatalkan.";
            break;

        // --- C. UPDATE PROFIL & USER ---
        case 'update_profile':
            require_login();
            $uid = $_SESSION['user_id'];
            
            // Handle Foto
            $stmt_old = $pdo->prepare("SELECT foto_user FROM tbl_users WHERE user_id=?");
            $stmt_old->execute([$uid]);
            $foto_final = $stmt_old->fetchColumn();

            if (isset($_FILES['foto_user']) && $_FILES['foto_user']['error'] == 0) {
                $ext = pathinfo($_FILES['foto_user']['name'], PATHINFO_EXTENSION);
                $new_name = 'user_' . $uid . '_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['foto_user']['tmp_name'], UPLOAD_DIR . $new_name)) {
                    if ($foto_final != 'default_user.png') @unlink(UPLOAD_DIR . $foto_final);
                    $foto_final = $new_name;
                    $_SESSION['foto_user'] = $new_name; // Update session
                }
            }

            // Update Data
            $pdo->prepare("UPDATE tbl_users SET full_name=?, email=?, foto_user=? WHERE user_id=?")->execute([$_POST['full_name'], $_POST['email'], $foto_final, $uid]);
            $_SESSION['full_name'] = $_POST['full_name'];

            // Update Password
            if (!empty($_POST['new_password'])) {
                $pdo->prepare("UPDATE tbl_users SET password=? WHERE user_id=?")->execute([password_hash($_POST['new_password'], PASSWORD_DEFAULT), $uid]);
            }
            
            $_SESSION['success_msg'] = "Profil diperbarui.";
            header("Location: profile.php"); exit;
            break;
        
        // Admin Manage User
        case 'save_user':
            require_admin();
            $m=$_POST['mode']; $uid=$_POST['user_id']??0; 
            $chk=$pdo->prepare("SELECT user_id FROM tbl_users WHERE (username=? OR email=?) AND user_id!=?"); 
            $chk->execute([$_POST['username'], $_POST['email'], ($m=='edit'?$uid:0)]);
            if($chk->fetch()) throw new Exception("Username/Email sudah ada.");

            if($m=='add'){
                $pdo->prepare("INSERT INTO tbl_users (username,email,full_name,role,password) VALUES (?,?,?,?,?)")
                    ->execute([$_POST['username'],$_POST['email'],$_POST['full_name'],$_POST['role'],password_hash($_POST['password'],PASSWORD_DEFAULT)]);
            } else {
                $sql="UPDATE tbl_users SET username=?,email=?,full_name=?,role=?"; $p=[$_POST['username'],$_POST['email'],$_POST['full_name'],$_POST['role']];
                if(!empty($_POST['password'])){ $sql.=",password=?"; $p[]=password_hash($_POST['password'],PASSWORD_DEFAULT); }
                $sql.=" WHERE user_id=?"; $p[]=$uid;
                $pdo->prepare($sql)->execute($p);
            }
            header("Location: user_manage.php"); exit;
            break;

        case 'delete_user': require_admin(); if($_GET['id']==$_SESSION['user_id'])throw new Exception("Gagal."); $pdo->prepare("DELETE FROM tbl_users WHERE user_id=?")->execute([$_GET['id']]); header("Location: user_manage.php"); exit; break;
        
        // Status Cepat
        case 'update_status_alat': require_admin(); $pdo->prepare("UPDATE tbl_alat SET status_ketersediaan=? WHERE alat_id=?")->execute([$_GET['status'], $_GET['id']]); break;
        case 'update_status_steril': require_admin(); $pdo->prepare("UPDATE tbl_alat SET status_sterilisasi=? WHERE alat_id=?")->execute([$_GET['status'], $_GET['id']]); break;

        default: throw new Exception("Aksi tidak dikenal.");
    }
    
    $pdo->commit();
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'dashboard.php'));

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $_SESSION['error_msg'] = $e->getMessage();
    header("Location: dashboard.php");
}
?>