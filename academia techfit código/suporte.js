// --- CHAT DE SUPORTE INTERATIVO ---

class SuporteChat {
    constructor() {
        this.chatMessages = document.getElementById('chatMessages');
        this.chatForm = document.getElementById('chatForm');
        this.chatInput = document.getElementById('chatInput');
        this.sendButton = document.getElementById('sendButton');
        this.chatStatus = document.getElementById('chatStatus');
        
        this.responses = {
            'problema-site': {
                question: "Qual problema você está enfrentando no site?",
                options: [
                    { text: "Site não carrega", value: "site-nao-carrega" },
                    { text: "Página com erro", value: "pagina-erro" },
                    { text: "Lentidão", value: "lentidao" },
                    { text: "Outro problema", value: "outro-problema-site" }
                ]
            },
            'produto': {
                question: "Sobre qual produto você tem dúvida?",
                options: [
                    { text: "Informações do produto", value: "info-produto" },
                    { text: "Disponibilidade", value: "disponibilidade" },
                    { text: "Preços", value: "precos" },
                    { text: "Garantia", value: "garantia" }
                ]
            },
            'agendamento': {
                question: "Qual problema com o agendamento?",
                options: [
                    { text: "Não consigo agendar", value: "nao-consegue-agendar" },
                    { text: "Horário indisponível", value: "horario-indisponivel" },
                    { text: "Erro no sistema", value: "erro-sistema-agendamento" },
                    { text: "Cancelar agendamento", value: "cancelar-agendamento" }
                ]
            },
            'pagamento': {
                question: "Qual a dificuldade com o pagamento?",
                options: [
                    { text: "Cartão recusado", value: "cartao-recusado" },
                    { text: "PIX não funciona", value: "pix-nao-funciona" },
                    { text: "Boleto não gerado", value: "boleto-nao-gerado" },
                    { text: "Reembolso", value: "reembolso" }
                ]
            }
        };

        this.solutions = {
            'site-nao-carrega': "Tente limpar o cache do navegador (Ctrl+F5) ou usar outro navegador. Se persistir, entre em contato pelo WhatsApp.",
            'pagina-erro': "Atualize a página (F5). Se o erro continuar, nos informe qual página específica está com problema.",
            'lentidao': "Verifique sua conexão com a internet. O site pode estar mais lento em horários de pico.",
            'outro-problema-site': "Por favor, descreva o problema com mais detalhes para que possamos ajudar.",
            
            'info-produto': "Todas as informações dos produtos estão disponíveis na página do produto. Precisa de algo específico?",
            'disponibilidade': "A disponibilidade é atualizada em tempo real. Se um produto aparece esgotado, volte em 24-48h.",
            'precos': "Todos os preços são mostrados com impostos inclusos. Promoções são por tempo limitado.",
            'garantia': "Todos os produtos têm garantia de 12 meses contra defeitos de fabricação.",
            
            'nao-consegue-agendar': "Verifique se está logado na sua conta. Se o problema persistir, tente em outro navegador.",
            'horario-indisponivel': "Os horários são liberados com 7 dias de antecedência. Tente agendar para outro horário.",
            'erro-sistema-agendamento': "Estamos com instabilidade momentânea. Tente novamente em 15 minutos.",
            'cancelar-agendamento': "Você pode cancelar pelo site até 2 horas antes do horário agendado.",
            
            'cartao-recusado': "Verifique os dados do cartão e limite disponível. Tente outro cartão ou forma de pagamento.",
            'pix-nao-funciona': "O PIX pode demorar até 30 segundos para processar. Verifique se copiou o código corretamente.",
            'boleto-nao-gerado': "Atualize a página. Se não aparecer, entre em contato com nosso suporte.",
            'reembolso': "Entre em contato pelo WhatsApp (19) 99936-4328 com o número do pedido para solicitar reembolso."
        };

        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupHeader();
    }

