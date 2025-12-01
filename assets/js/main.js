// assets/js/main.js
// تفعيل التحقق من النماذج
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms)
        .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
})()

// تأثيرات إضافية عند التركيز على الحقول
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('.form-control');
    
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            if (this.value === '') {
                this.parentElement.classList.remove('focused');
            }
        });
    });

    // إظهار/إخفاء كلمة المرور
    const passwordToggle = document.createElement('span');
    passwordToggle.innerHTML = '<i class="fas fa-eye"></i>';
    passwordToggle.className = 'password-toggle';
    passwordToggle.style.cursor = 'pointer';
    passwordToggle.style.marginRight = '10px';
    
    const passwordField = document.getElementById('password');
    if (passwordField) {
        passwordField.parentElement.appendChild(passwordToggle);
        
        passwordToggle.addEventListener('click', function() {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        });
    }
});

// إضافة تأثيرات CSS ديناميكية
const style = document.createElement('style');
style.textContent = `
    .focused .input-group-text {
        border-color: #4361ee;
        background: #4361ee;
        color: white;
    }
    
    .password-toggle {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        z-index: 10;
        color: #6c757d;
    }
`;
document.head.appendChild(style);