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

// Funcionalidades do Header Moderno
document.addEventListener('DOMContentLoaded', function() {
    const header = document.querySelector('.techfit-header');
    const hamburger = document.querySelector('.hamburger-menu');
    const navigation = document.querySelector('.main-navigation');
    const navLinks = document.querySelectorAll('.nav-link');

    // Efeito de scroll no header
    window.addEventListener('scroll', function() {
        if (window.scrollY > 100) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });

    // Menu hambúrguer
    hamburger.addEventListener('click', function() {
        hamburger.classList.toggle('active');
        navigation.classList.toggle('active');
        
        // Previne scroll quando menu está aberto
        document.body.style.overflow = navigation.classList.contains('active') ? 'hidden' : '';
    });

    // Fecha menu ao clicar em um link
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            hamburger.classList.remove('active');
            navigation.classList.remove('active');
            document.body.style.overflow = '';
        });
    });

    // Fecha menu ao clicar fora
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.main-navigation') && !event.target.closest('.hamburger-menu')) {
            hamburger.classList.remove('active');
            navigation.classList.remove('active');
            document.body.style.overflow = '';
        }
    });

    // Destacar link ativo baseado na página atual
    function setActiveNavLink() {
        const currentPage = window.location.pathname.split('/').pop() || 'inicio.html';
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === currentPage) {
                link.classList.add('active');
            }
        });
    }

    setActiveNavLink();
});

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