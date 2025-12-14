document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.classes button').forEach(btn => {
        btn.addEventListener('click', function () {
            const li   = this.closest('li');
            const nome = li.querySelector('.name').textContent;
            const hora = li.querySelector('.time').textContent;
            const spot = li.querySelector('.spots');

            if (!spot) return;

            let vagas = parseInt(spot.textContent.match(/\d+/)[0]);
            if (vagas === 0) return;

            vagas--;
            spot.textContent = `${vagas} vagas disponíveis`;

            if (vagas === 0) {
                this.replaceWith(createFullBadge());
            }
            alert(`Aula "${nome}" agendada para ${hora} com sucesso!`);
        });
    });
    document.querySelectorAll('.weekdays div').forEach(d => {
        d.addEventListener('click', function () {
            document.querySelector('.weekdays .active')?.classList.remove('active');
            this.classList.add('active');
        });
    });

    function createFullBadge() {
        const span = document.createElement('span');
        span.className = 'full';
        span.textContent = 'LOTADO';
        return span;
    }
});

document.addEventListener('DOMContentLoaded', function () {
    // --- CONFIGURAÇÃO DO AGENDAMENTO DE DIAS ---
    const weekdays = document.querySelectorAll('.weekdays li');
    const datePicker = document.querySelector('.date-picker .current');
    const nextDayBtn = document.querySelector('.date-picker .arrow');
    
    let currentDate = new Date();
    
    // Dias da semana em português
    const weekDaysPT = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'];
    const monthsPT = [
        'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun',
        'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'
    ];
    
    // Função para formatar a data
    function formatDate(date) {
        const day = date.getDate();
        const month = monthsPT[date.getMonth()];
        const weekDay = weekDaysPT[date.getDay()];
        return `${weekDay}, ${day} ${month}`;
    }
    
    // Função para atualizar a interface com a data atual
    function updateDateDisplay() {
        datePicker.textContent = formatDate(currentDate);
        updateWeekdaysHighlight();
        generateRandomSpots(); // Atualiza vagas quando muda o dia
    }
    
    // Função para destacar o dia da semana atual
    function updateWeekdaysHighlight() {
        const currentWeekDay = currentDate.getDay();
        weekdays.forEach((day, index) => {
            day.classList.toggle('active', index === currentWeekDay);
        });
    }
    
    // Função para avançar para o próximo dia
    function goToNextDay() {
        currentDate.setDate(currentDate.getDate() + 1);
        updateDateDisplay();
    }
    
    // Event listeners para os dias da semana
    weekdays.forEach((day, index) => {
        day.addEventListener('click', function() {
            // Calcula a diferença de dias entre o dia clicado e o atual
            const currentDayOfWeek = currentDate.getDay();
            let dayDifference = index - currentDayOfWeek;
            
            // Se for domingo (0) e clicar em sábado (6), vai para o próximo sábado
            if (dayDifference < -1) dayDifference += 7;
            // Se for sábado (6) e clicar em domingo (0), vai para o próximo domingo
            else if (dayDifference > 5) dayDifference -= 7;
            
            currentDate.setDate(currentDate.getDate() + dayDifference);
            updateDateDisplay();
        });
    });
    
    // Event listener para o botão de próximo dia
    nextDayBtn.addEventListener('click', goToNextDay);
    
    // --- SISTEMA DE AGENDAMENTO DE AULAS ---
    const classItems = document.querySelectorAll('.class-item');
    
    // Função para gerar números aleatórios de vagas (para simulação)
    function generateRandomSpots() {
        classItems.forEach(item => {
            const spotsElement = item.querySelector('.spots');
            if (spotsElement && !item.querySelector('.full-btn')) {
                const randomSpots = Math.floor(Math.random() * 50) + 1;
                spotsElement.textContent = `${randomSpots} vagas disponíveis`;
            }
        });
    }
    
    // Função para criar badge "LOTADO"
    function createFullBadge() {
        const span = document.createElement('span');
        span.className = 'full-btn';
        span.textContent = 'LOTADO';
        span.disabled = true;
        return span;
    }
    
    // Função para agendar aula
    function scheduleClass(button, className, classTime) {
        const listItem = button.closest('.class-item');
        const spotsElement = listItem.querySelector('.spots');
        
        if (!spotsElement) return;
        
        let spotsAvailable = parseInt(spotsElement.textContent.match(/\d+/)[0]);
        
        if (spotsAvailable === 0) {
            alert('Desculpe, esta aula já está lotada!');
            return;
        }
        
        spotsAvailable--;
        spotsElement.textContent = `${spotsAvailable} vagas disponíveis`;
        
        // Atualiza a interface
        if (spotsAvailable === 0) {
            button.replaceWith(createFullBadge());
            alert(`Última vaga para "${className}" às ${classTime} foi reservada!`);
        } else {
            alert(`Aula "${className}" agendada para ${classTime} com sucesso!\nVagas restantes: ${spotsAvailable}`);
        }
        
        // Simula um pequeno delay para feedback visual
        button.style.backgroundColor = '#00a38c';
        button.textContent = 'Agendado!';
        setTimeout(() => {
            if (spotsAvailable > 0) {
                button.style.backgroundColor = '';
                button.textContent = 'Agendar';
            }
        }, 2000);
    }
    
    // Adiciona event listeners para os botões de agendamento
    classItems.forEach(item => {
        const button = item.querySelector('.schedule-btn');
        if (button) {
            button.addEventListener('click', function() {
                const className = item.querySelector('.name').textContent;
                const classTime = item.querySelector('.time').textContent;
                scheduleClass(this, className, classTime);
            });
        }
    });
    
    // --- INICIALIZAÇÃO ---
    function initializeAgendamento() {
        updateDateDisplay();
        generateRandomSpots();
        
        console.log('Sistema de agendamento inicializado!');
        console.log('Funcionalidades disponíveis:');
        console.log('- Clique nos dias da semana para navegar');
        console.log('- Botão "→" para avançar um dia');
        console.log('- Botões "Agendar" para reservar aulas');
    }
    
    // Inicializa o sistema
    initializeAgendamento();
    
    // --- BÔNUS: Atualização automática de data ---
    // Atualiza a data meia-noite (opcional)
    function setupMidnightUpdate() {
        const now = new Date();
        const midnight = new Date();
        midnight.setHours(24, 0, 0, 0);
        const timeUntilMidnight = midnight - now;
        
        setTimeout(() => {
            currentDate = new Date();
            updateDateDisplay();
            // Configura para executar todo dia à meia-noite
            setInterval(() => {
                currentDate = new Date();
                updateDateDisplay();
            }, 24 * 60 * 60 * 1000);
        }, timeUntilMidnight);
    }
    
    // Inicia a atualização automática (opcional)
    setupMidnightUpdate();
});

// Funcionalidades do Header Moderno
document.addEventListener('DOMContentLoaded', function() {
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
    hamburger.addEventListener('click', function() {
        hamburger.classList.toggle('active');
        navigation.classList.toggle('active');
        
        // Previne scroll quando menu está aberto
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

    // Destacar link ativo baseado na página atual
    function setActiveNavLink() {
        const currentPage = window.location.pathname.split('/').pop() || 'inicio.html';
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === currentPage) {
                link.classList.add('active');
            }
        });
    }

    setActiveNavLink();
});