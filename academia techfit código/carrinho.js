// carrinho.js
class Carrinho {
    constructor() {
        this.itens = JSON.parse(localStorage.getItem('carrinhoTechFit')) || [];
        this.cupomAplicado = null;
        this.init();
    }

    init() {
        this.renderizarCarrinho();
        this.configurarEventos();
        this.atualizarHeader();
    }

    renderizarCarrinho() {
        const listaItens = document.getElementById('listaItens');
        const carrinhoVazio = document.getElementById('carrinhoVazio');

        if (this.itens.length === 0) {
            listaItens.style.display = 'none';
            carrinhoVazio.style.display = 'block';
        } else {
            listaItens.style.display = 'block';
            carrinhoVazio.style.display = 'none';
            
            listaItens.innerHTML = this.itens.map(item => `
                <div class="item-carrinho" data-id="${item.id}">
                    <img src="${item.imagem}" alt="${item.nome}" class="item-imagem">
                    <div class="item-info">
                        <h4 class="item-nome">${item.nome}</h4>
                        <p class="item-descricao">${item.descricao}</p>
                        <p class="item-preco">R$ ${item.preco.toFixed(2)}</p>
                        <div class="item-controles">
                            <div class="quantidade-controle">
                                <button class="quantidade-btn diminuir" onclick="carrinho.alterarQuantidade('${item.id}', -1)">-</button>
                                <span class="quantidade">${item.quantidade}</span>
                                <button class="quantidade-btn aumentar" onclick="carrinho.alterarQuantidade('${item.id}', 1)">+</button>
                            </div>
                            <button class="remover-item" onclick="carrinho.removerItem('${item.id}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        this.atualizarResumo();
    }

    adicionarItem(produto) {
        const itemExistente = this.itens.find(item => item.id === produto.id);
        
        if (itemExistente) {
            itemExistente.quantidade += 1;
        } else {
            this.itens.push({
                ...produto,
                quantidade: 1
            });
        }
        
        this.salvarNoLocalStorage();
        this.renderizarCarrinho();
        this.mostrarNotificacao(`${produto.nome} adicionado ao carrinho!`);
    }

    removerItem(id) {
        this.itens = this.itens.filter(item => item.id !== id);
        this.salvarNoLocalStorage();
        this.renderizarCarrinho();
        this.mostrarNotificacao('Produto removido do carrinho');
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
            }
        }
    }

    atualizarResumo() {
        const subtotal = this.calcularSubtotal();
        const frete = this.calcularFrete();
        const desconto = this.calcularDesconto();
        const total = subtotal + frete - desconto;

        document.getElementById('subtotal').textContent = `R$ ${subtotal.toFixed(2)}`;
        document.getElementById('frete').textContent = `R$ ${frete.toFixed(2)}`;
        document.getElementById('desconto').textContent = `- R$ ${desconto.toFixed(2)}`;
        document.getElementById('total').textContent = `R$ ${total.toFixed(2)}`;

        // Habilitar/desabilitar botão de finalizar
        document.getElementById('btnFinalizar').disabled = this.itens.length === 0;
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
        
        switch(this.cupomAplicado) {
            case 'TECHFIT10':
                return subtotal * 0.1; // 10% de desconto
            case 'TECHFIT20':
                return subtotal * 0.2; // 20% de desconto
            case 'FREEGRATIS':
                return this.calcularFrete(); // Frete grátis
            default:
                return 0;
        }
    }

    aplicarCupom(codigo) {
        const cuponsValidos = ['TECHFIT10', 'TECHFIT20', 'FREEGRATIS'];
        
        if (cuponsValidos.includes(codigo.toUpperCase())) {
            this.cupomAplicado = codigo.toUpperCase();
            this.mostrarNotificacao(`Cupom ${codigo} aplicado com sucesso!`);
            this.atualizarResumo();
            return true;
        } else {
            this.mostrarNotificacao('Cupom inválido!', 'error');
            return false;
        }
    }

    configurarEventos() {
        // Cupom de desconto
        document.getElementById('aplicarCupom').addEventListener('click', () => {
            const inputCupom = document.getElementById('inputCupom');
            this.aplicarCupom(inputCupom.value);
            inputCupom.value = '';
        });

        document.getElementById('inputCupom').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                document.getElementById('aplicarCupom').click();
            }
        });

        // Finalizar compra
        document.getElementById('btnFinalizar').addEventListener('click', () => {
            this.finalizarCompra();
        });

        // Header navigation
        this.configurarHeader();
    }

    finalizarCompra() {
        if (this.itens.length === 0) {
            this.mostrarNotificacao('Adicione produtos ao carrinho primeiro!', 'error');
            return;
        }

        // Salvar dados para a página de pagamento
        const dadosCompra = {
            itens: this.itens,
            subtotal: this.calcularSubtotal(),
            frete: this.calcularFrete(),
            desconto: this.calcularDesconto(),
            total: this.calcularSubtotal() + this.calcularFrete() - this.calcularDesconto(),
            cupom: this.cupomAplicado
        };

        localStorage.setItem('dadosCompraTechFit', JSON.stringify(dadosCompra));
        
        // Redirecionar para página de pagamento
        window.location.href = 'pagamento.html';
    }

    configurarHeader() {
        const hamburger = document.querySelector('.hamburger-menu');
        const navigation = document.querySelector('.main-navigation');
        const navLinks = document.querySelectorAll('.nav-link');

        if (hamburger && navigation) {
            hamburger.addEventListener('click', () => {
                hamburger.classList.toggle('active');
                navigation.classList.toggle('active');
            });

            navLinks.forEach(link => {
                link.addEventListener('click', () => {
                    hamburger.classList.remove('active');
                    navigation.classList.remove('active');
                });
            });
        }
    }

    atualizarHeader() {
        // Atualizar contador no header se existir
        const cartCounter = document.querySelector('.cart-counter');
        if (cartCounter) {
            const totalItens = this.itens.reduce((total, item) => total + item.quantidade, 0);
            cartCounter.textContent = totalItens;
            cartCounter.style.display = totalItens > 0 ? 'flex' : 'none';
        }
    }

    mostrarNotificacao(mensagem, tipo = 'success') {
        // Criar notificação
        const notification = document.createElement('div');
        notification.className = `notification ${tipo}`;
        notification.innerHTML = `
            <i class="fas fa-${tipo === 'success' ? 'check' : 'exclamation'}"></i>
            <span>${mensagem}</span>
        `;

        // Estilos da notificação
        notification.style.cssText = `
            position: fixed;
            top: 100px;
            right: 20px;
            background: ${tipo === 'success' ? '#4CAF50' : '#f44336'};
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 10000;
            animation: slideIn 0.3s ease;
        `;

        document.body.appendChild(notification);

        // Remover após 3 segundos
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    salvarNoLocalStorage() {
        localStorage.setItem('carrinhoTechFit', JSON.stringify(this.itens));
    }

    limparCarrinho() {
        this.itens = [];
        this.cupomAplicado = null;
        this.salvarNoLocalStorage();
        this.renderizarCarrinho();
        this.mostrarNotificacao('Carrinho limpo!');
    }
}

// Inicializar carrinho
const carrinho = new Carrinho();

// Adicionar CSS para animações
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOut {
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
`;
document.head.appendChild(style);