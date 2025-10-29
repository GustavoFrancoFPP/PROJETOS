const hamburger = document.querySelector(".hamburger");
const navMenu = document.querySelector(".nav-menu");

// Adiciona o evento de clique ao menu hambúrguer
hamburger.addEventListener("click", () => {
    // Alterna a classe 'active' no hambúrguer e no menu de navegação
    hamburger.classList.toggle("active");
    navMenu.classList.toggle("active");
});

// Fecha o menu ao clicar em um link (opcional, mas boa prática)
document.querySelectorAll(".nav-link").forEach(n => n.addEventListener("click", () => {
    hamburger.classList.remove("active");
    navMenu.classList.remove("active");
}));

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