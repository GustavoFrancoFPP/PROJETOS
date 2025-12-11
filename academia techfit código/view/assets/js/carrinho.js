// carrinho.js - Sistema completo de carrinho
class Carrinho {
    constructor() {
        this.itens = JSON.parse(localStorage.getItem('carrinhoTechFit')) || [];
        this.cupomAplicado = null;
        
        // Não inicializar automaticamente se estiver em modo PHP
        if (!window.CARRINHO_PHP_MODE) {
            this.init();
        }
    }

    init() {
        this.configurarHeader();
        this.renderizarCarrinho();
        this.configurarEventos();
        this.atualizarContadorCarrinho();
        console.log('🛒 Carrinho inicializado');
    }

    configurarHeader() {
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

        // Destacar link ativo
        this.destacarLinkAtivo();
    }

    destacarLinkAtivo() {
        const navLinks = document.querySelectorAll('.nav-link');
        const currentPage = window.location.pathname.split('/').pop() || 'carrinho.php';
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === currentPage) {
                link.classList.add('active');
            }
        });
    }

    renderizarCarrinho() {
        const listaItens = document.getElementById('listaItens');
        const carrinhoVazio = document.getElementById('carrinhoVazio');

        // Se estiver em modo PHP (com planos na sessão), não modificar visibilidade
        if (window.CARRINHO_PHP_MODE) {
            // Apenas renderiza produtos do localStorage, não mexe no carrinhoVazio
            if (this.itens.length > 0 && listaItens) {
                listaItens.style.display = 'block';
            }
        } else {
            // Modo normal (apenas localStorage)
            if (this.itens.length === 0) {
                if (listaItens) listaItens.style.display = 'none';
                if (carrinhoVazio) carrinhoVazio.style.display = 'block';
            } else {
                if (listaItens) listaItens.style.display = 'block';
                if (carrinhoVazio) carrinhoVazio.style.display = 'none';
            }
        }
        
        if (this.itens.length > 0) {
            
            if (listaItens) {
                listaItens.innerHTML = this.itens.map(item => `
                    <div class="item-carrinho" data-id="${item.id}">
                        <img src="${item.imagem}" alt="${item.nome}" class="item-imagem" 
                             onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgdmlld0JveD0iMCAwIDEwMCAxMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIiBmaWxsPSIjMkEyQTJBIi8+Cjx0ZXh0IHg9IjUwIiB5PSI1MCIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjE0IiBmaWxsPSIjN0E3QTdBIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iMC4zNWVtIj5TZW0gSW1hZ2VtPC90ZXh0Pgo8L3N2Zz4K'">
                        <div class="item-info">
                            <h4 class="item-nome">${item.nome}</h4>
                            <p class="item-descricao">${item.descricao || 'Produto premium TECHFIT'}</p>
                            <p class="item-preco">R$ ${item.preco.toFixed(2)}</p>
                            <div class="item-controles">
                                <div class="quantidade-controle">
                                    <button class="quantidade-btn diminuir" onclick="carrinho.alterarQuantidade('${item.id}', -1)">-</button>
                                    <span class="quantidade">${item.quantidade}</span>
                                    <button class="quantidade-btn aumentar" onclick="carrinho.alterarQuantidade('${item.id}', 1)">+</button>
                                </div>
                                <button class="remover-item" onclick="carrinho.removerItem('${item.id}')">
                                    <i class="fas fa-trash"></i> Remover
                                </button>
                            </div>
                        </div>
                    </div>
                `).join('');
            }
        }

        this.atualizarResumo();
        this.atualizarContadorCarrinho();
    }

    adicionarItem(produto) {
        const itemExistente = this.itens.find(item => item.id === produto.id);
        
        if (itemExistente) {
            itemExistente.quantidade += 1;
        } else {
            this.itens.push({
                id: produto.id,
                nome: produto.nome,
                descricao: produto.descricao,
                preco: produto.preco,
                imagem: produto.imagem,
                quantidade: 1
            });
        }
        
        this.salvarNoLocalStorage();
        this.renderizarCarrinho();
        this.mostrarNotificacao(`${produto.nome} adicionado ao carrinho!`, 'success');
    }

    removerItem(id) {
        const item = this.itens.find(item => item.id === id);
        if (item) {
            this.itens = this.itens.filter(item => item.id !== id);
            this.salvarNoLocalStorage();
            this.renderizarCarrinho();
            this.mostrarNotificacao(`${item.nome} removido do carrinho`, 'info');
        }
    }

    alterarQuantidade(id, mudanca) {
        const item = this.itens.find(item => item.id === id);
        
        if (item) {
            item.quantidade += mudanca;
            
            if (item.quantidade <= 0) {
                this.removerItem(id);
            } else {
                this.salvarNoLocalStorage();
                this.renderizarCarrinho();
                this.mostrarNotificacao(
                    `Quantidade de ${item.nome} atualizada para ${item.quantidade}`,
                    'info'
                );
            }
        }
    }

    atualizarResumo() {
        const subtotal = this.calcularSubtotal();
        const frete = this.calcularFrete();
        const desconto = this.calcularDesconto();
        const total = Math.max(0, subtotal + frete - desconto);

        // Atualizar elementos do resumo
        const elementos = {
            'subtotal': `R$ ${subtotal.toFixed(2)}`,
            'frete': frete === 0 ? 'Grátis' : `R$ ${frete.toFixed(2)}`,
            'desconto': `- R$ ${desconto.toFixed(2)}`,
            'total': `R$ ${total.toFixed(2)}`
        };

        Object.keys(elementos).forEach(id => {
            const elemento = document.getElementById(id);
            if (elemento) {
                elemento.textContent = elementos[id];
            }
        });

        // Habilitar/desabilitar botão de finalizar
        const btnFinalizar = document.getElementById('btnFinalizar');
        if (btnFinalizar) {
            btnFinalizar.disabled = this.itens.length === 0;
            btnFinalizar.textContent = this.itens.length === 0 ? 
                'Carrinho Vazio' : 
                `Finalizar Compra - R$ ${total.toFixed(2)}`;
        }

        // Mostrar mensagem de frete grátis
        this.atualizarMensagemFrete(subtotal);
    }

    calcularSubtotal() {
        return this.itens.reduce((total, item) => total + (item.preco * item.quantidade), 0);
    }

    calcularFrete() {
        const subtotal = this.calcularSubtotal();
        // Frete grátis para compras acima de R$ 200
        return subtotal > 200 ? 0 : 15.90;
    }

    calcularDesconto() {
        if (!this.cupomAplicado) return 0;
        
        const subtotal = this.calcularSubtotal();
        
        switch(this.cupomAplicado.toUpperCase()) {
            case 'TECHFIT10':
                return subtotal * 0.1; // 10% de desconto
            case 'TECHFIT20':
                return subtotal * 0.2; // 20% de desconto
            case 'FREEGRATIS':
                return this.calcularFrete(); // Frete grátis
            case 'TECHFIT50':
                return Math.min(subtotal * 0.5, 100); // 50% com limite de R$ 100
            default:
                return 0;
        }
    }

    atualizarMensagemFrete(subtotal) {
        const freteInfo = document.getElementById('freteInfo');
        if (!freteInfo) return;

        if (subtotal > 200) {
            freteInfo.innerHTML = '<span style="color: #4CAF50;">🎉 Parabéns! Frete grátis liberado!</span>';
        } else {
            const faltante = (200 - subtotal).toFixed(2);
            freteInfo.innerHTML = `<span style="color: #ff9800;">➕ Adicione R$ ${faltante} para frete grátis!</span>`;
        }
    }

    aplicarCupom(codigo) {
        if (!codigo.trim()) {
            this.mostrarNotificacao('Digite um código de cupom!', 'error');
            return false;
        }

        const cuponsValidos = {
            'TECHFIT10': '10% de desconto',
            'TECHFIT20': '20% de desconto', 
            'FREEGRATIS': 'Frete grátis',
            'TECHFIT50': '50% de desconto (até R$ 100)'
        };

        const cupomUpper = codigo.toUpperCase();
        
        if (cuponsValidos[cupomUpper]) {
            this.cupomAplicado = cupomUpper;
            this.mostrarNotificacao(
                `Cupom aplicado! ${cuponsValidos[cupomUpper]}`,
                'success'
            );
            this.atualizarResumo();
            return true;
        } else {
            this.mostrarNotificacao('Cupom inválido! Tente: TECHFIT10, TECHFIT20, FREEGRATIS', 'error');
            return false;
        }
    }

    removerCupom() {
        if (this.cupomAplicado) {
            this.cupomAplicado = null;
            this.mostrarNotificacao('Cupom removido', 'info');
            this.atualizarResumo();
        }
    }

    configurarEventos() {
        // Cupom de desconto
        const aplicarCupomBtn = document.getElementById('aplicarCupom');
        const inputCupom = document.getElementById('inputCupom');

        if (aplicarCupomBtn && inputCupom) {
            aplicarCupomBtn.addEventListener('click', () => {
                this.aplicarCupom(inputCupom.value);
                inputCupom.value = '';
            });

            inputCupom.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.aplicarCupom(inputCupom.value);
                    inputCupom.value = '';
                }
            });
        }

        // Finalizar compra
        const btnFinalizar = document.getElementById('btnFinalizar');
        if (btnFinalizar) {
            btnFinalizar.addEventListener('click', () => {
                this.finalizarCompra();
            });
        }

        // Continuar comprando
        const btnContinuar = document.querySelector('.btn-primary');
        if (btnContinuar && btnContinuar.href.includes('produtos.html')) {
            btnContinuar.addEventListener('click', (e) => {
                e.preventDefault();
                window.location.href = 'produtos.html';
            });
        }

        // Botão limpar carrinho (se existir)
        const btnLimpar = document.getElementById('btnLimparCarrinho');
        if (btnLimpar) {
            btnLimpar.addEventListener('click', () => {
                this.limparCarrinho();
            });
        }
    }

    finalizarCompra() {
        if (this.itens.length === 0) {
            this.mostrarNotificacao('Adicione produtos ao carrinho primeiro!', 'error');
            return;
        }

        // Validar itens do carrinho
        const itensInvalidos = this.itens.filter(item => 
            !item.id || !item.nome || !item.preco || item.quantidade <= 0
        );

        if (itensInvalidos.length > 0) {
            this.mostrarNotificacao('Alguns itens do carrinho estão inválidos!', 'error');
            return;
        }

        // Salvar dados para a página de pagamento
        const dadosCompra = {
            itens: this.itens,
            subtotal: this.calcularSubtotal(),
            frete: this.calcularFrete(),
            desconto: this.calcularDesconto(),
            total: this.calcularSubtotal() + this.calcularFrete() - this.calcularDesconto(),
            cupom: this.cupomAplicado,
            data: new Date().toISOString(),
            id: 'compra_' + Date.now()
        };

        try {
            localStorage.setItem('dadosCompraTechFit', JSON.stringify(dadosCompra));
            this.mostrarNotificacao('Redirecionando para pagamento...', 'success');
            
            // Simular processamento antes do redirecionamento
            setTimeout(() => {
                window.location.href = 'pagamento.html';
            }, 1500);
            
        } catch (error) {
            console.error('Erro ao salvar dados da compra:', error);
            this.mostrarNotificacao('Erro ao processar compra. Tente novamente.', 'error');
        }
    }

    atualizarContadorCarrinho() {
        // Atualizar contador no header
        const cartCounters = document.querySelectorAll('.cart-count, .cart-counter');
        const totalItens = this.itens.reduce((total, item) => total + item.quantidade, 0);

        cartCounters.forEach(counter => {
            counter.textContent = totalItens;
            counter.style.display = totalItens > 0 ? 'flex' : 'none';
        });

        // Atualizar também no localStorage para outras páginas
        localStorage.setItem('techfit_carrinho_count', totalItens.toString());
    }

    mostrarNotificacao(mensagem, tipo = 'info') {
        // Remover notificações anteriores
        const notificacoesAntigas = document.querySelectorAll('.notification-techfit');
        notificacoesAntigas.forEach(notif => notif.remove());

        // Criar notificação
        const notification = document.createElement('div');
        notification.className = `notification-techfit ${tipo}`;
        
        const icons = {
            'success': 'fa-check-circle',
            'error': 'fa-exclamation-circle', 
            'info': 'fa-info-circle',
            'warning': 'fa-exclamation-triangle'
        };

        notification.innerHTML = `
            <i class="fas ${icons[tipo] || icons['info']}"></i>
            <span>${mensagem}</span>
            <button class="fechar-notificacao">&times;</button>
        `;

        // Estilos da notificação
        notification.style.cssText = `
            position: fixed;
            top: 100px;
            right: 20px;
            background: ${this.getCorNotificacao(tipo)};
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 10000;
            animation: slideInRight 0.3s ease;
            max-width: 400px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            border-left: 4px solid ${this.getCorBordaNotificacao(tipo)};
        `;

        // Botão fechar
        const btnFechar = notification.querySelector('.fechar-notificacao');
        btnFechar.style.cssText = `
            background: none;
            border: none;
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 0;
            margin-left: 10px;
        `;

        btnFechar.addEventListener('click', () => {
            notification.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        });

        document.body.appendChild(notification);

        // Remover automaticamente após 5 segundos
        setTimeout(() => {
            if (notification.parentNode) {
                notification.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }
        }, 5000);
    }

    getCorNotificacao(tipo) {
        const cores = {
            'success': '#4CAF50',
            'error': '#f44336', 
            'info': '#2196F3',
            'warning': '#ff9800'
        };
        return cores[tipo] || cores['info'];
    }

    getCorBordaNotificacao(tipo) {
        const cores = {
            'success': '#2E7D32',
            'error': '#C62828',
            'info': '#1565C0', 
            'warning': '#EF6C00'
        };
        return cores[tipo] || cores['info'];
    }

    salvarNoLocalStorage() {
        try {
            localStorage.setItem('carrinhoTechFit', JSON.stringify(this.itens));
        } catch (error) {
            console.error('Erro ao salvar carrinho:', error);
            this.mostrarNotificacao('Erro ao salvar carrinho. Tente novamente.', 'error');
        }
    }

    limparCarrinho() {
        if (this.itens.length === 0) {
            this.mostrarNotificacao('O carrinho já está vazio!', 'info');
            return;
        }

        if (confirm('Tem certeza que deseja limpar todo o carrinho?')) {
            this.itens = [];
            this.cupomAplicado = null;
            this.salvarNoLocalStorage();
            this.renderizarCarrinho();
            this.mostrarNotificacao('Carrinho limpo com sucesso!', 'success');
        }
    }

    // Métodos utilitários
    getTotalItens() {
        return this.itens.reduce((total, item) => total + item.quantidade, 0);
    }

    getValorTotal() {
        return this.calcularSubtotal() + this.calcularFrete() - this.calcularDesconto();
    }

    temDesconto() {
        return this.cupomAplicado !== null;
    }

    // Debug e informações
    debugInfo() {
        console.log('=== DEBUG CARRINHO ===');
        console.log('Itens:', this.itens);
        console.log('Total itens:', this.getTotalItens());
        console.log('Subtotal:', this.calcularSubtotal());
        console.log('Frete:', this.calcularFrete());
        console.log('Desconto:', this.calcularDesconto());
        console.log('Total:', this.getValorTotal());
        console.log('Cupom:', this.cupomAplicado);
        console.log('=====================');
    }
}

