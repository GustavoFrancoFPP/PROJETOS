// --- FUNCIONALIDADE DA PÁGINA DE SUPORTE ---

// Verifica se os elementos da página de suporte existem antes de adicionar os listeners
const searchForm = document.getElementById('search-form');
const faqItems = document.querySelectorAll('.faq-item');

if (searchForm) {
    searchForm.addEventListener('submit', (event) => {
        event.preventDefault(); // Impede o recarregamento da página
        const searchInput = searchForm.querySelector('input');
        const query = searchInput.value.trim();

        if (query) {
            console.log(`Buscando por: "${query}"`);
            // Aqui você adicionaria a lógica para mostrar os resultados da busca
            alert(`Você buscou por: ${query}`);
        }
    });
}

if (faqItems.length > 0) {
    faqItems.forEach(item => {
        item.addEventListener('click', (event) => {
            event.preventDefault();
            const topic = item.querySelector('span').textContent;
            console.log(`Clicou no tópico de FAQ: "${topic}"`);
            // Aqui você adicionaria a lógica para navegar para a página do tópico
            alert(`Você será redirecionado para a página sobre: ${topic}`);
        });
    });
}