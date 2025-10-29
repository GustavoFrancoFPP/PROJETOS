document.addEventListener('DOMContentLoaded', function() {
    // ===== CARROSSEL =====
    const carouselSlide = document.querySelector('.carousel-slide');
    const carouselImages = document.querySelectorAll('.carousel-slide img');
    const prevBtn = document.querySelector('.prev-btn');
    const nextBtn = document.querySelector('.next-btn');

    if (carouselSlide && carouselImages.length > 0) {
        let counter = 0;
        const totalImages = carouselImages.length;

        // Configura largura do carrossel
        carouselSlide.style.width = `${totalImages * 100}%`;

        // Função para mover o slide
        function moveSlide() {
            const slideWidth = carouselImages[0].clientWidth;
            carouselSlide.style.transition = 'transform 0.5s ease-in-out';
            carouselSlide.style.transform = `translateX(${-slideWidth * counter}px)`;
            updateIndicators();
        }

        // Botão Próximo
        nextBtn.addEventListener('click', () => {
            if (counter >= totalImages - 1) {
                counter = 0;
            } else {
                counter++;
            }
            moveSlide();
        });

        // Botão Anterior
        prevBtn.addEventListener('click', () => {
            if (counter <= 0) {
                counter = totalImages - 1;
            } else {
                counter--;
            }
            moveSlide();
        });

        // Criar indicadores
        createCarouselIndicators();

        // Auto-slide
        let autoSlide = setInterval(() => {
            if (counter >= totalImages - 1) {
                counter = 0;
            } else {
                counter++;
            }
            moveSlide();
        }, 5000);

        // Pausa auto-slide no hover
        carouselSlide.addEventListener('mouseenter', () => {
            clearInterval(autoSlide);
        });

        carouselSlide.addEventListener('mouseleave', () => {
            autoSlide = setInterval(() => {
                if (counter >= totalImages - 1) {
                    counter = 0;
                } else {
                    counter++;
                }
                moveSlide();
            }, 5000);
        });

        // Ajuste no redimensionamento
        window.addEventListener('resize', () => {
            const slideWidth = carouselImages[0].clientWidth;
            carouselSlide.style.transition = 'none';
            carouselSlide.style.transform = `translateX(${-slideWidth * counter}px)`;
        });

        // Função para criar indicadores
        function createCarouselIndicators() {
            const carouselContainer = document.querySelector('.carousel-container');
            const indicatorsContainer = document.createElement('div');
            indicatorsContainer.className = 'carousel-indicators';
            
            carouselImages.forEach((_, index) => {
                const indicator = document.createElement('button');
                indicator.className = `carousel-indicator ${index === 0 ? 'active' : ''}`;
                indicator.addEventListener('click', () => {
                    counter = index;
                    moveSlide();
                });
                indicatorsContainer.appendChild(indicator);
            });
            
            carouselContainer.appendChild(indicatorsContainer);
        }

        // Função para atualizar indicadores
        function updateIndicators() {
            const indicators = document.querySelectorAll('.carousel-indicator');
            indicators.forEach((indicator, index) => {
                indicator.classList.toggle('active', index === counter);
            });
        }

        // Inicializar
        moveSlide();
    }

    // ===== HEADER E MENU HAMBÚRGUER =====
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
    if (hamburger && navigation) {
        hamburger.addEventListener('click', function() {
            hamburger.classList.toggle('active');
            navigation.classList.toggle('active');
            
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
    }

    // ===== ANIMAÇÕES DE ENTRADA =====
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observar elementos para animação
    const animatedElements = document.querySelectorAll('.plan-card, .info-card');
    animatedElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });

    // ===== DESTACAR LINK ATIVO =====
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

    // ===== PRELOADER =====
    window.addEventListener('load', () => {
        setTimeout(() => {
            document.body.classList.add('loaded');
        }, 500);
    });
});