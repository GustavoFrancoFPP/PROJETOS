// pagamento.js
class Pagamento {
    constructor() {
        this.dadosCompra = JSON.parse(localStorage.getItem('dadosCompraTechFit')) || null;
        this.init();
    }

    init() {
        if (!this.dadosCompra) {
            this.mostrarErro('Dados do carrinho não encontrados. Redirecionando...');
            setTimeout(() => window.location.href = 'carrinho.html', 2000);
            return;
        }

        this.renderizarResumo();
        this.configurarEventos();
        this.configurarMascaras();
        this.configurarMetodosPagamento();
    }

    renderizarResumo() {
        const itensResumo = document.getElementById('itensResumo');
        const { itens, subtotal, frete, desconto, total } = this.dadosCompra;

        itensResumo.innerHTML = itens.map(item => `
            <div class="item-resumo">
                <img src="${item.imagem}" alt="${item.nome}">
                <div class="item-resumo-info">
                    <div class="item-resumo-nome">${item.nome}</div>
                    <div class="item-resumo-detalhes">
                        ${item.quantidade}x R$ ${item.preco.toFixed(2)}
                    </div>
                </div>
            </div>
        `).join('');

        document.getElementById('resumoSubtotal').textContent = `R$ ${subtotal.toFixed(2)}`;
        document.getElementById('resumoFrete').textContent = `R$ ${frete.toFixed(2)}`;
        document.getElementById('resumoDesconto').textContent = `- R$ ${desconto.toFixed(2)}`;
        document.getElementById('resumoTotal').textContent = `R$ ${total.toFixed(2)}`;

        // Atualizar informações de entrega
        const infoEntrega = document.getElementById('infoEntrega');
        if (frete === 0) {
            infoEntrega.textContent = 'Frete grátis - Entrega em até 7 dias úteis';
        } else {
            infoEntrega.textContent = `Frete: R$ ${frete.toFixed(2)} - Entrega em até 7 dias úteis`;
        }

        // Configurar parcelamento
        this.configurarParcelamento(total);
    }

    configurarParcelamento(total) {
        const selectParcelas = document.getElementById('parcelas');
        selectParcelas.innerHTML = '';

        const maxParcelas = 12;
        const parcelaMinima = 50; // Valor mínimo por parcela

        let parcelasDisponiveis = Math.min(maxParcelas, Math.floor(total / parcelaMinima));
        parcelasDisponiveis = Math.max(parcelasDisponiveis, 1);

        for (let i = 1; i <= parcelasDisponiveis; i++) {
            const valorParcela = total / i;
            const juros = i > 6 ? 'com juros' : 'sem juros';
            const option = document.createElement('option');
            option.value = i;
            option.textContent = `${i}x R$ ${valorParcela.toFixed(2)} ${juros}`;
            selectParcelas.appendChild(option);
        }
    }

    configurarEventos() {
        // Métodos de pagamento
        document.querySelectorAll('input[name="metodo-pagamento"]').forEach(radio => {
            radio.addEventListener('change', (e) => {
                this.alternarMetodoPagamento(e.target.value);
            });
        });

        // Formatação do número do cartão
        document.getElementById('numero-cartao').addEventListener('input', (e) => {
            this.formatarNumeroCartao(e.target);
            this.identificarBandeira(e.target.value);
        });

        // Formatação da validade
        document.getElementById('validade').addEventListener('input', (e) => {
            this.formatarValidade(e.target);
        });

        // Buscar CEP
        document.getElementById('cep').addEventListener('blur', (e) => {
            this.buscarCEP(e.target.value);
        });

        // Confirmação de pagamento
        document.getElementById('btnConfirmarPagamento').addEventListener('click', () => {
            this.processarPagamento();
        });

        // Header navigation
        this.configurarHeader();
    }

