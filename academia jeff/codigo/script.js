/* =========================
   Navegação (onclick) — mantém padrão solicitado
   ========================= */
function mostrarsecao(id) {
  document.querySelectorAll('main .secao').forEach(s => s.classList.remove('active'));
  const el = document.getElementById(id);
  if (el) {
    el.classList.add('active');
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
  closeMobileMenu();
}

/* Mobile menu */
function toggleMobileMenu() {
  const menu = document.querySelector('.menu');
  menu.style.display = (menu.style.display === 'flex') ? 'none' : 'flex';
}
function closeMobileMenu() {
  if (window.innerWidth < 860) document.querySelector('.menu').style.display = 'none';
}

/* =========================
   HERO CARROSSEL (maior + indicadores)
   ========================= */
const heroSlides = Array.from(document.querySelectorAll('.hero-slide'));
const heroTitle = document.getElementById('heroTitle');
const heroDesc = document.getElementById('heroDesc');
const indicatorsContainer = document.getElementById('slideIndicators');

let heroIndex = 0;
let heroTimer = null;

// cria indicadores dinamicamente
function buildIndicators() {
  if (!indicatorsContainer) return;
  indicatorsContainer.innerHTML = '';
  heroSlides.forEach((_, i) => {
    const btn = document.createElement('button');
    btn.addEventListener('click', () => {
      heroIndex = i;
      showHero(heroIndex);
      resetHeroTimer();
    });
    indicatorsContainer.appendChild(btn);
  });
}

function updateIndicators() {
  const buttons = indicatorsContainer?.querySelectorAll('button') || [];
  buttons.forEach((b, i) => b.classList.toggle('active', i === heroIndex));
}

function showHero(index) {
  heroSlides.forEach((s, i) => s.classList.toggle('active', i === index));
  const slide = heroSlides[index];
  heroTitle.textContent = slide.dataset.title || '';
  heroDesc.textContent = slide.dataset.desc || '';
  updateIndicators();
}

function nextHero() {
  heroIndex = (heroIndex + 1) % heroSlides.length;
  showHero(heroIndex);
}
function prevHero() {
  heroIndex = (heroIndex - 1 + heroSlides.length) % heroSlides.length;
  showHero(heroIndex);
}

function startHeroAuto() {
  if (heroTimer) clearInterval(heroTimer);
  heroTimer = setInterval(nextHero, 4500);
}
function stopHeroAuto() {
  if (heroTimer) clearInterval(heroTimer);
}
function resetHeroTimer() {
  stopHeroAuto();
  startHeroAuto();
}

/* iniciar ao carregar */
document.addEventListener('DOMContentLoaded', function () {
  showHero(heroIndex);
  buildIndicators();
  startHeroAuto();
  // popula demo
  populateUnidades();
  populateProdutos();

  // pausa carrossel ao passar mouse
  document.querySelectorAll('.hero-carrossel').forEach(car => {
    car.addEventListener('mouseover', stopHeroAuto);
    car.addEventListener('mouseleave', startHeroAuto);
  });
});

/* =========================
   Dados de exemplo (popula Unidades e Produtos)
   ========================= */
const unidades = [
  { id: 1, nome: "TechFit Centro", endereco: "Av. Principal, 123", img: "assets/unit1.jpg", telefone: "(19) 9999-0001" },
  { id: 2, nome: "TechFit Shopping", endereco: "R. Comercio, 88", img: "assets/unit2.jpg", telefone: "(19) 9999-0002" },
  { id: 3, nome: "TechFit Norte", endereco: "Av. Norte, 456", img: "assets/unit3.jpg", telefone: "(19) 9999-0003" }
];

const produtosExemplo = [
  { id: 1, nome: "Garrafas Tech", preco: "R$ 39,90", img: "assets/prod1.jpg" },
  { id: 2, nome: "Mochila TechFit", preco: "R$ 129,90", img: "assets/prod2.jpg" },
  { id: 3, nome: "Luvas de Treino", preco: "R$ 49,90", img: "assets/prod3.jpg" }
];

function populateUnidades() {
  const container = document.getElementById('unidades-list');
  const select = document.getElementById('unidadeSelect');
  if (!container || !select) return;
  container.innerHTML = '';
  select.innerHTML = '<option value="">Escolha a unidade...</option>';
  unidades.forEach(u => {
    const div = document.createElement('div');
    div.className = 'unidade reveal';
    div.innerHTML = `
      <img src="${u.img}" alt="${u.nome}">
      <div>
        <h4>${u.nome}</h4>
        <div class="muted">${u.endereco}</div>
        <div class="muted">${u.telefone}</div>
      </div>
    `;
    container.appendChild(div);

    const opt = document.createElement('option');
    opt.value = u.id;
    opt.textContent = u.nome;
    select.appendChild(opt);
  });
}

function populateProdutos() {
  const grid = document.getElementById('produtos-grid');
  if (!grid) return;
  grid.innerHTML = '';
  produtosExemplo.forEach(p => {
    const card = document.createElement('div');
    card.className = 'produto reveal';
    card.innerHTML = `
      <img src="${p.img}" alt="${p.nome}">
      <h4>${p.nome}</h4>
      <div class="price">${p.preco}</div>
      <button class="btn primary" onclick="adicionarAoCarrinho(${p.id})">Comprar</button>
    `;
    grid.appendChild(card);
  });
}

function adicionarAoCarrinho(id) {
  alert('Produto adicionado (exemplo). ID: ' + id);
}

/* =========================
   Formulários: Agendamento, Contato, Login
   ========================= */
function handleAgendamento(e) {
  e.preventDefault();
  const nome = document.getElementById('nome').value.trim();
  const email = document.getElementById('email').value.trim();
  const unidadeId = document.getElementById('unidadeSelect').value;
  if (!nome || !email || !unidadeId) { alert('Preencha os campos obrigatórios.'); return false; }
  alert(`Agendamento recebido!\nNome: ${nome}\nE-mail: ${email}\nUnidade: ${unidadeId}`);
  e.target.reset();
  mostrarsecao('principal');
  return false;
}

function handleContato(e) {
  e.preventDefault();
  const nome = document.getElementById('nomeContato').value.trim();
  const email = document.getElementById('emailContato').value.trim();
  const msg = document.getElementById('mensagemContato').value.trim();
  if (!nome || !email || !msg) { alert('Preencha os campos obrigatórios.'); return false; }
  alert('Mensagem enviada! Obrigado, ' + nome);
  e.target.reset();
  mostrarsecao('principal');
  return false;
}

function handleLogin(e) {
  e.preventDefault();
  const email = document.getElementById('loginEmail').value.trim();
  const pass = document.getElementById('loginPass').value.trim();
  if (!email || !pass) { alert('Preencha e-mail e senha.'); return false; }
  alert('Login realizado (simulado): ' + email);
  mostrarsecao('principal');
  return false;
}

/* =========================
   IntersectionObserver para reveal (entrada suave)
   ========================= */
const observer = new IntersectionObserver(entries => {
  entries.forEach(entry => {
    if (entry.isIntersecting) entry.target.classList.add('reveal');
  });
}, { threshold: 0.12 });

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.secao, .card, .plano-card, .unidade, .produto').forEach(el => {
    observer.observe(el);
  });
});
