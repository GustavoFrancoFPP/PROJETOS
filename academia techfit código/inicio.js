document.addEventListener('DOMContentLoaded', function() {
    // ===== CARROSSEL COMPLETAMENTE CORRIGIDO =====
    const carouselSlide = document.querySelector('.carousel-slide');
    const carouselImages = document.querySelectorAll('.carousel-slide img');
    const prevBtn = document.querySelector('.prev-btn');
    const nextBtn = document.querySelector('.next-btn');

    if (carouselSlide && carouselImages.length > 0) {
        let counter = 0;
        const totalImages = carouselImages.length;
        let autoSlideInterval;
        let isTransitioning = false;

        // Configurar largura do slide para cada imagem
        carouselImages.forEach(img => {
            img.style.width = '100%';
            img.style.flexShrink = '0';
        });

        // Configurar largura total do carrossel
        carouselSlide.style.width = `${totalImages * 100}%`;

        // Função para mover o slide
        function moveSlide() {
            if (isTransitioning) return;
            
            isTransitioning = true;
            
            // Calcular a translação baseada no contador
            const translateX = -counter * 100;
            carouselSlide.style.transform = `translateX(${translateX}%)`;
            
            updateIndicators();
            
            // Reset do flag após a transição
            setTimeout(() => {
                isTransitioning = false;
            }, 500);
        }

        // Função para próximo slide
        function nextSlide() {
            if (isTransitioning) return;
            
            counter = (counter + 1) % totalImages;
            moveSlide();
        }

        // Função para slide anterior
        function prevSlide() {
            if (isTransitioning) return;
            
            counter = (counter - 1 + totalImages) % totalImages;
            moveSlide();
        }

        // Botão Próximo
        nextBtn.addEventListener('click', () => {
            nextSlide();
            resetAutoSlide();
        });

        // Botão Anterior
        prevBtn.addEventListener('click', () => {
            prevSlide();
            resetAutoSlide();
        });

        // Criar indicadores
        function createCarouselIndicators() {
            const carouselContainer = document.querySelector('.carousel-container');
            let indicatorsContainer = document.querySelector('.carousel-indicators');
            
            // Remover indicadores existentes se houver
            if (indicatorsContainer) {
                indicatorsContainer.remove();
            }
            
            // Criar novos indicadores
            indicatorsContainer = document.createElement('div');
            indicatorsContainer.className = 'carousel-indicators';
            
            carouselImages.forEach((_, index) => {
                const indicator = document.createElement('button');
                indicator.className = `carousel-indicator ${index === 0 ? 'active' : ''}`;
                indicator.setAttribute('aria-label', `Ir para slide ${index + 1}`);
                indicator.addEventListener('click', () => {
                    if (isTransitioning) return;
                    counter = index;
                    moveSlide();
                    resetAutoSlide();
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

        // Auto-slide
        function startAutoSlide() {
            autoSlideInterval = setInterval(() => {
                if (!isTransitioning) {
                    nextSlide();
                }
            }, 5000);
        }

        function resetAutoSlide() {
            clearInterval(autoSlideInterval);
            startAutoSlide();
        }

        // Pausa auto-slide no hover
        const carouselContainer = document.querySelector('.carousel-container');
        carouselContainer.addEventListener('mouseenter', () => {
            clearInterval(autoSlideInterval);
        });

        carouselContainer.addEventListener('mouseleave', () => {
            startAutoSlide();
        });

        // Suporte a touch para dispositivos móveis
        let startX = 0;
        let endX = 0;

        carouselSlide.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            clearInterval(autoSlideInterval);
        });

        carouselSlide.addEventListener('touchmove', (e) => {
            endX = e.touches[0].clientX;
        });

        carouselSlide.addEventListener('touchend', () => {
            handleSwipe();
            startAutoSlide();
        });

        function handleSwipe() {
            const swipeThreshold = 50;
            const diff = startX - endX;

            if (Math.abs(diff) > swipeThreshold && !isTransitioning) {
                if (diff > 0) {
                    nextSlide(); // Swipe para esquerda
                } else {
                    prevSlide(); // Swipe para direita
                }
            }
        }

        // Preload das imagens para evitar problemas
        function preloadImages() {
            carouselImages.forEach(img => {
                const image = new Image();
                image.src = img.src;
            });
        }

        // Ajuste no redimensionamento da janela
        window.addEventListener('resize', () => {
            // Forçar redesenho do carrossel
            carouselSlide.style.transition = 'none';
            const translateX = -counter * 100;
            carouselSlide.style.transform = `translateX(${translateX}%)`;
            
            // Restaurar transição após um frame
            requestAnimationFrame(() => {
                carouselSlide.style.transition = 'transform 0.5s ease-in-out';
            });
        });

        // Inicialização
        preloadImages();
        createCarouselIndicators();
        moveSlide();
        startAutoSlide();

        // Debug: Log para verificar se está funcionando
        console.log('Carrossel inicializado:', {
            totalImages: totalImages,
            counter: counter,
            slideWidth: carouselSlide.offsetWidth
        });
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
        el.style.transform = 'translateY(30px)';
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

    // ===== CORREÇÃO DO LINK DA PÁGINA DE UNIDADES =====
    const unidadesLink = document.querySelector('a[href="pagina_1.html"]');
    if (unidadesLink) {
        unidadesLink.href = 'pagina_1.html';
    }

    // ===== PRELOADER =====
    window.addEventListener('load', () => {
        setTimeout(() => {
            document.body.classList.add('loaded');
        }, 500);
    });
});

        // Ajuste no redimensionamento da janela
        window.addEventListener('resize', () => {
            // Forçar redesenho do carrossel
            carouselSlide.style.transition = 'none';
            const translateX = -counter * 100;
            carouselSlide.style.transform = `translateX(${translateX}%)`;
            
            // Restaurar transição após um frame
            requestAnimationFrame(() => {
                carouselSlide.style.transition = 'transform 0.5s ease-in-out';
            });
        });

        // Inicialização
        preloadImages();
        createCarouselIndicators();
        moveSlide();
        startAutoSlide();

        // Debug: Log para verificar se está funcionando
        console.log('Carrossel inicializado:', {
            totalImages: totalImages,
            counter: counter,
            slideWidth: carouselSlide.offsetWidth
        });


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
        el.style.transform = 'translateY(30px)';
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

    // ===== CORREÇÃO DO LINK DA PÁGINA DE UNIDADES =====
    const unidadesLink = document.querySelector('a[href="pagina_1.html"]');
    if (unidadesLink) {
        unidadesLink.href = 'pagina_1.html';
    }

    // ===== PRELOADER =====
    window.addEventListener('load', () => {
        setTimeout(() => {
            document.body.classList.add('loaded');
        }, 500);
    });