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