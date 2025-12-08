// aulas.js - Funcionalidades para gerenciamento de aulas
document.addEventListener('DOMContentLoaded', function() {
    
    // Modal para nova aula
    const novaAulaBtn = document.getElementById('novaAulaBtn');
    const novaAulaModal = document.getElementById('novaAulaModal');
    const modalClose = document.querySelector('.modal-close');
    const novaAulaForm = document.getElementById('novaAulaForm');

    if (novaAulaBtn && novaAulaModal) {
        // Abrir modal
        novaAulaBtn.addEventListener('click', function(e) {
            e.preventDefault();
            novaAulaModal.classList.add('active');
        });

        // Fechar modal
        modalClose.addEventListener('click', function() {
            novaAulaModal.classList.remove('active');
        });

        // Fechar modal clicando fora
        novaAulaModal.addEventListener('click', function(e) {
            if (e.target === novaAulaModal) {
                novaAulaModal.classList.remove('active');
            }
        });

        // Submeter formulário
        novaAulaForm.addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Aula criada com sucesso!');
            novaAulaModal.classList.remove('active');
            novaAulaForm.reset();
        });
    }

    // Confirmar exclusão de aula
    const deleteButtons = document.querySelectorAll('.btn-danger');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Tem certeza que deseja excluir esta aula?')) {
                // Simulação de exclusão
                const row = this.closest('tr');
                row.style.opacity = '0.5';
                setTimeout(() => {
                    row.remove();
                    alert('Aula excluída com sucesso!');
                }, 500);
            }
        });
    });

    console.log('✅ Módulo de aulas carregado');
});