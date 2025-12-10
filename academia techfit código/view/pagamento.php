<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$extra_head = '<link rel="stylesheet" href="assets/css/pagamento.css">';
require_once __DIR__ . '/inc/header.php';

// Adiciona informação de debug (pode remover depois)
if (isset($_SESSION['user_id'])) {
    error_log("Usuário logado no pagamento: ID=" . $_SESSION['user_id']);
} else {
    error_log("AVISO: Usuário NÃO está logado ao acessar pagamento");
}

// Processa submissão do pagamento e grava no banco
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order = [];
    $order['nome'] = trim($_POST['nome'] ?? '');
    $order['email'] = trim($_POST['email'] ?? '');
    $order['cpf'] = trim($_POST['cpf'] ?? '');
    $order['telefone'] = trim($_POST['telefone'] ?? '');
    $order['endereco'] = trim($_POST['endereco'] ?? '');
    $order['cep'] = trim($_POST['cep'] ?? '');
    $order['numero'] = trim($_POST['numero'] ?? '');
    $order['complemento'] = trim($_POST['complemento'] ?? '');
    $order['bairro'] = trim($_POST['bairro'] ?? '');
    $order['cidade'] = trim($_POST['cidade'] ?? '');
    $order['estado'] = trim($_POST['estado'] ?? '');
    $order['metodo_pagamento'] = $_POST['metodo-pagamento'] ?? 'cartao';

    // Dados opcionais do cartão (não persistir sensíveis em produção)
    $order['numero_cartao'] = trim($_POST['numero-cartao'] ?? '');
    $order['nome_cartao'] = trim($_POST['nome-cartao'] ?? '');
    $order['validade'] = trim($_POST['validade'] ?? '');
    $order['cvv'] = trim($_POST['cvv'] ?? '');

    // Dados do carrinho vindos do frontend
    $pedido_json = $_POST['pedido_json'] ?? null; // JSON com itens, subtotal, frete, desconto, total, numero_pedido

    // Validação mínima
    if (empty($order['nome']) || empty($order['email']) || empty($order['cpf'])) {
        $erro = 'Por favor, preencha os campos obrigatórios.';
    } else {
        // Tenta gravar no banco
        try {
            require_once __DIR__ . '/Database.php';
            $db = new Database();
            $conn = $db->getConnection();

            // Cria tabela simples de pedidos se não existir
            $conn->exec("CREATE TABLE IF NOT EXISTS pedidos (
                id_pedido INT AUTO_INCREMENT PRIMARY KEY,
                numero_pedido VARCHAR(50) DEFAULT NULL,
                id_cliente INT DEFAULT NULL,
                dados_cliente JSON DEFAULT NULL,
                itens JSON DEFAULT NULL,
                subtotal DECIMAL(10,2) DEFAULT 0,
                frete DECIMAL(10,2) DEFAULT 0,
                desconto DECIMAL(10,2) DEFAULT 0,
                total DECIMAL(10,2) DEFAULT 0,
                metodo_pagamento VARCHAR(50) DEFAULT NULL,
                dados_pagamento JSON DEFAULT NULL,
                status VARCHAR(50) DEFAULT 'pendente',
                data_pedido DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $itens = null;
            $subtotal = 0;
            $frete = 0;
            $desconto = 0;
            $total = 0;
            $numeroPedido = null;

            if ($pedido_json) {
                $pedidoData = json_decode($pedido_json, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($pedidoData)) {
                    $itens = json_encode($pedidoData['itens'] ?? []);
                    $subtotal = floatval($pedidoData['subtotal'] ?? 0);
                    $frete = floatval($pedidoData['frete'] ?? 0);
                    $desconto = floatval($pedidoData['desconto'] ?? 0);
                    $total = floatval($pedidoData['total'] ?? 0);
                    $numeroPedido = $pedidoData['numeroPedido'] ?? ($pedidoData['numero_pedido'] ?? null);
                }
            }

            // Prepara dados cliente e pagamento
            $dadosCliente = json_encode([
                'nome' => $order['nome'],
                'email' => $order['email'],
                'cpf' => $order['cpf'],
                'telefone' => $order['telefone'],
                'endereco' => [
                    'cep' => $order['cep'],
                    'logradouro' => $order['endereco'],
                    'numero' => $order['numero'],
                    'complemento' => $order['complemento'],
                    'bairro' => $order['bairro'],
                    'cidade' => $order['cidade'],
                    'estado' => $order['estado']
                ]
            ]);

            $dadosPagamento = json_encode([
                'metodo' => $order['metodo_pagamento'],
                'cartao' => [
                    'numero' => $order['numero_cartao'] ? substr($order['numero_cartao'], -4) : null,
                    'nome' => $order['nome_cartao'],
                    'validade' => $order['validade']
                ]
            ]);

            // Pega o ID do cliente se estiver logado
            $idClienteLogado = $_SESSION['user_id'] ?? null;
            
            $stmt = $conn->prepare("INSERT INTO pedidos (numero_pedido, id_cliente, dados_cliente, itens, subtotal, frete, desconto, total, metodo_pagamento, dados_pagamento, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $status = 'pendente';
            $stmt->execute([
                $numeroPedido,
                $idClienteLogado,
                $dadosCliente,
                $itens,
                $subtotal,
                $frete,
                $desconto,
                $total,
                $order['metodo_pagamento'],
                $dadosPagamento,
                $status
            ]);

            $insertId = $conn->lastInsertId();
            error_log("Pedido criado: ID=$insertId, Cliente=$idClienteLogado");

            // NOVO: Registra cada produto/plano na tabela venda e pagamento
            // Tenta pegar o ID do cliente da sessão ou do POST
            $idCliente = $_SESSION['user_id'] ?? $_SESSION['id_cliente'] ?? null;
            
            if ($pedido_json && $idCliente) {
                $pedidoData = json_decode($pedido_json, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($pedidoData['itens']) && is_array($pedidoData['itens'])) {
                    $stmtVenda = $conn->prepare("INSERT INTO venda (id_cliente, id_produtos, quantidade, valor_total, data_venda) VALUES (?, ?, ?, ?, NOW())");
                    $stmtPagamento = $conn->prepare("INSERT INTO pagamento (id_cliente, id_planos, valor_pago, metodo_pagamento, status_pagamento, data_pagamento) VALUES (?, ?, ?, ?, ?, NOW())");
                    
                    $vendasInseridas = 0;
                    $planoInserido = false;
                    
                    foreach ($pedidoData['itens'] as $item) {
                        $tipoItem = $item['tipo'] ?? 'produto';
                        $idItem = $item['id'] ?? null;
                        $quantidade = $item['quantidade'] ?? 1;
                        $valorTotalItem = $item['preco'] * $quantidade;
                        
                        // Se for PLANO, insere na tabela pagamento
                        if ($tipoItem === 'plano' && $idItem) {
                            try {
                                $stmtPagamento->execute([
                                    $idCliente,
                                    $idItem,
                                    $valorTotalItem,
                                    $order['metodo_pagamento'],
                                    'pago' // Status confirmado
                                ]);
                                $planoInserido = true;
                                error_log("✓ Plano contratado: Cliente=$idCliente, Plano=$idItem, Valor=$valorTotalItem");
                                
                                // Limpa carrinho de planos após sucesso
                                $_SESSION['carrinho_planos'] = ['itens' => [], 'subtotal' => 0];
                            } catch (PDOException $e) {
                                error_log("✗ Erro ao inserir pagamento de plano: " . $e->getMessage());
                            }
                        }
                        // Se for PRODUTO, insere na tabela venda
                        elseif ($idItem && is_numeric($idItem) && intval($idItem) > 0) {
                            try {
                                $stmtVenda->execute([
                                    $idCliente,
                                    intval($idItem),
                                    $quantidade,
                                    $valorTotalItem
                                ]);
                                $vendasInseridas++;
                                error_log("✓ Venda registrada: Cliente=$idCliente, Produto=$idItem, Qtd=$quantidade, Valor=$valorTotalItem");
                            } catch (PDOException $e) {
                                error_log("✗ Erro ao inserir venda: " . $e->getMessage());
                            }
                        }
                    }
                    error_log("Total de vendas: $vendasInseridas produtos | Planos: " . ($planoInserido ? '1' : '0'));
                }
            } else {
                error_log("Não foi possível registrar vendas. ID Cliente: " . ($idCliente ?? 'null') . ", Pedido JSON: " . ($pedido_json ? 'presente' : 'ausente'));
            }

            if (session_status() == PHP_SESSION_NONE) session_start();
            $_SESSION['order'] = $order;
            $_SESSION['pedido_id'] = $insertId;

            // Redireciona para página de confirmação com id do pedido
            header('Location: confirmacao.php?pedido_id=' . $insertId);
            exit;

        } catch (Exception $e) {
            $erro = 'Erro ao processar pagamento: ' . $e->getMessage();
        }
    }
}
?>

<div class="pagamento-container">
    <div class="container">
        <div class="pagamento-header">
            <h1><i class="fas fa-credit-card"></i> Finalizar Pagamento</h1>
        </div>

        <div class="pagamento-content">
            <form method="post" action="pagamento.php">
                <div class="dados-pagamento">
                    <div class="form-section">
                        <h3><i class="fas fa-user"></i> Dados Pessoais</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="nome">Nome Completo *</label>
                                <input type="text" id="nome" name="nome" required value="<?php echo htmlspecialchars($_POST['nome'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="email">E-mail *</label>
                                <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="cpf">CPF *</label>
                                <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00" required value="<?php echo htmlspecialchars($_POST['cpf'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="telefone">Telefone *</label>
                                <input type="tel" id="telefone" name="telefone" placeholder="(11) 99999-9999" required value="<?php echo htmlspecialchars($_POST['telefone'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3><i class="fas fa-truck"></i> Endereço de Entrega</h3>
                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label for="cep">CEP *</label>
                                <input type="text" id="cep" name="cep" placeholder="00000-000" required value="<?php echo htmlspecialchars($_POST['cep'] ?? ''); ?>">
                            </div>
                            <div class="form-group full-width">
                                <label for="endereco">Endereço *</label>
                                <input type="text" id="endereco" name="endereco" required value="<?php echo htmlspecialchars($_POST['endereco'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="numero">Número *</label>
                                <input type="text" id="numero" name="numero" required value="<?php echo htmlspecialchars($_POST['numero'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="complemento">Complemento</label>
                                <input type="text" id="complemento" name="complemento" value="<?php echo htmlspecialchars($_POST['complemento'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="bairro">Bairro *</label>
                                <input type="text" id="bairro" name="bairro" required value="<?php echo htmlspecialchars($_POST['bairro'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="cidade">Cidade *</label>
                                <input type="text" id="cidade" name="cidade" required value="<?php echo htmlspecialchars($_POST['cidade'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="estado">Estado *</label>
                                <select id="estado" name="estado" required>
                                    <option value="">Selecione</option>
                                    <option value="SP" <?php echo (($_POST['estado'] ?? '') === 'SP') ? 'selected' : ''; ?>>São Paulo</option>
                                    <option value="RJ" <?php echo (($_POST['estado'] ?? '') === 'RJ') ? 'selected' : ''; ?>>Rio de Janeiro</option>
                                    <option value="MG" <?php echo (($_POST['estado'] ?? '') === 'MG') ? 'selected' : ''; ?>>Minas Gerais</option>
                                    <option value="ES" <?php echo (($_POST['estado'] ?? '') === 'ES') ? 'selected' : ''; ?>>Espírito Santo</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3><i class="fas fa-credit-card"></i> Método de Pagamento</h3>
                        <div class="metodos-pagamento">
                            <div class="metodo-opcao">
                                <input type="radio" id="cartao" name="metodo-pagamento" value="cartao" checked>
                                <label for="cartao">
                                    <i class="fas fa-credit-card"></i>
                                    Cartão de Crédito
                                </label>
                            </div>
                            <div class="metodo-opcao">
                                <input type="radio" id="pix" name="metodo-pagamento" value="pix">
                                <label for="pix">
                                    <i class="fas fa-qrcode"></i>
                                    PIX
                                </label>
                            </div>
                            <div class="metodo-opcao">
                                <input type="radio" id="boleto" name="metodo-pagamento" value="boleto">
                                <label for="boleto">
                                    <i class="fas fa-barcode"></i>
                                    Boleto Bancário
                                </label>
                            </div>
                        </div>

                        <!-- Formulário de Cartão -->
                        <div class="form-cartao" id="form-cartao" style="display: block;">
                            <div class="form-grid">
                                <div class="form-group full-width">
                                    <label for="numero-cartao">Número do Cartão *</label>
                                    <input type="text" id="numero-cartao" name="numero-cartao" placeholder="0000 0000 0000 0000" maxlength="19" value="<?php echo htmlspecialchars($_POST['numero-cartao'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="nome-cartao">Nome no Cartão *</label>
                                    <input type="text" id="nome-cartao" name="nome-cartao" value="<?php echo htmlspecialchars($_POST['nome-cartao'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="validade">Validade *</label>
                                    <input type="text" id="validade" name="validade" placeholder="MM/AA" maxlength="5" value="<?php echo htmlspecialchars($_POST['validade'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="cvv">CVV *</label>
                                    <input type="text" id="cvv" name="cvv" placeholder="000" maxlength="3" value="<?php echo htmlspecialchars($_POST['cvv'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Formulário PIX -->
                        <div id="form-pix" style="display: none; padding: 20px; text-align: center;">
                            <div style="background: #fff; color: #121212; padding: 30px; border-radius: 10px; margin: 20px 0;">
                                <i class="fas fa-qrcode" style="font-size: 100px; color: #00f0e1; margin-bottom: 20px;"></i>
                                <h3>Pagamento via PIX</h3>
                                <p>Após gerar o QR Code, você terá 15 minutos para realizar o pagamento.</p>
                                <div style="background: #f0f0f0; padding: 15px; border-radius: 5px; margin: 20px 0;">
                                    <p style="font-size: 12px; color: #666;">Código PIX será gerado após confirmar o pedido</p>
                                </div>
                            </div>
                        </div>

                        <!-- Formulário Boleto -->
                        <div id="form-boleto" style="display: none; padding: 20px; text-align: center;">
                            <div style="background: #fff; color: #121212; padding: 30px; border-radius: 10px; margin: 20px 0;">
                                <i class="fas fa-barcode" style="font-size: 100px; color: #00f0e1; margin-bottom: 20px;"></i>
                                <h3>Pagamento via Boleto Bancário</h3>
                                <p>O boleto será gerado e enviado para seu e-mail.</p>
                                <div style="background: #f0f0f0; padding: 15px; border-radius: 5px; margin: 20px 0;">
                                    <p style="font-size: 12px; color: #666;">Vencimento: 3 dias úteis</p>
                                    <p style="font-size: 12px; color: #666;">O pedido será processado após a confirmação do pagamento</p>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="termos-condicoes">
                        <input type="checkbox" id="termos" name="termos" required>
                        <label for="termos">Concordo com os <a href="#">Termos e Condições</a></label>
                    </div>

                    <?php if (!empty($erro)): ?>
                        <div style="color: red; font-weight: bold; margin-bottom: 10px;"><?php echo htmlspecialchars($erro); ?></div>
                    <?php endif; ?>

                    <button class="btn-confirmar-pagamento" id="btnConfirmarPagamento" type="submit">
                        <i class="fas fa-lock"></i>
                        Confirmar Pagamento
                    </button>

                </div>
            </form>
        </div>
    </div>
</div>

<script src="assets/js/pagamento.js"></script>
<script>
// Inicializar sistema de pagamento
document.addEventListener('DOMContentLoaded', function() {
    // Configurar alternância de métodos de pagamento
    document.querySelectorAll('input[name="metodo-pagamento"]').forEach(radio => {
        radio.addEventListener('change', function(e) {
            const metodo = e.target.value;
            
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
        });
    });
    
    // Inicializar com cartão selecionado
    document.getElementById('form-cartao').style.display = 'block';
    
    console.log('✅ Sistema de pagamento inicializado');
});
</script>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
