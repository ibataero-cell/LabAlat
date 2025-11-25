document.addEventListener('DOMContentLoaded', function() {
    
    async function updateRealTime() {
        try {
            // Panggil API dengan timestamp agar tidak kena cache
            const response = await fetch('api/realtime_data.php?t=' + new Date().getTime());
            const data = await response.json();

            if (data.error) return;

            // 1. UPDATE NOTIFIKASI (Lonceng)
            const badge = document.getElementById('notifBadge');
            const list = document.getElementById('notifList');

            if (badge) {
                if (data.notif_count > 0) {
                    badge.innerText = data.notif_count;
                    badge.style.display = 'inline-block';
                } else {
                    badge.style.display = 'none';
                }
            }
            // Update list hanya jika dropdown tertutup agar user tidak terganggu
            if (list && !list.classList.contains('show')) {
                list.innerHTML = data.notif_html;
            }

            // 2. UPDATE DASHBOARD (Angka & Grafik)
            if (data.stats) {
                // Update Angka
                const elTotal = document.getElementById('statTotal');
                if (elTotal) {
                    document.getElementById('statTotal').innerText = data.stats.total;
                    document.getElementById('statPinjam').innerText = data.stats.dipinjam;
                    document.getElementById('statSedia').innerText = data.stats.tersedia;
                    document.getElementById('statRawat').innerText = data.stats.perawatan;
                }

                // Update Grafik Donat (Pie)
                if (window.myPieChart) {
                    // Cek jika data berubah baru update (biar hemat resource)
                    if (JSON.stringify(window.myPieChart.data.datasets[0].data) !== JSON.stringify(data.chart_pie)) {
                        window.myPieChart.data.datasets[0].data = data.chart_pie;
                        window.myPieChart.update();
                    }
                }

                // Update Grafik Garis (Line)
                if (window.myLineChart) {
                    if (JSON.stringify(window.myLineChart.data.datasets[0].data) !== JSON.stringify(data.chart_line)) {
                        window.myLineChart.data.datasets[0].data = data.chart_line;
                        window.myLineChart.update();
                    }
                }
            }

        } catch (error) {
            console.error("Gagal update realtime:", error);
        }
    }

    // Jalankan fungsi pertama kali
    updateRealTime();

    // Ulangi setiap 3 detik (3000 milidetik)
    setInterval(updateRealTime, 3000);

});