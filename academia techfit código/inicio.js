document.addEventListener('DOMContentLoaded', () => {
    const carouselSlide = document.querySelector('.carousel-slide');
    const carouselImages = document.querySelectorAll('.carousel-slide img');
    const prevBtn = document.querySelector('.prev-btn');
    const nextBtn = document.querySelector('.next-btn');

    // Contador para rastrear o slide atual
    let counter = 0;
    const size = carouselImages[0].clientWidth;

    // Função para mover o slide
    function moveSlide() {
        carouselSlide.style.transition = 'transform 0.5s ease-in-out';
        carouselSlide.style.transform = 'translateX(' + (-size * counter) + 'px)';
    }

    // Botão Próximo
    nextBtn.addEventListener('click', () => {
        if (counter >= carouselImages.length - 1) {
            // Volta para o início se estiver na última imagem
            counter = 0;
        } else {
            counter++;
        }
        moveSlide();
    });

    // Botão Anterior
    prevBtn.addEventListener('click', () => {
        if (counter <= 0) {
            // Vai para o final se estiver na primeira imagem
            counter = carouselImages.length - 1;
        } else {
            counter--;
        }
        moveSlide();
    });

    // Ajusta o tamanho do slide se a janela for redimensionada
    window.addEventListener('resize', () => {
        const newSize = carouselImages[0].clientWidth;
        // Move o slide sem transição para evitar quebras visuais
        carouselSlide.style.transition = 'none';
        carouselSlide.style.transform = 'translateX(' + (-newSize * counter) + 'px)';
    });
});