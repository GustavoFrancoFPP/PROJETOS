// header-admin.js - Header para páginas administrativas
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Configurar menu mobile
    function configurarMenuMobile() {
        const hamburger = document.querySelector('.hamburger-menu');
        const navigation = document.querySelector('.main-navigation');

        if (hamburger && navigation) {
            hamburger.addEventListener('click', function() {
                this.classList.toggle('active');
                navigation.classList.toggle('active');
                
                // Controlar scroll do body
                if (navigation.classList.contains('active')) {
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.overflow = '';
                }
            });

            // Fechar menu ao clicar nos links
            document.querySelectorAll('.nav-link').forEach(link => {
                link.addEventListener('click', () => {
                    hamburger.classList.remove('active');
                    navigation.classList.remove('active');
                    document.body.style.overflow = '';
                });
            });

            // Fechar menu ao clicar fora
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.main-navigation') && !e.target.closest('.hamburger-menu')) {
                    hamburger.classList.remove('active');
                    navigation.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        }
    }

    // 2. Configurar links ativos
    function configurarLinksAtivos() {
        const currentPage = window.location.pathname.split('/').pop() || 'admin.html';
        
        document.querySelectorAll('.nav-link').forEach(link => {
            const linkPage = link.getAttribute('href');
            
            link.classList.remove('active');
            
            if (linkPage === currentPage) {
                link.classList.add('active');
            }
        });
    }

    // 3. Efeito de scroll no header
    function configurarScrollHeader() {
        const header = document.querySelector('.techfit-header');
        
        if (header) {
            window.addEventListener('scroll', function() {
                if (window.scrollY > 100) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            });
        }
    }

    // 4. Botão de logout
    function configurarLogout() {
        const logoutBtn = document.querySelector('.cta-button.logout');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (confirm('Deseja realmente sair do painel administrativo?')) {
                    window.location.href = 'login.html';
                }
            });
        }
    }

    // Inicializar tudo
    function init() {
        configurarMenuMobile();
        configurarLinksAtivos();
        configurarScrollHeader();
        configurarLogout();
        
        console.log('✅ Header administrativo configurado');
    }

    // Iniciar
    init();
});