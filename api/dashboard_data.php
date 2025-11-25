<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
if (!isset($_SESSION['user_id'])) exit;

try {
    // Pie
    $stmt = $pdo->query("SELECT status_ketersediaan as l, COUNT(*) as c FROM tbl_alat GROUP BY status_ketersediaan");
    $raw = $stmt->fetchAll();
    $labels = array_column($raw, 'l'); $data = array_column($raw, 'c');
    $colors = array_map(function($s){ return $s=='Tersedia'?'#198754':($s=='Dipinjam'?'#ffc107':'#dc3545'); }, $labels);
    
    // Line
    $dates = []; $counts = [];
    for($i=6; $i>=0; $i--) {
        $d = date('Y-m-d', strtotime("-$i days"));
        $dates[] = date('d M', strtotime($d));
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tbl_booking WHERE DATE(waktu_pengajuan)=?");
        $stmt->execute([$d]);
        $counts[] = $stmt->fetchColumn();
    }
    
    echo json_encode(['chart_pie'=>['labels'=>$labels, 'data'=>$data, 'colors'=>$colors], 'chart_line'=>['labels'=>$dates, 'data'=>$counts]]);
} catch(Exception $e) { echo json_encode(['error'=>$e->getMessage()]); }
?>