    setupEventListeners() {
        // Envio de mensagens
        this.chatForm.addEventListener('submit', (e) => {
            e.preventDefault();
            this.sendUserMessage();
        });

        // Opções rápidas
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('quick-option')) {
                const option = e.target.getAttribute('data-option');
                this.handleQuickOption(option, e.target.textContent);
            }
        });

        // Enter para enviar
        this.chatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendUserMessage();
            }
        });
    }

    sendUserMessage() {
        const message = this.chatInput.value.trim();
        if (!message) return;

        this.addMessage(message, 'user');
        this.chatInput.value = '';
        this.sendButton.disabled = true;

        // Simular resposta do bot
        this.showTypingIndicator();
        setTimeout(() => {
            this.hideTypingIndicator();
            this.generateBotResponse(message);
        }, 1500);
    }

    handleQuickOption(option, text) {
        this.addMessage(text, 'user');
        this.showTypingIndicator();
        
        setTimeout(() => {
            this.hideTypingIndicator();
            this.handleBotResponse(option);
        }, 1000);
    }

    handleBotResponse(option) {
        if (this.responses[option]) {
            this.addBotMessage(this.responses[option].question);
            
            // Mostrar opções de resposta
            setTimeout(() => {
                this.showQuickOptions(this.responses[option].options);
            }, 500);
        } else if (this.solutions[option]) {
            this.addBotMessage(this.solutions[option]);
            this.showFinalOptions();
        } else {
            this.addBotMessage("Desculpe, não entendi. Poderia reformular sua pergunta?");
        }
    }

    generateBotResponse(userMessage) {
        const message = userMessage.toLowerCase();
        
        if (message.includes('problema') || message.includes('erro') || message.includes('não funciona')) {
            this.addBotMessage("Entendo que está com um problema. Vamos resolver isso!");
            this.showQuickOptions([
                { text: "Problemas no site", value: "problema-site" },
                { text: "Problemas com agendamento", value: "agendamento" },
                { text: "Problemas com pagamento", value: "pagamento" }
            ]);
        } else if (message.includes('produto') || message.includes('compra') || message.includes('loja')) {
            this.addBotMessage("Posso ajudar com informações sobre nossos produtos!");
            this.showQuickOptions([
                { text: "Informações do produto", value: "info-produto" },
                { text: "Disponibilidade", value: "disponibilidade" },
                { text: "Preços", value: "precos" }
            ]);
        } else if (message.includes('agendar') || message.includes('horário') || message.includes('aula')) {
            this.addBotMessage("Vou ajudar com o agendamento!");
            this.showQuickOptions([
                { text: "Não consigo agendar", value: "nao-consegue-agendar" },
                { text: "Horário indisponível", value: "horario-indisponivel" },
                { text: "Cancelar agendamento", value: "cancelar-agendamento" }
            ]);
        } else {
            this.addBotMessage("Desculpe, não entendi completamente. Poderia ser mais específico ou escolher uma das opções abaixo?");
            this.showQuickOptions([
                { text: "Problemas no site", value: "problema-site" },
                { text: "Dúvidas sobre produtos", value: "produto" },
                { text: "Problemas com agendamento", value: "agendamento" },
                { text: "Dificuldades com pagamento", value: "pagamento" }
            ]);
        }
    }

    addMessage(text, type) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${type}-message`;
        messageDiv.textContent = text;
        this.chatMessages.appendChild(messageDiv);
        this.scrollToBottom();
    }

    addBotMessage(text) {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'message bot-message';
        messageDiv.textContent = text;
        this.chatMessages.appendChild(messageDiv);
        this.scrollToBottom();
    }

    showQuickOptions(options) {
        const optionsContainer = document.createElement('div');
        optionsContainer.className = 'quick-options';
        
        options.forEach(option => {
            const optionElement = document.createElement('div');
            optionElement.className = 'quick-option';
            optionElement.textContent = option.text;
            optionElement.setAttribute('data-option', option.value);
            optionsContainer.appendChild(optionElement);
        });

        this.chatMessages.appendChild(optionsContainer);
        this.scrollToBottom();
    }

    showFinalOptions() {
        setTimeout(() => {
            this.addBotMessage("Precisa de mais alguma coisa?");
            this.showQuickOptions([
                { text: "Sim, outra dúvida", value: "nova-duvida" },
                { text: "Não, obrigado", value: "fim" }
            ]);
        }, 1000);
    }

    showTypingIndicator() {
        this.chatStatus.innerHTML = `
            <div class="typing-indicator">
                <span>Digitando</span>
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
            </div>
        `;
    }

    hideTypingIndicator() {
        this.chatStatus.innerHTML = '';
    }

    scrollToBottom() {
        this.chatMessages.scrollTop = this.chatMessages.scrollHeight;
    }

    setupHeader() {
        const header = document.querySelector('.techfit-header');
        const hamburger = document.querySelector('.hamburger-menu');
        const navigation = document.querySelector('.main-navigation');
        const navLinks = document.querySelectorAll('.nav-link');

        // Efeito de scroll no header
        window.addEventListener('scroll', () => {
            header.classList.toggle('scrolled', window.scrollY > 100);
        });

        // Menu hambúrguer
        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('active');
            navigation.classList.toggle('active');
            document.body.style.overflow = navigation.classList.contains('active') ? 'hidden' : '';
        });

        // Fecha menu ao clicar em um link
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                hamburger.classList.remove('active');
                navigation.classList.remove('active');
                document.body.style.overflow = '';
            });
        });

        // Fecha menu ao clicar fora
        document.addEventListener('click', (event) => {
            if (!event.target.closest('.main-navigation') && !event.target.closest('.hamburger-menu')) {
                hamburger.classList.remove('active');
                navigation.classList.remove('active');
                document.body.style.overflow = '';
            }
        });

        // Ativar link da página atual
        this.setActiveNavLink();
    }

    setActiveNavLink() {
        const currentPage = window.location.pathname.split('/').pop() || 'inicio.html';
        const navLinks = document.querySelectorAll('.nav-link');
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === currentPage) {
                link.classList.add('active');
            }
        });
    }

    showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}"></i>
            <span>${message}</span>
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
}

// Inicializar o chat quando a página carregar
document.addEventListener('DOMContentLoaded', () => {
    new SuporteChat();
});