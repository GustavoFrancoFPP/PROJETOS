<?php
$extra_head = '<link rel="stylesheet" href="pagamento.css">';
require_once __DIR__ . '/inc/header.php';

// Processa submissão do pagamento (simplesmente armazena em sessão e redireciona)
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
    // Dados do cartão (não persistir em produção sem tokenização)
    $order['numero_cartao'] = trim($_POST['numero-cartao'] ?? '');
    $order['nome_cartao'] = trim($_POST['nome-cartao'] ?? '');
    $order['validade'] = trim($_POST['validade'] ?? '');
    $order['cvv'] = trim($_POST['cvv'] ?? '');

    // Validação mínima
    if (empty($order['nome']) || empty($order['email']) || empty($order['cpf'])) {
        $erro = 'Por favor, preencha os campos obrigatórios.';
    } else {
        if (session_status() == PHP_SESSION_NONE) session_start();
        $_SESSION['order'] = $order;
        header('Location: confirmacao.php');
        exit;
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

                        <div class="form-cartao" id="form-cartao">
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

<?php require_once __DIR__ . '/inc/footer.php'; ?>
