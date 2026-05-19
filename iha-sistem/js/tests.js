/**
 * js/tests.js
 * İBP Modül Testleri için manuel / konsol tabanlı test senaryoları
 * Tarayıcı konsolunda `runIhaTests()` çağrılarak çalıştırılabilir.
 */

window.runIhaTests = async function() {
    console.log("=== İHA Sistem Frontend & API Testleri Başlıyor ===");

    // Test 1: İHA Listesi Çekme
    console.log("Test 1: İHA Listesi Çekme API'si (/api/uavs.php GET)");
    try {
        const res1 = await fetch('api/uavs.php');
        if(res1.status === 401) {
            console.error("Test 1 Başarısız: Oturum açık değil. Lütfen önce giriş yapın.");
            return;
        }
        const data1 = await res1.json();
        if (data1.ok && Array.isArray(data1.data)) {
            console.log("✅ Başarılı. Çekilen İHA sayısı:", data1.data.length);
        } else {
            console.error("❌ Başarısız. Beklenen format dönmedi:", data1);
        }
    } catch (e) {
        console.error("❌ Başarısız. Hata:", e);
    }

    // Test 2: Müsaitlik Kontrol Simülasyonu
    console.log("Test 2: Müsaitlik Kontrol Fonksiyonu Simülasyonu");
    // dashboard_ajax.js'de bulunan mantığı burada mockluyoruz
    const mockUavs = [
        { id: 1, name: "TB2", status: "Müsait" },
        { id: 2, name: "Akinci", status: "Bakımda" }
    ];
    
    function checkAvail(uavId) {
        const u = mockUavs.find(x => x.id === uavId);
        return u && u.status === 'Müsait';
    }

    if (checkAvail(1) === true) {
        console.log("✅ TB2 (Müsait) müsaitlik kontrolünü geçti.");
    } else {
        console.error("❌ Beklenen: true, Gelen: false");
    }

    if (checkAvail(2) === false) {
        console.log("✅ Akinci (Bakımda) müsaitlik reddini geçti.");
    } else {
        console.error("❌ Beklenen: false, Gelen: true");
    }

    console.log("=== Testler Tamamlandı ===");
};

console.log("Test modülü yüklendi. Konsoldan runIhaTests() komutunu çalıştırabilirsiniz.");
