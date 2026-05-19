// Dil Değiştirme
function dilDegistir() {
    document.body.classList.toggle('en-mode');
}

// Slider İşlemleri
let suankiSlayt = 0;
const slaytlar = document.querySelectorAll('.slayt-kart');
const toplamSlayt = slaytlar.length;

function slaytGuncelle() {
    slaytlar.forEach((slayt) => slayt.classList.remove('slayt-merkez', 'slayt-sol', 'slayt-sag'));
    const merkezIndex = suankiSlayt;
    const solIndex = (suankiSlayt - 1 + toplamSlayt) % toplamSlayt;
    const sagIndex = (suankiSlayt + 1) % toplamSlayt;

    slaytlar[merkezIndex].classList.add('slayt-merkez');
    slaytlar[solIndex].classList.add('slayt-sol');
    slaytlar[sagIndex].classList.add('slayt-sag');
}

function slaytDegistir(yon) {
    suankiSlayt = (suankiSlayt + yon + toplamSlayt) % toplamSlayt;
    slaytGuncelle();
}

// Animasyon Gözlemcisi Kurulumu
let animasyonGozlemcisi;

function animasyonlariAyarla() {
    if (animasyonGozlemcisi) {
        animasyonGozlemcisi.disconnect();
    }

    const aktifSayfa = document.querySelector('.sayfa.aktif-sayfa');
    if (!aktifSayfa) return;

    // Hem aktif sayfanın içindekileri hem de footer'daki elemanları seç
    const animasyonElemanlari = [
        ...aktifSayfa.querySelectorAll('.anim-eleman'),
        ...document.querySelectorAll('footer .anim-eleman'),
    ];

    // Gözlemci Ayarları
    animasyonGozlemcisi = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('gorunur');
                } else {
                    // Ekranda çıkınca efekti sıfırla (tekrar kaydırınca animasyon oynasın)
                    entry.target.classList.remove('gorunur');
                }
            });
        },
        { threshold: 0.15 }
    );

    // Başlangıç durumunu ayarla ve gözlemlemeye başla
    animasyonElemanlari.forEach((el) => {
        el.classList.remove('gorunur');
        // Kısa bir gecikme ile gözlemciye ekle ki sayfa değişirken hemen tetiklenmesin
        setTimeout(() => animasyonGozlemcisi.observe(el), 50);
    });
}

// Sayfa Değiştirme
function sayfaDegistir(hedefSayfaId) {
    document.querySelectorAll('.sayfa').forEach((s) => s.classList.remove('aktif-sayfa'));
    document.getElementById(hedefSayfaId).classList.add('aktif-sayfa');

    document.querySelectorAll('.nav-links a').forEach((a) => a.classList.remove('aktif'));
    if (hedefSayfaId === 'sayfa-ana') document.getElementById('link-ana').classList.add('aktif');
    if (hedefSayfaId === 'sayfa-hakkimizda') document.getElementById('link-hakkimizda').classList.add('aktif');

    window.scrollTo(0, 0);

    // Sayfa değiştiğinde o sayfanın animasyonlarını yeniden kur
    animasyonlariAyarla();
}

// Sayfa ilk yüklendiğinde animasyonları başlat (+ file:// iken PHP sayfalarına localhost üzerinden git)
window.addEventListener('DOMContentLoaded', function () {
    if (location.protocol === 'file:') {
        const yerelPhpSunucu = 'http://localhost:8000/';
        document.querySelectorAll('a[href$=".php"]').forEach(function (a) {
            const href = a.getAttribute('href');
            if (!href || /:\/\//.test(href)) {
                return;
            }
            a.addEventListener('click', function (e) {
                e.preventDefault();
                window.location.href = yerelPhpSunucu + href.replace(/^\.\//, '');
            });
        });
    }
    animasyonlariAyarla();
});
