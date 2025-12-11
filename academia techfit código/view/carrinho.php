<?php
/**
 * Carrinho de Compras - TechFit
 * Integra produtos (localStorage) e planos (SESSION)
 */
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Inicializa carrinho de planos na sessão
if (!isset($_SESSION['carrinho_planos'])) {
    $_SESSION['carrinho_planos'] = [
        'itens' => [],
        'subtotal' => 0
    ];
}

$planosCarrinho = $_SESSION['carrinho_planos'];

// Debug: Log do estado do carrinho
error_log("📦 Carrinho.php - Session ID: " . session_id());
error_log("📦 Carrinho.php - Itens: " . count($planosCarrinho['itens']));
error_log("📦 Carrinho.php - Subtotal: " . $planosCarrinho['subtotal']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho de Compras - TECHFIT</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    <link rel="stylesheet" href="assets/css/carrinho.css">
    <link rel="icon" type="image/x-icon" href="assets/images/imagens/favicon.ico">
    <style>
        /* Estilos adicionais para planos no carrinho */
        .plano-item {
            background: linear-gradient(135deg, #1e1e1e 0%, #2a2a2a 100%);
            border: 2px solid #00f0e1;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 4px 15px rgba(0, 240, 225, 0.1);
        }

        .plano-item .item-info {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .plano-badge {
            display: inline-block;
            background: #00f0e1;
            color: #121212;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .plano-preco-destaque {
            font-size: 28px;
            color: #00f0e1;
            font-weight: bold;
        }

        .section-divider {
            border-top: 2px solid #2a2a2a;
            margin: 20px 0;
            padding-top: 20px;
        }

        .section-title {
            color: #00f0e1;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>
</head>
<body>
    <header class="techfit-header">
        <div class="header-container">
            <a href="inicio.html" class="header-logo">
                <img src="assets/images/imagens/WhatsApp Image 2025-10-02 at 15.15.22.jpeg" 
                     alt="TechFit - Academia Inteligente" 
                     class="logo-image">
                <div class="logo-text">TECH<span>FIT</span></div>
            </a>

            <nav class="main-navigation">
                <ul class="nav-links">
                    <li><a href="inicio.html" class="nav-link">Início</a></li>
                    <li><a href="pagina_1.html" class="nav-link">Academias</a></li>
                    <li><a href="produtos_loja.php" class="nav-link">Produtos</a></li>
                    <li><a href="agendamento.php" class="nav-link">Agendamento</a></li>
                    <li><a href="suporte.html" class="nav-link">Suporte</a></li>
                </ul>

                <div class="header-cta">
                    <a href="login.php" class="cta-button">Área do Aluno</a>
                </div>
            </nav>

            <button class="hamburger-menu" aria-label="Menu">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>
        </div>
    </header>

    <main class="carrinho-container">
        <div class="container">
            <div class="carrinho-header">
                <h1><i class="fas fa-shopping-cart"></i> Meu Carrinho</h1>
                <div class="carrinho-steps">
                    <div class="step active">
                        <span class="step-number">1</span>
                        <span class="step-text">Carrinho</span>
                    </div>
                    <div class="step">
                        <span class="step-number">2</span>
                        <span class="step-text">Pagamento</span>
                    </div>
                    <div class="step">
                        <span class="step-number">3</span>
                        <span class="step-text">Confirmação</span>
                    </div>
                </div>
            </div>

            <div class="carrinho-content">
                <div class="itens-carrinho">
                    <!-- Seção de Planos (PHP) -->
                    <?php if (count($planosCarrinho['itens']) > 0): ?>
                        <div class="section-title">
                            <i class="fas fa-crown"></i>
                            Planos de Assinatura
                        </div>
                        <?php foreach ($planosCarrinho['itens'] as $plano): ?>
                            <div class="plano-item" data-plano-id="<?php echo htmlspecialchars($plano['id']); ?>">
                                <div class="item-info">
                                    <span class="plano-badge"><i class="fas fa-star"></i> Plano Premium</span>
                                    <h4 class="item-nome"><?php echo htmlspecialchars($plano['nome']); ?></h4>
                                    <p class="item-descricao"><?php echo htmlspecialchars($plano['descricao']); ?></p>
                                    <p class="plano-preco-destaque">R$ <?php echo number_format($plano['preco'], 2, ',', '.'); ?>/mês</p>
                                    <div class="item-controles">
                                        <button class="remover-item" onclick="removerPlano('<?php echo htmlspecialchars($plano['id']); ?>')">
                                            <i class="fas fa-trash"></i> Remover Plano
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="section-divider"></div>
                    <?php endif; ?>

                    <!-- Seção de Produtos (JavaScript/localStorage) -->
                    <div class="section-title" id="produtosTitle" style="display: none;">
                        <i class="fas fa-shopping-bag"></i>
                        Produtos
                    </div>
                    
                    <div class="carrinho-vazio" id="carrinhoVazio" style="<?php echo count($planosCarrinho['itens']) > 0 ? 'display: none;' : ''; ?>">
                        <i class="fas fa-shopping-cart"></i>
                        <h3>Seu carrinho está vazio</h3>
                        <p>Adicione produtos ou escolha um plano da TECHFIT!</p>
                        <div style="display: flex; gap: 10px; justify-content: center; margin-top: 15px;">
                            <a href="produtos_loja.php" class="btn-primary">Ver Produtos</a>
                            <a href="planos.php" class="btn-primary" style="background: linear-gradient(135deg, #00f0e1 0%, #00c9b8 100%);">Ver Planos</a>
                        </div>
                    </div>

                    <div class="lista-itens" id="listaItens" style="display: none;"></div>
                </div>

                <div class="resumo-pedido">
                    <div class="resumo-card">
                        <h3>Resumo do Pedido</h3>
                        
                        <div class="resumo-linha">
                            <span>Planos</span>
                            <span id="subtotalPlanos">R$ <?php echo number_format($planosCarrinho['subtotal'], 2, ',', '.'); ?></span>
                        </div>
                        
                        <div class="resumo-linha">
                            <span>Produtos</span>
                            <span id="subtotalProdutos">R$ 0,00</span>
                        </div>
                        
                        <div class="resumo-linha">
                            <span>Frete</span>
                            <span id="frete">R$ 0,00</span>
                        </div>
                        
                        <div class="resumo-linha">
                            <span>Desconto</span>
                            <span id="desconto">- R$ 0,00</span>
                        </div>
                        
                        <div class="resumo-linha total">
                            <strong>Total</strong>
                            <strong id="total">R$ <?php echo number_format($planosCarrinho['subtotal'], 2, ',', '.'); ?></strong>
                        </div>

                        <div class="cupom-desconto">
                            <input type="text" id="inputCupom" placeholder="Código do cupom">
                            <button id="aplicarCupom">Aplicar</button>
                        </div>

                        <button class="btn-finalizar" id="btnFinalizar">
                            Finalizar Compra
                        </button>

                        <div class="opcoes-pagamento">
                            <p>Pagamento seguro com:</p>
                            <div class="bandeiras">
                                <i class="fab fa-cc-visa"></i>
                                <i class="fab fa-cc-mastercard"></i>
                                <i class="fab fa-cc-amex"></i>
                                <i class="fab fa-cc-paypal"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container footer-container">
            <div class="footer-brand">
                <a href="#" class="logo">
                     <img src="assets/images/imagens/WhatsApp Image 2025-10-02 at 15.15.22.jpeg" alt="TechFit Logo" class="logo-img">
                     <span>TECHFIT</span>
                </a>
                <p>Sua Academia do Futuro!</p>
            </div>
            <div class="footer-contact">
                 <a href="#" class="contact-link">
                    <i class="fab fa-instagram"></i>
                    <span>TECHFIT_OFC</span>
                </a>
                <a href="#" class="contact-link">
                    <i class="fab fa-whatsapp"></i>
                    <span>(19) 99936 - 4328</span>
                </a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 TECHFIT. Todos os direitos reservados</p>
        </div>
    </footer>

    <script>
        /**
         * Remove plano do carrinho via AJAX
         */
        async function removerPlano(planoId) {
            try {
                const response = await fetch('CarrinhoController.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'remover_plano',
                        id: planoId
                    })
                });

                const result = await response.json();
                
                if (result.sucesso) {
                    alert('Plano removido do carrinho!');
                    window.location.reload();
                } else {
                    alert(result.mensagem || 'Erro ao remover plano');
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao remover plano. Tente novamente.');
            }
        }
    </script>
    <script>
        // Variável global para prevenir inicialização automática do carrinho.js
        window.CARRINHO_PHP_MODE = true;
    </script>
    <script src="assets/js/carrinho.js"></script>
    <script src="assets/js/header-carrinho-simples.js"></script>
    <script>
        // Integração PHP + JavaScript para carrinho
        document.addEventListener('DOMContentLoaded', function() {
            const temPlanos = <?php echo count($planosCarrinho['itens']) > 0 ? 'true' : 'false'; ?>;
            const subtotalPlanosPhp = <?php echo $planosCarrinho['subtotal']; ?>;
            
            console.log('📦 Carrinho PHP Mode ativo');
            console.log('📦 Planos na sessão:', temPlanos);
            console.log('📦 Subtotal planos: R$', subtotalPlanosPhp);
            
            // Renderiza produtos do localStorage (se houver)
            const produtosLocalStorage = JSON.parse(localStorage.getItem('carrinhoTechFit')) || [];
            console.log('📦 Produtos no localStorage:', produtosLocalStorage.length);
            
            // Função para renderizar produtos
            function renderizarProdutos() {
                const produtosAtuais = JSON.parse(localStorage.getItem('carrinhoTechFit')) || [];
                
                if (produtosAtuais.length > 0) {
                    document.getElementById('produtosTitle').style.display = 'flex';
                    document.getElementById('listaItens').style.display = 'block';
                    
                    const listaItens = document.getElementById('listaItens');
                    if (listaItens) {
                        listaItens.innerHTML = produtosAtuais.map(item => `
                            <div class="item-carrinho" data-id="${item.id}">
                                <img src="${item.imagem}" alt="${item.nome}" class="item-imagem" 
                                     onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgdmlld0JveD0iMCAwIDEwMCAxMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIiBmaWxsPSIjMkEyQTJBIi8+Cjx0ZXh0IHg9IjUwIiB5PSI1MCIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjE0IiBmaWxsPSIjN0E3QTdBIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iMC4zNWVtIj5TZW0gSW1hZ2VtPC90ZXh0Pgo8L3N2Zz4K'">
                                <div class="item-info">
                                    <h4 class="item-nome">${item.nome}</h4>
                                    <p class="item-descricao">${item.descricao || 'Produto premium TECHFIT'}</p>
                                    <p class="item-preco">R$ ${item.preco.toFixed(2)}</p>
                                    <div class="item-controles">
                                        <div class="quantidade-controle">
                                            <button class="quantidade-btn diminuir" onclick="alterarQuantidadeProduto('${item.id}', -1)">-</button>
                                            <span class="quantidade">${item.quantidade}</span>
                                            <button class="quantidade-btn aumentar" onclick="alterarQuantidadeProduto('${item.id}', 1)">+</button>
                                        </div>
                                        <button class="remover-item" onclick="removerProduto('${item.id}')">
                                            <i class="fas fa-trash"></i> Remover
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `).join('');
                    }
                } else {
                    document.getElementById('produtosTitle').style.display = 'none';
                    document.getElementById('listaItens').style.display = 'none';
                }
                
                // Atualiza totais
                atualizarTotais();
            }
            
            // Função para remover produto
            window.removerProduto = function(id) {
                let produtos = JSON.parse(localStorage.getItem('carrinhoTechFit')) || [];
                produtos = produtos.filter(item => item.id !== id);
                localStorage.setItem('carrinhoTechFit', JSON.stringify(produtos));
                renderizarProdutos();
            };
            
            // Função para alterar quantidade
            window.alterarQuantidadeProduto = function(id, mudanca) {
                let produtos = JSON.parse(localStorage.getItem('carrinhoTechFit')) || [];
                const produto = produtos.find(item => item.id === id);
                
                if (produto) {
                    produto.quantidade += mudanca;
                    
                    if (produto.quantidade <= 0) {
                        produtos = produtos.filter(item => item.id !== id);
                    }
                    
                    localStorage.setItem('carrinhoTechFit', JSON.stringify(produtos));
                    renderizarProdutos();
                }
            };
            
            // Função para atualizar totais
            function atualizarTotais() {
                const produtosAtuais = JSON.parse(localStorage.getItem('carrinhoTechFit')) || [];
                const subtotalProdutos = produtosAtuais.reduce((sum, item) => sum + (item.preco * item.quantidade), 0);
                const frete = subtotalProdutos > 0 ? 15.90 : 0;
                const totalGeral = subtotalPlanosPhp + subtotalProdutos + frete;
                
                document.getElementById('subtotalProdutos').textContent = `R$ ${subtotalProdutos.toFixed(2).replace('.', ',')}`;
                document.getElementById('frete').textContent = `R$ ${frete.toFixed(2).replace('.', ',')}`;
                document.getElementById('total').textContent = `R$ ${totalGeral.toFixed(2).replace('.', ',')}`;
                
                // Atualiza botão finalizar
                atualizarBotaoFinalizar();
                
                // Controla visibilidade do carrinho vazio
                const carrinhoVazio = document.getElementById('carrinhoVazio');
                if (temPlanos || produtosAtuais.length > 0) {
                    if (carrinhoVazio) carrinhoVazio.style.display = 'none';
                } else {
                    if (carrinhoVazio) carrinhoVazio.style.display = 'block';
                }
            }
            
            // Renderiza inicialmente
            renderizarProdutos();
            
            // Função para atualizar estado do botão
            function atualizarBotaoFinalizar() {
                const planosPhp = <?php echo json_encode($planosCarrinho['itens']); ?>;
                const produtosJs = JSON.parse(localStorage.getItem('carrinhoTechFit')) || [];
                const btnFinalizar = document.getElementById('btnFinalizar');
                
                const temItens = planosPhp.length > 0 || produtosJs.length > 0;
                
                if (temItens) {
                    btnFinalizar.disabled = false;
                    btnFinalizar.style.opacity = '1';
                    btnFinalizar.style.cursor = 'pointer';
                    
                    // Calcula total para exibir no botão
                    const subtotalProdutos = produtosJs.reduce((sum, item) => sum + (item.preco * item.quantidade), 0);
                    const frete = subtotalProdutos > 0 ? 15.90 : 0;
                    const totalGeral = subtotalPlanosPhp + subtotalProdutos + frete;
                    
                    btnFinalizar.textContent = `Finalizar Compra - R$ ${totalGeral.toFixed(2).replace('.', ',')}`;
                } else {
                    btnFinalizar.disabled = true;
                    btnFinalizar.style.opacity = '0.5';
                    btnFinalizar.style.cursor = 'not-allowed';
                    btnFinalizar.textContent = 'Carrinho Vazio';
                }
            }
            
            // Override do botão Finalizar Compra para incluir planos
            const btnFinalizar = document.getElementById('btnFinalizar');
            if (btnFinalizar) {
                // Remove listeners antigos
                const novoBotao = btnFinalizar.cloneNode(true);
                btnFinalizar.parentNode.replaceChild(novoBotao, btnFinalizar);
                
                // Adiciona novo listener que considera planos + produtos
                novoBotao.addEventListener('click', function() {
                    const planosPhp = <?php echo json_encode($planosCarrinho['itens']); ?>;
                    const produtosJs = JSON.parse(localStorage.getItem('carrinhoTechFit')) || [];
                    
                    // Verifica se há itens (planos OU produtos)
                    if (planosPhp.length === 0 && produtosJs.length === 0) {
                        alert('Adicione produtos ou escolha um plano primeiro!');
                        return;
                    }
                    
                    // Combina planos + produtos para o pedido
                    const todosItens = [...planosPhp, ...produtosJs];
                    const subtotalProdutos = produtosJs.reduce((sum, item) => sum + (item.preco * item.quantidade), 0);
                    const frete = subtotalProdutos > 0 ? 15.90 : 0;
                    const totalGeral = subtotalPlanosPhp + subtotalProdutos + frete;
                    
                    // Prepara dados do pedido
                    const dadosPedido = {
                        itens: todosItens,
                        subtotal: subtotalPlanosPhp + subtotalProdutos,
                        frete: frete,
                        desconto: 0,
                        total: totalGeral,
                        numeroPedido: 'TECH' + Date.now(),
                        data: new Date().toISOString()
                    };
                    
                    // Salva no localStorage para página de pagamento
                    localStorage.setItem('dadosCompraTechFit', JSON.stringify(dadosPedido));
                    
                    console.log('✅ Finalizando compra:', dadosPedido);
                    
                    // Redireciona para pagamento.html
                    window.location.href = 'pagamento.html';
                });
                
                // Atualiza botão quando o carrinho mudar
                atualizarBotaoFinalizar();
            }
        });
    </script>
</body>
</html>
