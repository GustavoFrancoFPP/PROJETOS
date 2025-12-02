<?php
require_once 'UsuarioController.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado e é aluno
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['tipo_usuario'] !== 'cliente') {
    header('Location: login.php');
    exit;
}

// Dados atuais do aluno
$nome = $_SESSION['user_nome'] ?? '';
$email = $_SESSION['user_email'] ?? '';
$idAluno = $_SESSION['user_id'] ?? '';

// Buscar dados completos do aluno no banco
try {
    require_once 'Database.php';
    $db = new Database();
    $conn = $db->getConnection();
    $stmt = $conn->prepare('SELECT * FROM cliente WHERE id_cliente = ?');
    $stmt->execute([$idAluno]);
    $aluno = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $aluno = [];
}

// Preenche variáveis
$telefone = $aluno['telefone'] ?? '';
$endereco = $aluno['endereco'] ?? '';
$genero = $aluno['genero'] ?? '';
$cpf = $aluno['cpf'] ?? '';

$mensagem = '';

// Atualização de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'editar') {
    $novoNome = trim($_POST['nome'] ?? '');
    $novoEmail = trim($_POST['email'] ?? '');
    $novoTelefone = trim($_POST['telefone'] ?? '');
    $novoEndereco = trim($_POST['endereco'] ?? '');
    $novoGenero = $_POST['genero'] ?? '';

    if (empty($novoNome) || empty($novoEmail) || empty($novoTelefone) || empty($novoEndereco) || empty($novoGenero)) {
        $mensagem = 'Preencha todos os campos.';
    } elseif (!filter_var($novoEmail, FILTER_VALIDATE_EMAIL)) {
        $mensagem = 'E-mail inválido.';
    } else {
        try {
            $stmt = $conn->prepare('UPDATE cliente SET nome_cliente = ?, email = ?, telefone = ?, endereco = ?, genero = ? WHERE id_cliente = ?');
            $stmt->execute([$novoNome, $novoEmail, $novoTelefone, $novoEndereco, $novoGenero, $idAluno]);
            // Atualiza sessão
            $_SESSION['user_nome'] = $novoNome;
            $_SESSION['user_email'] = $novoEmail;
            $mensagem = 'Perfil atualizado com sucesso!';
            // Atualiza variáveis locais
            $nome = $novoNome;
            $email = $novoEmail;
            $telefone = $novoTelefone;
            $endereco = $novoEndereco;
            $genero = $novoGenero;
        } catch (Exception $e) {
            $mensagem = 'Erro ao atualizar perfil.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TECHFIT - Editar Perfil</title>
    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .edit-container {
            max-width: 500px;
            margin: 60px auto;
            background: var(--cor-card-escuro);
            border-radius: 15px;
            padding: 40px 30px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }
        .edit-container h1 {
            color: var(--cor-ciano-principal);
            text-align: center;
            margin-bottom: 25px;
        }
        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            color: var(--cor-texto-primario);
            font-weight: 500;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.2);
            background: rgba(255,255,255,0.1);
            color: var(--cor-texto-primario);
            font-size: 15px;
        }
        .form-group input[readonly] {
            background: rgba(255,255,255,0.05);
            color: var(--cor-texto-secundario);
        }
        .btn-salvar {
            background: linear-gradient(135deg, var(--cor-ciano-principal), #00d4ff);
            color: var(--cor-fundo-escuro);
            padding: 15px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
        }
        .btn-voltar {
            display: block;
            margin: 20px auto 0;
            text-align: center;
            color: var(--cor-ciano-principal);
            text-decoration: none;
            font-weight: 500;
        }
        .mensagem {
            text-align: center;
            font-weight: bold;
            margin-bottom: 18px;
            color: #fff;
            background: rgba(0,209,178,0.15);
            border-radius: 6px;
            padding: 10px;
        }
    </style>
</head>
<body>
    <header class="techfit-header">
        <div class="header-container">
            <a href="dashboard_aluno.php" class="header-logo">
                <div class="logo-text">TECH<span>FIT</span></div>
            </a>
        </div>
    </header>
    <main>
        <div class="edit-container">
            <h1>Editar Perfil</h1>
            <?php if ($mensagem): ?>
                <div class="mensagem"> <?php echo htmlspecialchars($mensagem); ?> </div>
            <?php endif; ?>
            <form method="POST" action="editar_perfil.php">
                <input type="hidden" name="acao" value="editar">
                <div class="form-group">
                    <label for="nome">Nome:</label>
                    <input id="nome" name="nome" type="text" required value="<?php echo htmlspecialchars($nome); ?>">
                </div>
                <div class="form-group">
                    <label for="email">E-mail:</label>
                    <input id="email" name="email" type="email" required value="<?php echo htmlspecialchars($email); ?>">
                </div>
                <div class="form-group">
                    <label for="telefone">Telefone:</label>
                    <input id="telefone" name="telefone" type="text" required value="<?php echo htmlspecialchars($telefone); ?>">
                </div>
                <div class="form-group">
                    <label for="endereco">Endereço:</label>
                    <input id="endereco" name="endereco" type="text" required value="<?php echo htmlspecialchars($endereco); ?>">
                </div>
                <div class="form-group">
                    <label for="genero">Gênero:</label>
                    <select id="genero" name="genero" required>
                        <option value="">Selecione...</option>
                        <option value="Masculino" <?php echo ($genero === 'Masculino') ? 'selected' : ''; ?>>Masculino</option>
                        <option value="Feminino" <?php echo ($genero === 'Feminino') ? 'selected' : ''; ?>>Feminino</option>
                        <option value="Outro" <?php echo ($genero === 'Outro') ? 'selected' : ''; ?>>Outro</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="cpf">CPF:</label>
                    <input id="cpf" name="cpf" type="text" value="<?php echo htmlspecialchars($cpf); ?>" readonly>
                </div>
                <button type="submit" class="btn-salvar">Salvar Alterações</button>
            </form>
            <a href="dashboard_aluno.php" class="btn-voltar"><i class="fas fa-arrow-left"></i> Voltar ao Dashboard</a>
        </div>
    </main>
</body>
</html>
