document.addEventListener('DOMContentLoaded', function() {
    try { AOS.init({duration: 800, once: true}); } catch(e){}
    
    // Modal Logic
    const modal = document.getElementById('confirmModal');
    if(modal){
        modal.addEventListener('show.bs.modal', function(e) {
            const btn = e.relatedTarget;
            modal.querySelector('#confirmBody').textContent = btn.getAttribute('data-message');
            const yesBtn = modal.querySelector('#confirmBtn');
            yesBtn.setAttribute('href', btn.getAttribute('data-href'));
            yesBtn.className = 'btn ' + (btn.classList.contains('btn-danger')?'btn-danger':'btn-primary');
        });
    }
    
    // ===== LOGIKA NOTIFIKASI REAL-TIME =====
    function loadNotif() {
        const badge = document.getElementById('notifBadge');
        const list = document.getElementById('notifList');
        if(!badge || !list) return;

        fetch('api/get_notif.php')
        .then(res => res.json())
        .then(data => {
            if(data.count > 0) {
                badge.innerText = data.count;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
            // Hanya update list jika dropdown tertutup (agar tidak ganggu klik user)
            if(!list.classList.contains('show')) {
                list.innerHTML = data.html;
            }
        })
        .catch(e => console.log(e));
    }

    // Load pertama kali
    loadNotif();
    // Cek setiap 3 detik
    setInterval(loadNotif, 3000);
});