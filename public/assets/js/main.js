// Funções para manipulação do modal de usuários
document.addEventListener('DOMContentLoaded', function() {
    // Limpar formulário quando o modal for fechado
    const userModal = document.getElementById('userModal');
    if (userModal) {
        userModal.addEventListener('hidden.bs.modal', function () {
            const form = this.querySelector('form');
            if (form) form.reset();
        });
    }
});