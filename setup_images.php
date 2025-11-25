<?php
// setup_images.php
// Script untuk generate gambar otomatis

require_once 'includes/config.php';

// Pastikan folder uploads ada
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}

echo "<h1>Mulai Download Gambar Otomatis...</h1>";
echo "<p>Mohon tunggu sebentar, proses ini membutuhkan koneksi internet.</p><hr>";

try {
    // 1. Ambil semua alat dari database
    $stmt = $pdo->query("SELECT alat_id, nama_alat, tipe_alat FROM tbl_alat");
    $alats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $count = 0;

    foreach ($alats as $alat) {
        $id = $alat['alat_id'];
        $nama = $alat['nama_alat'];
        $tipe = $alat['tipe_alat'];

        // 2. Tentukan warna berdasarkan tipe (agar variatif)
        $bg_color = '673ab7'; // Default Ungu
        if(strpos($nama, 'Mikroskop') !== false) $bg_color = '0d6efd'; // Biru
        if(strpos($nama, 'Centrifuge') !== false) $bg_color = '198754'; // Hijau
        if(strpos($nama, 'Timbangan') !== false) $bg_color = 'ffc107'; // Kuning
        if(strpos($nama, 'Autoclave') !== false) $bg_color = 'dc3545'; // Merah
        if(strpos($nama, 'Pipet') !== false) $bg_color = '0dcaf0'; // Cyan

        // 3. Buat URL Gambar (Placeholder dengan Nama Alat)
        // Format: https://placehold.co/600x600/WARNA/FFFFFF?text=Nama+Alat
        $text = urlencode($nama);
        $image_url = "https://placehold.co/800x800/$bg_color/ffffff.jpg?text=$text";

        // 4. Tentukan Nama File Lokal
        // Bersihkan nama file dari karakter aneh
        $clean_name = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $nama));
        $filename = "alat_{$id}_{$clean_name}.jpg";
        $save_path = UPLOAD_DIR . $filename;

        // 5. Download dan Simpan Gambar
        $image_content = file_get_contents($image_url);
        
        if ($image_content !== false) {
            file_put_contents($save_path, $image_content);
            
            // 6. Update Database
            $update = $pdo->prepare("UPDATE tbl_alat SET foto_alat = ? WHERE alat_id = ?");
            $update->execute([$filename, $id]);

            echo "<p style='color:green'>[OK] Berhasil update: <strong>$nama</strong> ($filename)</p>";
            $count++;
        } else {
            echo "<p style='color:red'>[GAGAL] Tidak bisa download gambar untuk: <strong>$nama</strong></p>";
        }
        
        // Beri jeda sedikit agar tidak dianggap spam
        usleep(100000); // 0.1 detik
    }

    echo "<hr><h3>SELESAI! $count gambar berhasil diperbarui.</h3>";
    echo "<a href='alat.php' style='font-size:20px; font-weight:bold;'>&larr; Kembali ke Katalog Alat</a>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>