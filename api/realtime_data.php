<?php
// Matikan error display agar JSON tidak rusak
ini_set('display_errors', 0);
header('Content-Type: application/json');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

require_once __DIR__ . '/../includes/config.php';

// Cek Login
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) { echo json_encode(['error' => 'auth']); exit; }

$uid = $_SESSION['user_id'];
$response = [];

try {
    // 1. DATA NOTIFIKASI (Untuk Lonceng)
    $stmt_n = $pdo->prepare("SELECT COUNT(*) FROM tbl_notifikasi WHERE user_id = ? AND is_read = 0");
    $stmt_n->execute([$uid]);
    $response['notif_count'] = $stmt_n->fetchColumn();

    // List Notifikasi Terbaru
    $stmt_list = $pdo->prepare("SELECT * FROM tbl_notifikasi WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
    $stmt_list->execute([$uid]);
    $notifs = $stmt_list->fetchAll(PDO::FETCH_ASSOC);
    
    $html = '';
    if(empty($notifs)) {
        $html = '<li><span class="dropdown-item text-muted text-center small">Tidak ada notifikasi.</span></li>';
    } else {
        foreach($notifs as $n) {
            $bg = $n['is_read'] == 0 ? 'bg-light fw-bold' : '';
            // Hitung waktu
            $time = strtotime($n['created_at']);
            $diff = time() - $time;
            if($diff<60) $ago = 'baru saja';
            elseif($diff<3600) $ago = floor($diff/60).' menit lalu';
            elseif($diff<86400) $ago = floor($diff/3600).' jam lalu';
            else $ago = floor($diff/86400).' hari lalu';
            
            $link = BASE_URL . 'mark_read.php?id=' . $n['notif_id'];
            $html .= '<li><a class="dropdown-item border-bottom '.$bg.'" href="'.$link.'"><div class="text-wrap">'.$n['message'].'</div><small class="text-muted" style="font-size:10px">'.$ago.'</small></a></li>';
        }
    }
    $response['notif_html'] = $html;

    // 2. DATA DASHBOARD (Hanya jika Admin)
    if ($_SESSION['role'] == 'admin') {
        // Angka Statistik
        $stats = $pdo->query("SELECT 
            (SELECT COUNT(*) FROM tbl_alat) as total,
            (SELECT COUNT(*) FROM tbl_alat WHERE status_ketersediaan='Dipinjam') as dipinjam,
            (SELECT COUNT(*) FROM tbl_alat WHERE status_ketersediaan='Tersedia') as tersedia,
            (SELECT COUNT(*) FROM tbl_alat WHERE status_ketersediaan='Perawatan') as perawatan,
            (SELECT COUNT(*) FROM tbl_booking WHERE status_booking='Pending') as pending
        ")->fetch(PDO::FETCH_ASSOC);
        $response['stats'] = $stats;

        // Data Grafik Pie (Donat)
        $pie = $pdo->query("SELECT status_ketersediaan as l, COUNT(*) as c FROM tbl_alat GROUP BY status_ketersediaan")->fetchAll(PDO::FETCH_KEY_PAIR);
        $response['chart_pie'] = [
            $pie['Tersedia'] ?? 0,
            $pie['Dipinjam'] ?? 0,
            $pie['Perawatan'] ?? 0
        ];

        // Data Grafik Line (Tren)
        $line_data = [];
        for($i=6; $i>=0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM tbl_booking WHERE DATE(waktu_pengajuan)=?");
            $stmt->execute([$d]);
            $line_data[] = (int)$stmt->fetchColumn();
        }
        $response['chart_line'] = $line_data;
    }

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>