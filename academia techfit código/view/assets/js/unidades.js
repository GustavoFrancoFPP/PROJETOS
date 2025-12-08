// unidades.js - CORREÇÃO
function showUnitDetails(unidade) {
    // Redirecionar para a página de detalhes com o parâmetro da unidade
    window.location.href = `detalhes-unidade.html?unidade=${unidade}`;
}

// Mapa Interativo
let map;
let markers = [];

// Centros e zooms para cada estado
const stateViews = {
    'sp': { center: [-23.5505, -46.6333], zoom: 9 },
    'rj': { center: [-22.9068, -43.1729], zoom: 10 },
    'mg': { center: [-19.9167, -43.9345], zoom: 9 },
    'todas': { center: [-15.7797, -47.9297], zoom: 4 }
};

function initMap() {
    // Criar mapa com limites de zoom
    map = L.map('map', {
        minZoom: 4,
        maxZoom: 18,
        zoomControl: true
    }).setView(stateViews.todas.center, stateViews.todas.zoom);
    
    // Adicionar camada do mapa
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        className: 'map-tiles',
        minZoom: 3,
        maxZoom: 19
    }).addTo(map);
    
    // Adicionar marcadores das unidades
    addUnitMarkers();
}

function addUnitMarkers() {
    const unidades = [
        {
            coords: [-23.5630, -46.6520],
            title: "TechFit Paulista",
            info: "Av. Paulista, 1234 - Bela Vista, São Paulo - SP",
            cidade: "sp",
            unidadeId: "paulista"
        },
        {
            coords: [-22.9836, -43.1991],
            title: "TechFit Ipanema",
            info: "Rua Visconde de Pirajá, 550 - Ipanema, Rio de Janeiro - RJ",
            cidade: "rj",
            unidadeId: "ipanema"
        },
        {
            coords: [-19.9246, -43.9355],
            title: "TechFit Savassi", 
            info: "Rua Antônio de Albuquerque, 781 - Savassi, Belo Horizonte - MG",
            cidade: "mg",
            unidadeId: "savassi"
        },
        {
            coords: [-23.6220, -46.6990],
            title: "TechFit Morumbi",
            info: "Av. Jorge João Saad, 767 - Morumbi, São Paulo - SP",
            cidade: "sp",
            unidadeId: "morumbi"
        }
    ];
    
    // Limpar marcadores existentes
    markers.forEach(item => map.removeLayer(item.marker));
    markers = [];
    
    // Adicionar novos marcadores
    unidades.forEach((unidade, index) => {
        const marker = L.marker(unidade.coords)
            .addTo(map)
            .bindPopup(`
                <div style="min-width: 250px;">
                    <h3 class="map-popup-title">${unidade.title}</h3>
                    <p class="map-popup-address">${unidade.info}</p>
                    <div style="display: flex; gap: 10px;">
                        <a href="agendamento2.html" class="map-popup-btn">Agendar Aula</a>
                        <a href="detalhes-unidade.html?unidade=${unidade.unidadeId}" class="map-popup-btn" style="background: #666;">Detalhes</a>
                    </div>
                </div>
            `);
        
        markers.push({
            marker: marker,
            cidade: unidade.cidade
        });
    });
}

function filterMapMarkers(filtro) {
    // Mostrar/ocultar marcadores baseado no filtro
    markers.forEach(item => {
        if (filtro === 'todas' || item.cidade === filtro) {
            map.addLayer(item.marker);
        } else {
            map.removeLayer(item.marker);
        }
    });
    
    // Ajustar visualização para o estado/filtro selecionado
    if (stateViews[filtro]) {
        map.setView(stateViews[filtro].center, stateViews[filtro].zoom);
    }
}

// Inicialização quando a página carregar
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar mapa
    initMap();
    
    // Filtro de unidades por cidade
    const filtroBtns = document.querySelectorAll('.filtro-btn');
    const unidadeCards = document.querySelectorAll('.unidade-card');
    
    filtroBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove a classe active de todos os botões
            filtroBtns.forEach(b => b.classList.remove('active'));
            // Adiciona a classe active ao botão clicado
            this.classList.add('active');
            
            const filtro = this.getAttribute('data-filtro');
            
            // Filtrar cards
            unidadeCards.forEach(card => {
                if (filtro === 'todas') {
                    card.style.display = 'block';
                } else {
                    if (card.getAttribute('data-cidade') === filtro) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                }
            });
            
            // Filtrar marcadores no mapa
            filterMapMarkers(filtro);
        });
    });

    // Botões de detalhes - CORREÇÃO AQUI
    document.querySelectorAll('.acao-btn.detalhes').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const unidade = this.getAttribute('data-unidade');
            // Redirecionar diretamente em vez de chamar função
            window.location.href = `detalhes-unidade.html?unidade=${unidade}`;
        });
    });

    // Menu mobile
    const hamburgerMenu = document.querySelector('.hamburger-menu');
    if (hamburgerMenu) {
        hamburgerMenu.addEventListener('click', function() {
            this.classList.toggle('active');
            document.querySelector('.main-navigation').classList.toggle('active');
        });
    }
    
    // Fechar menu ao clicar em um link (mobile)
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function() {
            const hamburger = document.querySelector('.hamburger-menu');
            const navigation = document.querySelector('.main-navigation');
            if (hamburger) hamburger.classList.remove('active');
            if (navigation) navigation.classList.remove('active');
        });
    });
});