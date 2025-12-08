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
            const translateX = -currentIndex * 33.333;
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
        indicators.forEach((indicator) => {
            indicator.addEventListener('click', () => {
                currentIndex = parseInt(indicator.getAttribute('data-index'));
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

    // ... resto do seu código JavaScript existente ...
});