    configurarMascaras() {
        // CPF
        const cpfInput = document.getElementById('cpf');
        cpfInput.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                value = value.replace(/(\d{3})(\d)/, '$1.$2')
                           .replace(/(\d{3})(\d)/, '$1.$2')
                           .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            }
            e.target.value = value;
        });

        // Telefone
        const telefoneInput = document.getElementById('telefone');
        telefoneInput.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                value = value.replace(/(\d{2})(\d)/, '($1) $2')
                           .replace(/(\d{5})(\d)/, '$1-$2');
            }
            e.target.value = value;
        });

        // CEP
        const cepInput = document.getElementById('cep');
        cepInput.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 8) {
                value = value.replace(/(\d{5})(\d)/, '$1-$2');
            }
            e.target.value = value;
        });

        // CVV
        const cvvInput = document.getElementById('cvv');
        cvvInput.addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(/\D/g, '').substring(0, 3);
        });
    }

    formatarNumeroCartao(input) {
        let value = input.value.replace(/\D/g, '');
        value = value.substring(0, 16);
        value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
        input.value = value;
    }

    formatarValidade(input) {
        let value = input.value.replace(/\D/g, '');
        if (value.length >= 2) {
            value = value.substring(0, 2) + '/' + value.substring(2, 4);
        }
        input.value = value;
    }

    identificarBandeira(numero) {
        const bandeiras = document.querySelectorAll('.bandeiras-cartao i');
        bandeiras.forEach(icon => icon.classList.remove('active'));

        numero = numero.replace(/\s/g, '');

        if (/^4/.test(numero)) {
            document.querySelector('[data-bandeira="visa"]').classList.add('active');
        } else if (/^5[1-5]/.test(numero)) {
            document.querySelector('[data-bandeira="mastercard"]').classList.add('active');
        } else if (/^3[47]/.test(numero)) {
            document.querySelector('[data-bandeira="amex"]').classList.add('active');
        }
    }

    alternarMetodoPagamento(metodo) {
        // Esconder todos os formulários
        document.getElementById('form-cartao').style.display = 'none';
        document.getElementById('form-pix').style.display = 'none';
        document.getElementById('form-boleto').style.display = 'none';

        // Mostrar o formulário selecionado
        document.getElementById(`form-${metodo}`).style.display = 'block';

        // Atualizar texto do botão
        const btn = document.getElementById('btnConfirmarPagamento');
        if (metodo === 'pix') {
            btn.innerHTML = '<i class="fas fa-qrcode"></i> Gerar QR Code PIX';
        } else if (metodo === 'boleto') {
            btn.innerHTML = '<i class="fas fa-barcode"></i> Gerar Boleto';
        } else {
            btn.innerHTML = '<i class="fas fa-lock"></i> Confirmar Pagamento';
        }
    }

    configurarMetodosPagamento() {
        // Inicializar com cartão selecionado
        this.alternarMetodoPagamento('cartao');
    }

    async buscarCEP(cep) {
        cep = cep.replace(/\D/g, '');
        
        if (cep.length !== 8) return;

        try {
            const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
            const data = await response.json();

            if (!data.erro) {
                document.getElementById('endereco').value = data.logradouro;
                document.getElementById('bairro').value = data.bairro;
                document.getElementById('cidade').value = data.localidade;
                document.getElementById('estado').value = data.uf;
                document.getElementById('numero').focus();
            }
        } catch (error) {
            console.error('Erro ao buscar CEP:', error);
        }
    }

    validarFormulario() {
        const requiredFields = [
            'nome', 'email', 'cpf', 'telefone', 'cep', 'endereco', 'numero', 
            'bairro', 'cidade', 'estado'
        ];

        for (const field of requiredFields) {
            const input = document.getElementById(field);
            if (!input.value.trim()) {
                this.mostrarErro(`Preencha o campo ${field}`);
                input.focus();
                return false;
            }
        }

        // Validar email
        const email = document.getElementById('email').value;
        if (!this.validarEmail(email)) {
            this.mostrarErro('E-mail inválido');
            return false;
        }

        // Validar CPF
        const cpf = document.getElementById('cpf').value;
        if (!this.validarCPF(cpf)) {
            this.mostrarErro('CPF inválido');
            return false;
        }

        // Validar termos
        if (!document.getElementById('termos').checked) {
            this.mostrarErro('Aceite os termos e condições para continuar');
            return false;
        }

        return true;
    }

    validarEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }

    validarCPF(cpf) {
        cpf = cpf.replace(/\D/g, '');
        return cpf.length === 11;
    }

    async processarPagamento() {
        if (!this.validarFormulario()) return;

        const metodoPagamento = document.querySelector('input[name="metodo-pagamento"]:checked').value;
        const btn = document.getElementById('btnConfirmarPagamento');
        
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';

        try {
            // Envia dados para o servidor via AJAX
            const response = await fetch('processar_pagamento.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    metodoPagamento: metodoPagamento,
                    dadosCompra: this.dadosCompra,
                    dadosCliente: this.obterDadosCliente()
                })
            });

            const result = await response.json();

            if (result.sucesso) {
                // Limpa localStorage
                localStorage.removeItem('dadosCompraTechFit');
                localStorage.removeItem('carrinhoTechFit');
                
                // Mostra sucesso e redireciona
                this.mostrarSucesso('Pagamento processado com sucesso!');
                
                setTimeout(() => {
                    window.location.href = 'confirmacao.php?success=1&total=' + result.total;
                }, 1500);
            } else {
                throw new Error(result.mensagem || 'Erro ao processar pagamento');
            }
        } catch (error) {
            console.error('Erro ao processar pagamento:', error);
            this.mostrarErro(error.message || 'Erro ao processar pagamento. Tente novamente.');
            
            btn.disabled = false;
            btn.innerHTML = 'Confirmar Pagamento';
        }
    }

    obterDadosCliente() {
        return {
            nome: document.getElementById('nome').value,
            email: document.getElementById('email').value,
            cpf: document.getElementById('cpf').value,
            telefone: document.getElementById('telefone').value,
            endereco: {
                cep: document.getElementById('cep').value,
                logradouro: document.getElementById('endereco').value,
                numero: document.getElementById('numero').value,
                complemento: document.getElementById('complemento').value,
                bairro: document.getElementById('bairro').value,
                cidade: document.getElementById('cidade').value,
                estado: document.getElementById('estado').value
            }
        };
    }

    gerarNumeroPedido() {
        return 'TECH' + Date.now().toString().substr(-8);
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

    mostrarErro(mensagem) {
        this.mostrarNotificacao(mensagem, 'error');
    }

    mostrarSucesso(mensagem) {
        this.mostrarNotificacao(mensagem, 'success');
    }

    mostrarNotificacao(mensagem, tipo = 'success') {
        const notification = document.createElement('div');
        notification.className = `notification ${tipo}`;
        notification.innerHTML = `
            <i class="fas fa-${tipo === 'success' ? 'check' : 'exclamation'}"></i>
            <span>${mensagem}</span>
        `;

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

        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
}

// Adicionar estilos para animações
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
    
    .fa-spin {
        animation: fa-spin 1s infinite linear;
    }
    
    @keyframes fa-spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
`;
document.head.appendChild(style);

// Inicializar pagamento
const pagamento = new Pagamento();