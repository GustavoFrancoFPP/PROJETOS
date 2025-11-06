// header-carrinho-simples.js - Botão do carrinho simples
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Adicionar ícone do carrinho no header
    function adicionarIconeCarrinho() {
        const headerCta = document.querySelector('.header-cta');
        
        if (headerCta) {
            // Criar link do carrinho
            const cartLink = document.createElement('a');
            cartLink.href = 'carrinho.html';
            cartLink.className = 'cart-button';
            cartLink.innerHTML = `
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-count">0</span>
            `;
            
            // Adicionar antes do botão "Área do Aluno"
            headerCta.insertBefore(cartLink, headerCta.firstChild);
            
            console.log('🛒 Botão do carrinho adicionado');
        }
    }

    // 2. Atualizar contador do carrinho
    function atualizarContadorCarrinho() {
        const cartCount = document.querySelector('.cart-count');
        if (!cartCount) return;

        try {
            const carrinho = JSON.parse(localStorage.getItem('carrinhoTechFit')) || [];
            const totalItens = carrinho.reduce((total, item) => total + item.quantidade, 0);
            
            cartCount.textContent = totalItens;
            
            if (totalItens > 0) {
                cartCount.style.display = 'flex';
            } else {
                cartCount.style.display = 'none';
            }
            
        } catch (error) {
            console.error('Erro ao atualizar contador:', error);
        }
    }

    // 3. Configurar menu mobile
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

    // 4. Configurar links ativos
    function configurarLinksAtivos() {
        const currentPage = window.location.pathname.split('/').pop() || 'inicio.html';
        
        document.querySelectorAll('.nav-link').forEach(link => {
            const linkPage = link.getAttribute('href');
            
            link.classList.remove('active');
            
            if (linkPage === currentPage) {
                link.classList.add('active');
            }
        });
    }

    // 5. Efeito de scroll no header
    function configurarScrollHeader() {
        const header = document.querySelector('.techfit-header');
        
        window.addEventListener('scroll', function() {
            if (window.scrollY > 100) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    }

    // 6. Adicionar estilos do carrinho
    function adicionarEstilosCarrinho() {
        const styles = `
            <style>
                /* Botão do Carrinho */
                .cart-button {
                    position: relative;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    width: 45px;
                    height: 45px;
                    border-radius: 50%;
                    background: rgba(255, 255, 255, 0.1);
                    margin-right: 15px;
                    transition: all 0.3s ease;
                    text-decoration: none;
                }
                
                .cart-button:hover {
                    background: rgba(0, 240, 225, 0.2);
                    transform: scale(1.05);
                }
                
                .cart-button i {
                    font-size: 1.3rem;
                    color: var(--nav-text);
                }
                
                /* Contador do Carrinho */
                .cart-count {
                    position: absolute;
                    top: -5px;
                    right: -5px;
                    background: #00f0e1;
                    color: #121212;
                    border-radius: 50%;
                    width: 20px;
                    height: 20px;
                    font-size: 0.7rem;
                    font-weight: 700;
                    display: none;
                    align-items: center;
                    justify-content: center;
                    border: 2px solid #121212;
                }
                
                /* Layout do Header CTA */
                .header-cta {
                    display: flex;
                    align-items: center;
                    gap: 0;
                }
                
                /* Responsivo */
                @media (max-width: 768px) {
                    .cart-button {
                        width: 40px;
                        height: 40px;
                        margin-right: 10px;
                    }
                    
                    .cart-count {
                        width: 18px;
                        height: 18px;
                        font-size: 0.6rem;
                    }
                    
                    .header-cta {
                        gap: 10px;
                    }
                }
            </style>
        `;
        
        document.head.insertAdjacentHTML('beforeend', styles);
    }

    // Inicializar tudo
    function init() {
        adicionarEstilosCarrinho();
        adicionarIconeCarrinho();
        configurarMenuMobile();
        configurarLinksAtivos();
        configurarScrollHeader();
        atualizarContadorCarrinho();
        
        // Atualizar contador periodicamente
        setInterval(atualizarContadorCarrinho, 2000);
        
        // Atualizar quando o carrinho mudar
        window.addEventListener('storage', atualizarContadorCarrinho);
        
        console.log('✅ Header com carrinho configurado');
    }

    // Iniciar
    init();
});