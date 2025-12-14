// feedbacks.js - Funcionalidades para gerenciamento de feedbacks
document.addEventListener('DOMContentLoaded', function() {
    
    // Sistema de filtros
    const filterBtn = document.querySelector('.action-btn');
    const statusFilters = document.querySelectorAll('.status-badge');

    if (filterBtn) {
        filterBtn.addEventListener('click', function(e) {
            e.preventDefault();
            alert('Filtros aplicados!');
        });
    }

    // Responder feedback
    const replyButtons = document.querySelectorAll('.action-btn');
    replyButtons.forEach(button => {
        if (button.querySelector('.fa-reply')) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const row = this.closest('tr');
                const aluno = row.querySelector('strong').textContent;
                const comentario = row.querySelector('div').textContent;
                
                const resposta = prompt(`Responder ao feedback de ${aluno}:\n\nComentário: ${comentario}\n\nDigite sua resposta:`);
                
                if (resposta) {
                    // Atualizar status para respondido
                    const statusBadge = row.querySelector('.status-badge');
                    statusBadge.textContent = 'Respondido';
                    statusBadge.className = 'status-badge status-completed';
                    
                    alert('Resposta enviada com sucesso!');
                }
            });
        }
    });

    // Marcar feedback como lido
    const feedbackRows = document.querySelectorAll('.data-table tbody tr');
    feedbackRows.forEach(row => {
        row.addEventListener('click', function() {
            this.style.background = 'rgba(0, 209, 178, 0.05)';
        });
    });

    console.log('✅ Módulo de feedbacks carregado');
});