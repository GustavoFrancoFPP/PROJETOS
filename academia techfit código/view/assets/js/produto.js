// produto.js - Funcionalidades da página de produtos


// Adicionar esta função ao produto.js existente
function configurarBotoesCarrinho() {
    const addToCartButtons = document.querySelectorAll('.btn-add-to-cart');
    
    addToCartButtons.forEach(button => {
        button.addEventListener('click', async function() {
            const productCard = this.closest('.product-card');
            const product = {
                action: 'adicionar_produto',
                id: productCard.dataset.id,
                quantidade: 1
            };
            
            // Desabilita o botão temporariamente
            this.disabled = true;
            const textoOriginal = this.textContent;
            this.textContent = 'Adicionando...';
            
            try {
                // Envia via AJAX para o CarrinhoController
                const response = await fetch('CarrinhoController.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(product)
                });
                
                const result = await response.json();
                
                if (result.sucesso) {
                    // Efeito visual de sucesso
                    this.textContent = '✓ Adicionado!';
                    this.style.background = '#4CAF50';
                    
                    // Mostra notificação
                    mostrarNotificacao(result.mensagem, 'success');
                    
                    // Atualiza contador do carrinho se existir
                    atualizarContadorCarrinho();
                    
                    setTimeout(() => {
                        this.textContent = textoOriginal;
                        this.style.background = '';
                        this.disabled = false;
                    }, 2000);
                } else {
                    throw new Error(result.mensagem || 'Erro ao adicionar produto');
                }
            } catch (error) {
                console.error('Erro:', error);
                mostrarNotificacao(error.message, 'error');
                this.textContent = textoOriginal;
                this.disabled = false;
            }
        });
    });
}

// Função para mostrar notificação
function mostrarNotificacao(mensagem, tipo = 'success') {
    const notification = document.createElement('div');
    notification.textContent = mensagem;
    notification.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        background: ${tipo === 'success' ? '#4CAF50' : '#f44336'};
        color: white;
        padding: 15px 20px;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        z-index: 10000;
        animation: slideIn 0.3s ease;
    `;
    document.body.appendChild(notification);
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Função para atualizar contador do carrinho
async function atualizarContadorCarrinho() {
    try {
        const response = await fetch('CarrinhoController.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ action: 'obter_carrinho' })
        });
        
        const result = await response.json();
        
        if (result.sucesso && result.carrinho) {
            const totalItens = result.carrinho.itens.length;
            const badge = document.querySelector('.cart-badge');
            if (badge) {
                badge.textContent = totalItens;
                badge.style.display = totalItens > 0 ? 'block' : 'none';
            }
        }
    } catch (error) {
        console.error('Erro ao atualizar contador:', error);
    }
}

// Chamar a função quando a página carregar
document.addEventListener('DOMContentLoaded', function() {
    configurarBotoesCarrinho();
    
    // Atualizar contador do carrinho ao carregar
    atualizarContadorCarrinho();
});
document.addEventListener('DOMContentLoaded', function() {
    // Header e navegação (mantenha o código existente)
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

    // Funcionalidades dos produtos
    const addToCartButtons = document.querySelectorAll('.btn-add-to-cart');
    let cartCount = 0;

    // Adicionar ao carrinho
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productCard = this.closest('.product-card');
            const productName = productCard.querySelector('.product-name').textContent;
            const productPrice = productCard.querySelector('.product-price').textContent;
            
            cartCount++;
            updateCartCounter();
            
            // Efeito visual
            this.textContent = '✓ Adicionado!';
            this.style.background = '#4CAF50';
            
            setTimeout(() => {
                this.textContent = 'Adicionar ao carrinho';
                this.style.background = '';
            }, 2000);
            
            console.log(`Produto adicionado: ${productName} - ${productPrice}`);
            
            // Aqui você pode integrar com um sistema de carrinho real
            // addToCart(productName, productPrice);
        });
    });

    // Contador do carrinho (poderia ser exibido no header)
    function updateCartCounter() {
        console.log(`Itens no carrinho: ${cartCount}`);
        // Aqui você pode atualizar um ícone de carrinho no header
    }

    // Sistema de filtros (se implementado)
    const filterButtons = document.querySelectorAll('.filter-btn');
    const productCards = document.querySelectorAll('.product-card');

    if (filterButtons.length > 0) {
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active de todos os botões
                filterButtons.forEach(btn => btn.classList.remove('active'));
                // Adiciona active ao botão clicado
                this.classList.add('active');
                
                const filter = this.getAttribute('data-filter');
                
                // Filtra os produtos
                productCards.forEach(card => {
                    if (filter === 'all') {
                        card.style.display = 'block';
                    } else {
                        const category = card.getAttribute('data-category');
                        if (category === filter) {
                            card.style.display = 'block';
                        } else {
                            card.style.display = 'none';
                        }
                    }
                });
            });
        });
    }

    // Sistema de busca (se implementado)
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            productCards.forEach(card => {
                const productName = card.querySelector('.product-name').textContent.toLowerCase();
                const productDescription = card.querySelector('.product-description').textContent.toLowerCase();
                
                if (productName.includes(searchTerm) || productDescription.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }

    // Animação de entrada dos produtos
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

    // Observar produtos para animação
    productCards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });

    // Destacar link ativo
    function setActiveNavLink() {
        const currentPage = window.location.pathname.split('/').pop() || 'produtos.html';
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === currentPage) {
                link.classList.add('active');
            }
        });
    }

    setActiveNavLink();
});