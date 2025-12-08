// Dados das unidades
const unidadesData = {
    'paulista': {
        nome: 'TechFit Paulista',
        endereco: 'Av. Paulista, 1234 - Bela Vista, São Paulo - SP',
        telefone: '(11) 3456-7890',
        email: 'paulista@techfit.com',
        imagem: 'imagens/academia-interior.jpg',
        descricao: 'A TechFit Paulista é nossa unidade flagship, localizada no coração da Avenida Paulista. Com 3 andares e 2.500m² de área total, oferecemos a mais completa estrutura de equipamentos de última geração, acompanhamento com IA e diversas modalidades de exercícios.',
        sobre: `
            <p>Inaugurada em 2020, a TechFit Paulista foi projetada para oferecer a melhor experiência em fitness inteligente. Nossa unidade conta com:</p>
            <ul style="color: #ccc; line-height: 1.8; margin: 15px 0; padding-left: 20px;">
                <li>Área de musculação com equipamentos TechnoGym de última geração</li>
                <li>Estúdios para aulas coletivas (Yoga, Pilates, Spinning)</li>
                <li>Piscina semiolímpica coberta e aquecida</li>
                <li>Área de crossfit com 300m²</li>
                <li>Estacionamento próprio com 200 vagas</li>
                <li>Spa com sauna seca e úmida</li>
            </ul>
        `
    },
    'ipanema': {
        nome: 'TechFit Ipanema',
        endereco: 'Rua Visconde de Pirajá, 550 - Ipanema, Rio de Janeiro - RJ',
        telefone: '(21) 9876-5432',
        email: 'ipanema@techfit.com',
        imagem: 'imagens/academia-interior2.jpg',
        descricao: 'A TechFit Ipanema combina fitness de ponta com o lifestyle carioca. Localizada a poucos metros da praia, oferecemos treinos inteligentes com vista para o mar e toda a tecnologia TechFit.',
        sobre: `
            <p>Com vista privilegiada para o mar, a TechFit Ipanema une tecnologia e bem-estar em um ambiente único. Destaques:</p>
            <ul style="color: #ccc; line-height: 1.8; margin: 15px 0; padding-left: 20px;">
                <li>Área de musculação com vista para o mar</li>
                <li>Estúdio de yoga com deck externo</li>
                <li>Equipamentos Life Fitness premium</li>
                <li>Área de treinamento funcional ao ar livre</li>
                <li>Juice bar com produtos naturais</li>
                <li>Vestiários com armários inteligentes</li>
            </ul>
        `
    },
    'savassi': {
        nome: 'TechFit Savassi',
        endereco: 'Rua Antônio de Albuquerque, 781 - Savassi, Belo Horizonte - MG',
        telefone: '(31) 2345-6789',
        email: 'savassi@techfit.com',
        imagem: 'imagens/academia-interior3.jpg',
        descricao: 'No coração de BH, a TechFit Savassi oferece o que há de mais moderno em fitness inteligente. Estrutura completa em um dos bairros mais charmosos da cidade.',
        sobre: `
            <p>Localizada no coração de Belo Horizonte, a TechFit Savassi é referência em tecnologia e qualidade. Características:</p>
            <ul style="color: #ccc; line-height: 1.8; margin: 15px 0; padding-left: 20px;">
                <li>Dois andares com estrutura completa</li>
                <li>Área de lutas com tatame profissional</li>
                <li>Estúdio de pilates equipado</li>
                <li>Consultório de nutrição integrado</li>
                <li>Parking valet gratuito</li>
                <li>Área de convivência com coworking</li>
            </ul>
        `
    },
    'morumbi': {
        nome: 'TechFit Morumbi',
        endereco: 'Av. Jorge João Saad, 767 - Morumbi, São Paulo - SP',
        telefone: '(11) 8765-4321',
        email: 'morumbi@techfit.com',
        imagem: 'imagens/academia-interior4.jpg',
        descricao: 'A TechFit Morumbi é nossa unidade mais espaçosa, com 3.000m² de pura tecnologia fitness. Ideal para quem busca variedade e equipamentos de ponta.',
        sobre: `
            <p>Com uma das maiores estruturas da rede, a TechFit Morumbi oferece diversidade e tecnologia. Diferenciais:</p>
            <ul style="color: #ccc; line-height: 1.8; margin: 15px 0; padding-left: 20px;">
                <li>Piscina olímpica semicoberta</li>
                <li>Quadra poliesportiva coberta</li>
                <li>Área de crossfit com 500m²</li>
                <li>Circuit training com IA</li>
                <li>Estacionamento para 300 carros</li>
                <li>Academia kids com monitoria</li>
            </ul>
        `
    }
};

// Função para carregar dados da unidade
function carregarDetalhesUnidade() {
    const urlParams = new URLSearchParams(window.location.search);
    const unidadeId = urlParams.get('unidade') || 'paulista';
    
    const unidade = unidadesData[unidadeId];
    
    if (unidade) {
        // Atualizar dados da página
        document.getElementById('unidade-nome').textContent = unidade.nome;
        document.getElementById('unidade-endereco').textContent = unidade.endereco;
        document.getElementById('unidade-telefone').textContent = unidade.telefone;
        document.getElementById('unidade-email').textContent = unidade.email;
        document.getElementById('unidade-descricao').textContent = unidade.descricao;
        document.getElementById('unidade-sobre').innerHTML = unidade.sobre;
        
        // Atualizar imagem principal
        const imagemPrincipal = document.getElementById('unidade-imagem-principal');
        if (imagemPrincipal) {
            imagemPrincipal.src = unidade.imagem;
            imagemPrincipal.alt = unidade.nome;
        }
        
        // Atualizar título da página
        document.title = `${unidade.nome} - TechFit`;
        
        // Atualizar link do WhatsApp
        const whatsappLink = document.querySelector('a[href*="wa.me"]');
        if (whatsappLink) {
            const numero = unidade.telefone.replace(/\D/g, '');
            whatsappLink.href = `https://wa.me/55${numero}`;
        }
    }
}

// Funções do Modal da Galeria
function openModal(src) {
    const modal = document.getElementById('modal-galeria');
    const modalImg = document.getElementById('modal-image');
    
    modal.style.display = 'flex';
    modalImg.src = src;
    
    // Prevenir scroll do body quando modal estiver aberto
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    const modal = document.getElementById('modal-galeria');
    modal.style.display = 'none';
    
    // Restaurar scroll do body
    document.body.style.overflow = 'auto';
}

// Fechar modal ao clicar fora da imagem
document.getElementById('modal-galeria').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Menu mobile
document.querySelector('.hamburger-menu')?.addEventListener('click', function() {
    this.classList.toggle('active');
    document.querySelector('.main-navigation').classList.toggle('active');
});

// Fechar menu ao clicar em um link (mobile)
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', function() {
        document.querySelector('.hamburger-menu').classList.remove('active');
        document.querySelector('.main-navigation').classList.remove('active');
    });
});

// Inicializar quando a página carregar
document.addEventListener('DOMContentLoaded', function() {
    carregarDetalhesUnidade();
});