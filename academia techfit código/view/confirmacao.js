// confirmacao.js - Script completo da página de confirmação
document.addEventListener('DOMContentLoaded', function() {
    class ConfirmacaoPedido {
        constructor() {
            this.pedido = JSON.parse(localStorage.getItem('pedidoTechFit')) || null;
            this.init();
        }

        init() {
            if (!this.pedido) {
                this.mostrarErro('Pedido não encontrado. Redirecionando para a página de produtos...');
                setTimeout(() => {
                    window.location.href = 'produtos.html';
                }, 3000);
                return;
            }

            this.renderizarConfirmacao();
            this.configurarEventos();
            this.configurarHeader();
            this.iniciarContadorRedirecionamento();
            
            console.log('Pedido confirmado:', this.pedido);
        }

        renderizarConfirmacao() {
            try {
                const { numeroPedido, dadosCliente, total, itens, metodoPagamento, frete, desconto } = this.pedido;

                // Informações principais
                document.getElementById('numeroPedido').textContent = numeroPedido;
                document.getElementById('emailCliente').textContent = dadosCliente.email;
                
                // Totais
                const totalItens = itens.reduce((total, item) => total + item.quantidade, 0);
                document.getElementById('totalItens').textContent = totalItens;
                document.getElementById('totalPedido').textContent = `R$ ${total.toFixed(2)}`;
                
                // Método de pagamento formatado
                document.getElementById('metodoPagamento').textContent = this.formatarMetodoPagamento(metodoPagamento);

                // Previsão de entrega
                const previsaoEntrega = this.calcularPrevisaoEntrega();
                document.getElementById('previsaoEntrega').textContent = previsaoEntrega;

                // Renderizar itens do pedido (se houver seção para isso)
                this.renderizarItensPedido(itens);

                // Adicionar informações adicionais
                this.adicionarInformacoesAdicionais();

            } catch (error) {
                console.error('Erro ao renderizar confirmação:', error);
                this.mostrarErro('Erro ao carregar informações do pedido.');
            }
        }

        renderizarItensPedido(itens) {
            const containerItens = document.querySelector('.itens-confirmacao');
            if (!containerItens) return;

            containerItens.innerHTML = itens.map(item => `
                <div class="item-confirmacao">
                    <img src="${item.imagem}" alt="${item.nome}" class="item-confirmacao-imagem">
                    <div class="item-confirmacao-info">
                        <h4>${item.nome}</h4>
                        <p>Quantidade: ${item.quantidade}</p>
                        <p class="item-preco">R$ ${(item.preco * item.quantidade).toFixed(2)}</p>
                    </div>
                </div>
            `).join('');
        }

        adicionarInformacoesAdicionais() {
            const infoAdicional = document.querySelector('.info-adicional');
            if (!infoAdicional) return;

            const { dadosCliente, metodoPagamento } = this.pedido;

            const infoHTML = `
                <div class="detalhes-adicionais">
                    <div class="detalhe-grupo">
                        <h5><i class="fas fa-user"></i> Dados do Cliente</h5>
                        <p><strong>Nome:</strong> ${dadosCliente.nome}</p>
                        <p><strong>Telefone:</strong> ${dadosCliente.telefone}</p>
                        <p><strong>CPF:</strong> ${dadosCliente.cpf}</p>
                    </div>
                    
                    <div class="detalhe-grupo">
                        <h5><i class="fas fa-map-marker-alt"></i> Endereço de Entrega</h5>
                        <p>${dadosCliente.endereco.logradouro}, ${dadosCliente.endereco.numero}</p>
                        <p>${dadosCliente.endereco.bairro}</p>
                        <p>${dadosCliente.endereco.cidade} - ${dadosCliente.endereco.estado}</p>
                        <p>CEP: ${dadosCliente.endereco.cep}</p>
                        ${dadosCliente.endereco.complemento ? `<p>Complemento: ${dadosCliente.endereco.complemento}</p>` : ''}
                    </div>
                    
                    <div class="detalhe-grupo">
                        <h5><i class="fas fa-credit-card"></i> Pagamento</h5>
                        <p><strong>Método:</strong> ${this.formatarMetodoPagamento(metodoPagamento)}</p>
                        <p><strong>Status:</strong> <span class="status-pago">Pagamento Aprovado</span></p>
                    </div>
                </div>
            `;

            infoAdicional.insertAdjacentHTML('beforeend', infoHTML);
        }

        formatarMetodoPagamento(metodo) {
            const metodos = {
                'cartao': 'Cartão de Crédito',
                'pix': 'PIX',
                'boleto': 'Boleto Bancário'
            };
            return metodos[metodo] || metodo;
        }

        calcularPrevisaoEntrega() {
            const hoje = new Date();
            const diasUteisParaEntrega = 7;
            
            let diasAdicionados = 0;
            let dataEntrega = new Date(hoje);
            
            // Adiciona dias úteis (segunda a sexta)
            while (diasAdicionados < diasUteisParaEntrega) {
                dataEntrega.setDate(dataEntrega.getDate() + 1);
                
                // Verifica se é dia útil (segunda a sexta)
                const diaSemana = dataEntrega.getDay();
                if (diaSemana !== 0 && diaSemana !== 6) { // 0 = domingo, 6 = sábado
                    diasAdicionados++;
                }
            }
            
            // Formata a data
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            };
            
            return dataEntrega.toLocaleDateString('pt-BR', options);
        }

        configurarEventos() {
            // Botão de continuar comprando
            const btnContinuarComprando = document.querySelector('.btn-continuar-comprando');
            if (btnContinuarComprando) {
                btnContinuarComprando.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.continuarComprando();
                });
            }

            // Botão de voltar ao início
            const btnVoltarInicio = document.querySelector('.btn-voltar-inicio');
            if (btnVoltarInicio) {
                btnVoltarInicio.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.voltarAoInicio();
                });
            }

            // Botão de imprimir comprovante
            const btnImprimir = document.querySelector('.btn-imprimir');
            if (btnImprimir) {
                btnImprimir.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.imprimirComprovante();
                });
            }

            // Botão de acompanhar pedido
            const btnAcompanhar = document.querySelector('.btn-acompanhar');
            if (btnAcompanhar) {
                btnAcompanhar.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.acompanharPedido();
                });
            }

            // Configurar compartilhamento
            this.configurarCompartilhamento();
        }

        continuarComprando() {
            this.mostrarLoading('Redirecionando para a loja...');
            setTimeout(() => {
                window.location.href = 'produtos.html';
            }, 1000);
        }

        voltarAoInicio() {
            this.mostrarLoading('Redirecionando para o início...');
            setTimeout(() => {
                window.location.href = 'inicio.html';
            }, 1000);
        }

        imprimirComprovante() {
            this.mostrarNotificacao('Gerando comprovante...', 'info');
            
            setTimeout(() => {
                const conteudoImprimir = `
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Comprovante TECHFIT - Pedido ${this.pedido.numeroPedido}</title>
                        <style>
                            body { font-family: Arial, sans-serif; margin: 20px; }
                            .header { text-align: center; margin-bottom: 30px; }
                            .detalhes { margin-bottom: 20px; }
                            .itens { margin: 20px 0; }
                            .item { display: flex; justify-content: space-between; margin: 10px 0; }
                            .total { font-weight: bold; font-size: 1.2em; border-top: 2px solid #000; padding-top: 10px; }
                            .footer { margin-top: 30px; text-align: center; font-size: 0.9em; color: #666; }
                        </style>
                    </head>
                    <body>
                        <div class="header">
                            <h1>TECHFIT</h1>
                            <h2>Comprovante de Pedido</h2>
                            <p><strong>Número do Pedido:</strong> ${this.pedido.numeroPedido}</p>
                            <p><strong>Data:</strong> ${new Date().toLocaleDateString('pt-BR')}</p>
                        </div>
                        
                        <div class="detalhes">
                            <h3>Dados do Cliente</h3>
                            <p><strong>Nome:</strong> ${this.pedido.dadosCliente.nome}</p>
                            <p><strong>E-mail:</strong> ${this.pedido.dadosCliente.email}</p>
                        </div>
                        
                        <div class="itens">
                            <h3>Itens do Pedido</h3>
                            ${this.pedido.itens.map(item => `
                                <div class="item">
                                    <span>${item.nome} (${item.quantidade}x)</span>
                                    <span>R$ ${(item.preco * item.quantidade).toFixed(2)}</span>
                                </div>
                            `).join('')}
                        </div>
                        
                        <div class="total">
                            <p><strong>Total: R$ ${this.pedido.total.toFixed(2)}</strong></p>
                        </div>
                        
                        <div class="footer">
                            <p>Obrigado por comprar na TECHFIT!</p>
                            <p>www.techfit.com.br</p>
                        </div>
                    </body>
                    </html>
                `;
                
                const janelaImpressao = window.open('', '_blank');
                janelaImpressao.document.write(conteudoImprimir);
                janelaImpressao.document.close();
                janelaImpressao.print();
                
            }, 1000);
        }

        acompanharPedido() {
            this.mostrarNotificacao('Em desenvolvimento - Em breve você poderá acompanhar seu pedido aqui!', 'info');
        }

        configurarCompartilhamento() {
            const btnCompartilhar = document.querySelector('.btn-compartilhar');
            if (btnCompartilhar && navigator.share) {
                btnCompartilhar.style.display = 'flex';
                btnCompartilhar.addEventListener('click', () => {
                    this.compartilharPedido();
                });
            }
        }

        compartilharPedido() {
            if (navigator.share) {
                navigator.share({
                    title: 'Meu pedido TECHFIT',
                    text: `Acabei de fazer um pedido na TECHFIT! Número do pedido: ${this.pedido.numeroPedido}`,
                    url: window.location.href
                })
                .then(() => this.mostrarNotificacao('Pedido compartilhado com sucesso!', 'success'))
                .catch(error => console.log('Erro ao compartilhar:', error));
            }
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

                // Fechar menu ao clicar fora
                document.addEventListener('click', (event) => {
                    if (!event.target.closest('.main-navigation') && !event.target.closest('.hamburger-menu')) {
                        hamburger.classList.remove('active');
                        navigation.classList.remove('active');
                    }
                });
            }
        }

        iniciarContadorRedirecionamento() {
            // Opcional: Redirecionar automaticamente após 60 segundos
            setTimeout(() => {
                this.mostrarNotificacao('Redirecionando para a página inicial...', 'info');
                setTimeout(() => {
                    window.location.href = 'inicio.html';
                }, 2000);
            }, 60000);
        }

        mostrarLoading(mensagem) {
            const loading = document.createElement('div');
            loading.className = 'loading-overlay';
            loading.innerHTML = `
                <div class="loading-content">
                    <div class="spinner"></div>
                    <p>${mensagem}</p>
                </div>
            `;
            
            loading.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.8);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10000;
                color: white;
            `;
            
            document.body.appendChild(loading);
            
            return {
                remover: () => loading.remove()
            };
        }

        mostrarNotificacao(mensagem, tipo = 'success') {
            // Remover notificações anteriores
            const notificacoesAnteriores = document.querySelectorAll('.notification');
            notificacoesAnteriores.forEach(notif => notif.remove());

            const notification = document.createElement('div');
            notification.className = `notification ${tipo}`;
            
            const icones = {
                success: 'fa-check-circle',
                error: 'fa-exclamation-circle',
                warning: 'fa-exclamation-triangle',
                info: 'fa-info-circle'
            };

            notification.innerHTML = `
                <i class="fas ${icones[tipo] || 'fa-info-circle'}"></i>
                <span>${mensagem}</span>
            `;

            const cores = {
                success: '#4CAF50',
                error: '#f44336',
                warning: '#ff9800',
                info: '#2196F3'
            };

            notification.style.cssText = `
                position: fixed;
                top: 100px;
                right: 20px;
                background: ${cores[tipo] || '#2196F3'};
                color: white;
                padding: 15px 20px;
                border-radius: 10px;
                display: flex;
                align-items: center;
                gap: 10px;
                z-index: 10000;
                animation: slideInRight 0.3s ease;
                max-width: 400px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            `;

            document.body.appendChild(notification);

            // Remover após 5 segundos
            setTimeout(() => {
                notification.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 300);
            }, 5000);
        }

        mostrarErro(mensagem) {
            this.mostrarNotificacao(mensagem, 'error');
        }

        // Método para enviar e-mail de confirmação (simulado)
        enviarEmailConfirmacao() {
            console.log('Enviando e-mail de confirmação para:', this.pedido.dadosCliente.email);
            // Em uma aplicação real, aqui seria uma chamada para a API de e-mail
        }
    }

    // Adicionar estilos CSS para animações
    const styles = document.createElement('style');
    styles.textContent = `
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        
        .loading-content .spinner {
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top: 4px solid #00f0e1;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin-bottom: 15px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .detalhes-adicionais {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .detalhe-grupo {
            background: rgba(255, 255, 255, 0.05);
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #00f0e1;
        }
        
        .detalhe-grupo h5 {
            color: #00f0e1;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .detalhe-grupo p {
            margin: 5px 0;
            color: #ccc;
        }
        
        .status-pago {
            color: #4CAF50;
            font-weight: bold;
        }
        
        .btn-compartilhar {
            display: none;
            align-items: center;
            gap: 10px;
            background: #4267B2;
            color: white;
        }
        
        .btn-compartilhar:hover {
            background: #365899;
        }
        
        .btn-imprimir {
            background: #666;
            color: white;
        }
        
        .btn-imprimir:hover {
            background: #555;
        }
        
        .item-confirmacao {
            display: flex;
            gap: 15px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            margin-bottom: 10px;
        }
        
        .item-confirmacao-imagem {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
        }
        
        .item-confirmacao-info h4 {
            margin: 0 0 5px 0;
            font-size: 1rem;
        }
        
        .item-confirmacao-info p {
            margin: 2px 0;
            color: #ccc;
            font-size: 0.9rem;
        }
        
        .item-preco {
            color: #00f0e1 !important;
            font-weight: bold;
        }
    `;
    document.head.appendChild(styles);

    // Inicializar a confirmação do pedido
    const confirmacao = new ConfirmacaoPedido();

    // Enviar e-mail de confirmação (simulado)
    setTimeout(() => {
        confirmacao.enviarEmailConfirmacao();
    }, 2000);

    // Analytics - Registrar conversão de venda
    console.log('✅ Conversão registrada - Pedido confirmado');
    
    // Limpar dados sensíveis após um tempo
    setTimeout(() => {
        localStorage.removeItem('dadosCompraTechFit');
        console.log('Dados sensíveis limpos do localStorage');
    }, 60000); // 1 minuto
});

// Service Worker para cache (opcional)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/sw.js').then(function(registration) {
            console.log('ServiceWorker registration successful with scope: ', registration.scope);
        }, function(err) {
            console.log('ServiceWorker registration failed: ', err);
        });
    });
}