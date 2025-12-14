// Aguarda o carregamento completo do HTML antes de executar o script
document.addEventListener('DOMContentLoaded', () => {

    /**
     * FUNÇÃO 1: CONTADORES ANIMADOS PARA AS ESTATÍSTICAS
     * Anima um número de um valor inicial a um final.
     */
    function animateValue(element, start, end, duration) {
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            
            // Lógica para formatar como moeda (R$)
            if (element.dataset.format === 'currency') {
                const value = Math.floor(progress * (end - start) + start);
                element.innerHTML = `R$ ${value.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`;
            } 
            // Lógica para formatar como porcentagem
            else if (element.dataset.format === 'percentage') {
                const value = Math.floor(progress * (end - start) + start);
                element.innerHTML = `${value}%`;
            }
            // Lógica para números inteiros
            else {
                element.innerHTML = Math.floor(progress * (end - start) + start);
            }

            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    }

    // Seleciona e anima cada card de estatística
    const statCards = document.querySelectorAll('.stat-card .value');
    statCards.forEach(card => {
        const finalValue = parseFloat(card.textContent.replace('R$', '').replace('%', '').replace('.', '').replace(',', '.'));
        card.dataset.format = card.textContent.includes('R$') ? 'currency' : (card.textContent.includes('%') ? 'percentage' : 'integer');
        animateValue(card, 0, finalValue, 1500); // Anima por 1.5 segundos
    });


    /**
     * FUNÇÃO 2: SIMULAÇÃO DE ACESSO DE ALUNOS EM TEMPO REAL
     * Adiciona um novo aluno à lista a cada 5 segundos.
     */
    const studentAccessList = document.querySelector('.panel:first-child .item-list');
    if (studentAccessList) {
        const sampleStudents = ['Lucas Mendes', 'Juliana Alves', 'Fernando Dias', 'Patricia Rocha', 'Ricardo Neves', 'Sofia Andrade'];
        
        setInterval(() => {
            // Escolhe um nome aleatório
            const randomName = sampleStudents[Math.floor(Math.random() * sampleStudents.length)];
            
            // Pega a hora atual
            const now = new Date();
            const time = now.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });

            // Cria o novo item da lista
            const newEntry = document.createElement('li');
            newEntry.innerHTML = `
                <img src="imagens/avatar-placeholder.png" alt="Avatar" class="avatar">
                <div class="item-info">
                    <span class="name">${randomName}</span>
                </div>
                <span class="time">${time}</span>
            `;

            // Adiciona o novo item no topo da lista com uma animação
            newEntry.style.animation = 'fadeIn 0.5s ease-in-out';
            studentAccessList.prepend(newEntry);

            // Remove o último item se a lista estiver muito grande
            if (studentAccessList.children.length > 4) {
                studentAccessList.lastElementChild.remove();
            }

        }, 5000); // A cada 5 segundos (5000 milissegundos)
    }

    // Adiciona uma pequena animação de fade-in no CSS para a lista
    const style = document.createElement('style');
    style.innerHTML = `@keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }`;
    document.head.appendChild(style);


    /**
     * FUNÇÃO 3: BOTÕES DE AÇÕES RÁPIDAS INTERATIVOS
     * Adiciona um alerta ao clicar nos botões.
     */
    const quickActionButtons = document.querySelectorAll('.action-btn');
    quickActionButtons.forEach(button => {
        button.addEventListener('click', (event) => {
            event.preventDefault(); // Impede o link de navegar
            const actionText = button.querySelector('span').textContent;
            alert(`Ação executada: ${actionText}`);
            console.log(`Botão '${actionText}' foi clicado.`);
        });
    });

});