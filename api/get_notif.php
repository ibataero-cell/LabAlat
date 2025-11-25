<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_SESSION['user_id'])) { echo json_encode(['count'=>0]); exit; }
$uid = $_SESSION['user_id'];

try {
    // Ambil 5 notif
    $stmt = $pdo->prepare("SELECT * FROM tbl_notifikasi WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([$uid]);
    $list = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Hitung unread
    $c = $pdo->prepare("SELECT COUNT(*) FROM tbl_notifikasi WHERE user_id = ? AND is_read = 0");
    $c->execute([$uid]);
    $count = $c->fetchColumn();

    $html = '';
    if(empty($list)){
        $html = '<li><span class="dropdown-item text-muted text-center small">Tidak ada notifikasi.</span></li>';
    } else {
        foreach($list as $n){
            $bg = $n['is_read']==0 ? 'bg-light fw-bold' : '';
            $time = format_time_ago($n['created_at']);
            $link = BASE_URL . 'mark_read.php?id=' . $n['notif_id']; // Link lewat mark_read
            
            $html .= '<li><a class="dropdown-item border-bottom '.$bg.'" href="'.$link.'">
                        <div class="text-wrap">'.$n['message'].'</div>
                        <small class="text-muted" style="font-size:10px">'.$time.'</small>
                      </a></li>';
        }
    }
    echo json_encode(['count'=>$count, 'html'=>$html]);
} catch(Exception $e){ echo json_encode(['count'=>0]); }
?>