// Inicializar carrinho (apenas se não estiver em modo PHP)
let carrinho;
if (window.CARRINHO_PHP_MODE) {
    // Em modo PHP, apenas cria instância sem inicializar (para uso de métodos)
    carrinho = new Carrinho();
    carrinho.configurarHeader();
    carrinho.configurarEventos();
    console.log('🛒 Carrinho em modo PHP - não renderizando automaticamente');
} else {
    // Modo normal - inicializa completo
    carrinho = new Carrinho();
}

// Adicionar CSS para animações
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    
    .cart-counter {
        position: absolute;
        top: -5px;
        right: -5px;
        background: var(--primary-color);
        color: var(--background-dark);
        border-radius: 50%;
        width: 20px;
        height: 20px;
        font-size: 0.8rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .fechar-notificacao:hover {
        opacity: 0.8;
    }

    /* Estilos para o carrinho vazio */
    .carrinho-vazio {
        text-align: center;
        padding: 60px 20px;
    }

    .carrinho-vazio i {
        font-size: 4rem;
        color: var(--text-muted);
        margin-bottom: 20px;
        opacity: 0.5;
    }

    .carrinho-vazio h3 {
        color: var(--text-muted);
        margin-bottom: 10px;
        font-size: 1.5rem;
    }

    .carrinho-vazio p {
        color: var(--text-muted);
        margin-bottom: 30px;
        font-size: 1rem;
    }
`;
document.head.appendChild(style);

// Exportar para uso global
window.carrinho = carrinho;

// Debug helper (remover em produção)
window.debugCarrinho = () => carrinho.debugInfo();

console.log('🛒 Sistema de carrinho carregado com sucesso!');