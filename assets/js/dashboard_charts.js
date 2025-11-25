document.addEventListener('DOMContentLoaded', function() {
    
    // Inisialisasi Chart (Kosong dulu)
    let pieChart, lineChart;
    
    const ctxPie = document.getElementById('alatStatusChart');
    if(ctxPie) {
        pieChart = new Chart(ctxPie, {
            type: 'doughnut',
            data: {
                labels: ['Tersedia', 'Dipinjam', 'Perawatan'],
                datasets: [{ data: [0,0,0], backgroundColor: ['#198754', '#ffc107', '#dc3545'], borderWidth: 0 }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }

    const ctxLine = document.getElementById('loanTrendChart');
    if(ctxLine) {
        // Label 7 hari terakhir
        const labels = [];
        for(let i=6; i>=0; i--) {
            const d = new Date(); d.setDate(d.getDate() - i);
            labels.push(d.toLocaleDateString('id-ID', {day:'numeric', month:'short'}));
        }

        lineChart = new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Peminjaman', data: [0,0,0,0,0,0,0],
                    borderColor: '#673ab7', backgroundColor: 'rgba(103, 58, 183, 0.1)', fill: true, tension: 0.4
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
        });
    }

    // FUNGSI UPDATE REALTIME
    async function updateData() {
        try {
            const res = await fetch('api/dashboard_updates.php?t=' + new Date().getTime());
            const data = await res.json();
            
            if(data.error) return;

            // 1. Update Angka Dashboard
            if(document.getElementById('statTotal')) {
                document.getElementById('statTotal').innerText = data.stats.total;
                document.getElementById('statPinjam').innerText = data.stats.dipinjam;
                document.getElementById('statSedia').innerText = data.stats.tersedia;
                document.getElementById('statRawat').innerText = data.stats.perawatan;
            }

            // 2. Update Grafik (Jika Data Berubah)
            if(pieChart && JSON.stringify(pieChart.data.datasets[0].data) !== JSON.stringify(data.chart_pie)) {
                pieChart.data.datasets[0].data = data.chart_pie;
                pieChart.update();
            }
            if(lineChart && JSON.stringify(lineChart.data.datasets[0].data) !== JSON.stringify(data.chart_line)) {
                lineChart.data.datasets[0].data = data.chart_line;
                lineChart.update();
            }

            // 3. Update Notifikasi Header (Lonceng)
            const badge = document.getElementById('notifBadge');
            const list = document.getElementById('notifList');
            
            if(badge) {
                if(data.notif_count > 0) {
                    badge.innerText = data.notif_count;
                    badge.style.display = 'inline-block';
                } else {
                    badge.style.display = 'none';
                }
            }
            // Update list hanya jika dropdown tertutup
            if(list && !list.classList.contains('show')) {
                list.innerHTML = data.notif_html;
            }

        } catch(e) { console.error(e); }
    }

    // Jalankan
    updateData();
    setInterval(updateData, 3000); // Update setiap 3 detik
});