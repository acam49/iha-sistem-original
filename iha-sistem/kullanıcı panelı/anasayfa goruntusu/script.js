// Basit etkileşimler için
document.addEventListener("DOMContentLoaded", () => {
    const items = document.querySelectorAll('.nav-item');

    items.forEach(item => {
        item.addEventListener('click', () => {
            // Eski aktif olanı kaldır, tıklanana ekle
            document.querySelector('.nav-item.active').classList.remove('active');
            item.classList.add('active');
        });
    });
});