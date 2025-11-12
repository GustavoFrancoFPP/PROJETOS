document.addEventListener('DOMContentLoaded', function() {
    // ===== CARROSSEL SIMPLES E FUNCIONAL =====
    const carouselSlide = document.querySelector('.carousel-slide');
    const prevBtn = document.querySelector('.prev-btn');
    const nextBtn = document.querySelector('.next-btn');
    const indicators = document.querySelectorAll('.carousel-indicator');
    
    if (carouselSlide) {
        let currentIndex = 0;
        const totalSlides = 3;
        let autoSlideInterval;

        function updateCarousel() {
            // Move o slide para a posição correta
            const translateX = -currentIndex * 100;
            carouselSlide.style.transform = `translateX(${translateX}%)`;
            
            // Atualiza indicadores
            indicators.forEach((indicator, index) => {
                indicator.classList.toggle('active', index === currentIndex);
            });
        }
        
        function nextSlide() {
            currentIndex = (currentIndex + 1) % totalSlides;
            updateCarousel();
        }
        
        function prevSlide() {
            currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
            updateCarousel();
        }
        
        // Event listeners para os botões
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                nextSlide();
                resetAutoSlide();
            });
        }
        
        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                prevSlide();
                resetAutoSlide();
            });
        }
        
        // Event listeners para indicadores
        indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', () => {
                currentIndex = index;
                updateCarousel();
                resetAutoSlide();
            });
        });
        
        // Auto slide
        function startAutoSlide() {
            autoSlideInterval = setInterval(nextSlide, 5000);
        }
        
        function resetAutoSlide() {
            clearInterval(autoSlideInterval);
            startAutoSlide();
        }
        
        // Pausar auto-slide no hover
        const carouselContainer = document.querySelector('.carousel-container');
        if (carouselContainer) {
            carouselContainer.addEventListener('mouseenter', () => {
                clearInterval(autoSlideInterval);
            });
            
            carouselContainer.addEventListener('mouseleave', () => {
                startAutoSlide();
            });
        }
        
        // Suporte a touch para mobile
        let startX = 0;
        let endX = 0;
        
        if (carouselSlide) {
            carouselSlide.addEventListener('touchstart', (e) => {
                startX = e.touches[0].clientX;
                clearInterval(autoSlideInterval);
            });
            
            carouselSlide.addEventListener('touchmove', (e) => {
                endX = e.touches[0].clientX;
            });
            
            carouselSlide.addEventListener('touchend', () => {
                const diff = startX - endX;
                if (Math.abs(diff) > 50) {
                    if (diff > 0) {
                        nextSlide();
                    } else {
                        prevSlide();
                    }
                }
                startAutoSlide();
            });
        }
        
        // Inicialização
        updateCarousel();
        startAutoSlide();
    }

    // ===== HEADER E MENU HAMBÚRGUER =====
    const header = document.querySelector('.techfit-header');
    const hamburger = document.querySelector('.hamburger-menu');
    const navigation = document.querySelector('.main-navigation');
    const navLinks = document.querySelectorAll('.nav-link');

    // Efeito de scroll no header
    if (header) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 100) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    }

    // Menu hambúrguer
    if (hamburger && navigation) {
        hamburger.addEventListener('click', function() {
            hamburger.classList.toggle('active');
            navigation.classList.toggle('active');
            
            // Previne scroll do body quando menu está aberto
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

    // ===== ANIMAÇÕES DE ENTRADA PARA OS CARDS =====
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
    const animatedElements = document.querySelectorAll('.plan-card');
    animatedElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });

    // ===== DESTACAR LINK ATIVO NA NAVEGAÇÃO =====
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

    // ===== EFEITOS HOVER PARA OS CARDS DE PLANOS =====
    const planCards = document.querySelectorAll('.plan-card');
    planCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = this.classList.contains('featured') 
                ? 'scale(1.05) translateY(-8px)' 
                : 'translateY(-8px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = this.classList.contains('featured') 
                ? 'scale(1.05)' 
                : 'translateY(0)';
        });
    });

    // ===== PREVENIR COMPORTAMENTO PADRÃO DOS BOTÕES =====
    const buttons = document.querySelectorAll('.btn-primary');
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            // Aqui você pode adicionar a lógica para cada botão
            console.log('Botão clicado:', this.textContent);
            
            // Exemplo: Adicionar um efeito visual temporário
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
        });
    });

    // ===== INICIALIZAÇÃO DE DEBUG =====
    console.log('TechFit - Página inicial carregada com sucesso!');
    console.log('Carrossel:', carouselSlide ? 'OK' : 'Não encontrado');
    console.log('Cards de planos:', planCards.length);
    console.log('Links de navegação:', navLinks.length);
});

// ===== FUNÇÕES GLOBAIS ADICIONAIS =====
function scrollToSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        section.scrollIntoView({ behavior: 'smooth' });
    }
}

function toggleCardDetails(cardId) {
    const card = document.getElementById(cardId);
    if (card) {
        card.classList.toggle('expanded');
    }
}

// ===== TRATAMENTO DE ERROS =====
window.addEventListener('error', function(e) {
    console.error('Erro na página:', e.error);
});

// ===== OTIMIZAÇÃO DE PERFORMANCE =====
let resizeTimeout;
window.addEventListener('resize', function() {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(function() {
        // Recálculos necessários no redimensionamento
        const carouselSlide = document.querySelector('.carousel-slide');
        if (carouselSlide) {
            carouselSlide.style.transition = 'none';
            setTimeout(() => {
                carouselSlide.style.transition = '';
            }, 50);
        }
    }, 250);
});