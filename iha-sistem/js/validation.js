/**
 * js/validation.js
 * Form verilerinin sunucuya gitmeden önce kontrol edilmesi (İBP İsterleri - JS Doğrulamaları)
 */

document.addEventListener('DOMContentLoaded', () => {
    
    // Register Form Validasyonları
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            const adInput = document.getElementById('ad');
            const soyadInput = document.getElementById('soyad');
            const epostaInput = document.getElementById('eposta');
            const passwordInput = document.getElementById('password');
            const passwordConfirmInput = document.getElementById('password_confirm');
            
            // Temizle
            clearErrors(this);
            
            // Ad Kontrolü
            const adVal = adInput.value.trim();
            if (adVal.length < 2) {
                showError(adInput, 'Ad en az 2 karakter olmalıdır.');
                isValid = false;
            }
            
            // Soyad Kontrolü
            const soyadVal = soyadInput.value.trim();
            if (soyadVal.length < 2) {
                showError(soyadInput, 'Soyad en az 2 karakter olmalıdır.');
                isValid = false;
            }
            
            // E-posta Kontrolü
            const epostaVal = epostaInput.value.trim();
            if (!epostaVal.includes('@')) {
                showError(epostaInput, 'Geçerli bir e-posta adresi giriniz.');
                isValid = false;
            }
            
            // Şifre Kontrolü
            const passwordVal = passwordInput.value;
            if (passwordVal.length < 8) {
                showError(passwordInput, 'Şifre en az 8 karakter olmalıdır.');
                isValid = false;
            }
            
            // Şifre Eşleşme Kontrolü
            if (passwordVal !== passwordConfirmInput.value) {
                showError(passwordConfirmInput, 'Şifreler eşleşmiyor.');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault(); // Sunucuya gönderilmesini engelle
            }
        });
    }

    // Login Form Validasyonları
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            const epostaInput = document.getElementById('eposta');
            const passwordInput = document.getElementById('password');
            
            clearErrors(this);
            
            if (epostaInput.value.trim() === '') {
                showError(epostaInput, 'E-posta boş bırakılamaz.');
                isValid = false;
            }
            
            if (passwordInput.value === '') {
                showError(passwordInput, 'Şifre boş bırakılamaz.');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    }

    // Ortak Hata Gösterme Fonksiyonları
    function showError(inputElement, message) {
        inputElement.style.borderColor = 'red';
        const errorDiv = document.createElement('div');
        errorDiv.className = 'js-error-msg';
        errorDiv.style.color = 'red';
        errorDiv.style.fontSize = '12px';
        errorDiv.style.marginTop = '4px';
        errorDiv.textContent = message;
        inputElement.parentNode.appendChild(errorDiv);
    }
    
    function clearErrors(form) {
        const errors = form.querySelectorAll('.js-error-msg');
        errors.forEach(err => err.remove());
        const inputs = form.querySelectorAll('input');
        inputs.forEach(input => input.style.borderColor = '');
    }
});
