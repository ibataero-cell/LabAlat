<?php
require_once 'includes/config.php'; require_once 'includes/functions.php'; require_admin();
header('Content-Type: text/csv'); header('Content-Disposition: attachment; filename="Laporan_Alat.csv"');
$out = fopen('php://output', 'w');
fputcsv($out, ['ID','Kode','Nama','Tipe','Lokasi','Status']);
$rows = $pdo->query("SELECT alat_id,kode_alat,nama_alat,tipe_alat,lokasi,status_ketersediaan FROM tbl_alat");
while($r = $rows->fetch(PDO::FETCH_ASSOC)) fputcsv($out, $r);
fclose($out);
?>