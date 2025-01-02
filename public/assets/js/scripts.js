// Inicialização do Bootstrap
document.addEventListener('DOMContentLoaded', function() {
    // Inicializa todos os tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Inicializa todos os popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
});

// Validação de Formulários
function validateForm(form) {
    'use strict';
    
    form.classList.add('was-validated');
    return form.checkValidity();
}

// Confirmação de Ações
function confirmAction(message) {
    return confirm(message || 'Tem certeza que deseja realizar esta ação?');
}

// Auto-dismiss para alertas
document.addEventListener('DOMContentLoaded', function() {
    var alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            var closeButton = alert.querySelector('.btn-close');
            if (closeButton) {
                closeButton.click();
            }
        }, 5000);
    });
});

// Validação de senha em tempo real
document.addEventListener('DOMContentLoaded', function() {
    var passwordInput = document.getElementById('password');
    var confirmInput = document.getElementById('confirm_password');
    
    if (passwordInput && confirmInput) {
        function validatePasswords() {
            if (confirmInput.value === passwordInput.value) {
                confirmInput.setCustomValidity('');
            } else {
                confirmInput.setCustomValidity('As senhas não conferem');
            }
        }
        
        passwordInput.addEventListener('change', validatePasswords);
        confirmInput.addEventListener('keyup', validatePasswords);
    }
});
