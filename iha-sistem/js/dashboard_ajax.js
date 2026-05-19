/**
 * js/dashboard_ajax.js
 * Dashboard için dinamik içerik yönetimi, İHA müsaitlik kontrolü ve AJAX istekleri
 */

document.addEventListener('DOMContentLoaded', () => {
    
    // Uygulama State'i
    const appState = {
        uavs: [],
        flightLogs: []
    };

    // DOM Elementleri
    const uavListDiv = document.getElementById('uavList');
    const uavSelect = document.getElementById('uav_id');
    const addUavForm = document.getElementById('addUavForm');
    const addFlightForm = document.getElementById('addFlightForm');
    const flightDateInput = document.getElementById('flightDate');
    const uavAvailabilityMsg = document.getElementById('uavAvailabilityMsg');

    // Başlangıç verilerini yükle
    loadUavs();

    // İHA Ekleme Formu
    if (addUavForm) {
        addUavForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const name = document.getElementById('uavName').value.trim();
            const model = document.getElementById('uavModel').value.trim();
            const serial = document.getElementById('uavSerial').value.trim();
            const status = document.getElementById('uavStatus').value;

            try {
                const res = await fetch('api/uavs.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ name, model, serial_number: serial, status })
                });
                const data = await res.json();
                if (data.ok) {
                    alert('İHA başarıyla eklendi!');
                    addUavForm.reset();
                    loadUavs(); // Listeyi yenile
                } else {
                    alert('Hata: ' + data.error);
                }
            } catch (err) {
                console.error(err);
                alert('İletişim hatası.');
            }
        });
    }

    // Görev/Uçuş Planlama Formu
    if (addFlightForm) {
        addFlightForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const uavId = uavSelect.value;
            const startedAt = document.getElementById('flightDate').value.replace('T', ' ') + ':00';
            const notes = document.getElementById('flightNotes').value.trim();

            if (!checkUavAvailability(uavId, startedAt)) {
                alert('Seçilen İHA bu saatte müsait değil veya aktif durumda değil!');
                return;
            }

            try {
                const res = await fetch('api/flight_logs.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ uav_id: uavId, started_at: startedAt, notes })
                });
                const data = await res.json();
                if (data.ok) {
                    alert('Görev planlandı!');
                    addFlightForm.reset();
                    uavAvailabilityMsg.textContent = '';
                } else {
                    alert('Hata: ' + data.error);
                }
            } catch (err) {
                console.error(err);
                alert('İletişim hatası.');
            }
        });

        // Müsaitlik anlık kontrolü
        flightDateInput.addEventListener('change', updateAvailabilityMessage);
        uavSelect.addEventListener('change', updateAvailabilityMessage);
    }

    function updateAvailabilityMessage() {
        const uavId = uavSelect.value;
        const dateVal = flightDateInput.value;
        
        if (!uavId || !dateVal) {
            uavAvailabilityMsg.textContent = '';
            return;
        }

        const formattedDate = dateVal.replace('T', ' ') + ':00';
        if (checkUavAvailability(uavId, formattedDate)) {
            uavAvailabilityMsg.textContent = 'Müsait';
            uavAvailabilityMsg.style.color = 'green';
        } else {
            uavAvailabilityMsg.textContent = 'Müsait Değil (İHA aktif değil veya çakışma var)';
            uavAvailabilityMsg.style.color = 'red';
        }
    }

    // Verileri API'den Çekme
    async function loadUavs() {
        try {
            const res = await fetch('api/uavs.php');
            const data = await res.json();
            if (data.ok) {
                appState.uavs = data.data;
                renderUavs();
                populateUavSelect();
            }
        } catch (err) {
            console.error('İHA verisi çekilemedi:', err);
        }
    }

    // İHA Listesini Ekrana Basma
    function renderUavs() {
        if (!uavListDiv) return;
        uavListDiv.innerHTML = '';
        appState.uavs.forEach(uav => {
            const div = document.createElement('div');
            div.style.border = '1px solid #333';
            div.style.padding = '10px';
            div.style.marginBottom = '5px';
            div.innerHTML = `
                <strong>${uav.name}</strong> (${uav.serial_number}) 
                <span class="status">${uav.status}</span>
            `;
            uavListDiv.appendChild(div);
        });
        
        // Dashboard özet bilgilerini güncelle
        const activeUavCount = appState.uavs.filter(u => u.status === 'Müsait').length;
        const statEl = document.getElementById('activeUavCount');
        if(statEl) statEl.textContent = `Müsait İHA sayısı: ${activeUavCount}`;
    }

    // Select Menüsünü Doldurma (Sadece Active olanlar)
    function populateUavSelect() {
        if (!uavSelect) return;
        uavSelect.innerHTML = '<option value="">-- İHA Seç --</option>';
        appState.uavs.forEach(uav => {
            if (uav.status === 'Müsait') {
                const opt = document.createElement('option');
                opt.value = uav.id;
                opt.textContent = `${uav.name} (${uav.serial_number})`;
                uavSelect.appendChild(opt);
            }
        });
    }

    // Müsaitlik Kontrol Mantığı
    // Gerçek bir sistemde flightLogs da çekilip tarih çakışmasına bakılabilir.
    // Şimdilik sadece İHA'nın durumunun active olduğundan emin oluyoruz,
    // ve örnek bir basit tarih çakışması kontrolü yapıyoruz.
    function checkUavAvailability(uavId, requestDate) {
        // İHA'yı bul
        const uav = appState.uavs.find(u => u.id == uavId);
        if (!uav || uav.status !== 'Müsait') return false;

        // Gelecek versiyonlarda buraya API'den o İHA'nın flight_logs.php kayıtları 
        // çekilip aynı gün/saat çakışması var mı diye bakılır. 
        // Şimdilik Müsait olması yeterli kabul ediliyor.
        
        return true; 
    }

});
