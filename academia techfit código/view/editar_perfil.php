<?php
require_once 'AlunoDAO.php';
require_once __DIR__ . '/../config/Connection.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$idAluno = $_SESSION['user_id'] ?? '';
$alunoDAO = new AlunoDAO();
$infoAluno = $alunoDAO->buscarAlunoPorId($idAluno);

// Processar atualização
$mensagem = '';
$mensagem_tipo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $endereco = $_POST['endereco'] ?? '';
    
    try {
        $conn = Connection::getInstance();
        // NOTA: coluna data_atualizacao não existe na tabela cliente
        $sql = "UPDATE cliente SET 
                nome_cliente = :nome,
                email = :email,
                telefone = :telefone,
                endereco = :endereco
                WHERE id_cliente = :id";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':nome' => $nome,
            ':email' => $email,
            ':telefone' => $telefone,
            ':endereco' => $endereco,
            ':id' => $idAluno
        ]);
        
        $mensagem = 'Perfil atualizado com sucesso!';
        $mensagem_tipo = 'success';
        
        // Atualizar dados na sessão
        $_SESSION['user_nome'] = $nome;
        $_SESSION['user_email'] = $email;
        
        // Recarregar dados
        $infoAluno = $alunoDAO->buscarAlunoPorId($idAluno);
        
    } catch (PDOException $e) {
        $mensagem = 'Erro ao atualizar perfil: ' . $e->getMessage();
        $mensagem_tipo = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil - TECHFIT</title>
    <link rel="stylesheet" href="assets/css/https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root {
            --cor-ciano-principal: #00F0E1;
            --cor-fundo-escuro: #0f1525;
            --cor-card-escuro: rgba(20, 25, 40, 0.8);
            --cor-texto-primario: #ffffff;
            --cor-texto-secundario: #b0b7d9;
            --cor-verde: #2ecc71;
            --cor-vermelho: #e74c3c;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--cor-fundo-escuro);
            color: var(--cor-texto-primario);
            margin: 0;
            padding: 0;
        }
        
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }
        
        .profile-card {
            background: var(--cor-card-escuro);
            backdrop-filter: blur(15px);
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .profile-header h1 {
            color: var(--cor-ciano-principal);
            margin-bottom: 10px;
        }
        
        .profile-header p {
            color: var(--cor-texto-secundario);
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--cor-texto-secundario);
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: var(--cor-texto-primario);
            font-size: 16px;
            font-family: 'Poppins', sans-serif;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--cor-ciano-principal);
            box-shadow: 0 0 0 2px rgba(0, 240, 225, 0.2);
        }
        
        .btn {
            background: linear-gradient(135deg, var(--cor-ciano-principal), #00d4ff);
            color: var(--cor-fundo-escuro);
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 16px;
            width: 100%;
            font-family: 'Poppins', sans-serif;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 209, 178, 0.4);
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .alert-success {
            background: rgba(46, 204, 113, 0.1);
            border: 1px solid var(--cor-verde);
            color: var(--cor-verde);
        }
        
        .alert-error {
            background: rgba(231, 76, 60, 0.1);
            border: 1px solid var(--cor-vermelho);
            color: var(--cor-vermelho);
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: var(--cor-ciano-principal);
            text-decoration: none;
            font-size: 14px;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .readonly-field {
            background: rgba(255, 255, 255, 0.03);
            color: var(--cor-texto-secundario);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="profile-card">
            <div class="profile-header">
                <h1><i class="fas fa-user-edit"></i> Editar Perfil</h1>
                <p>Atualize suas informações pessoais</p>
            </div>
            
            <?php if ($mensagem): ?>
                <div class="alert alert-<?php echo $mensagem_tipo; ?>">
                    <?php echo $mensagem; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="nome"><i class="fas fa-user"></i> Nome Completo</label>
                    <input type="text" id="nome" name="nome" class="form-control" 
                           value="<?php echo htmlspecialchars($infoAluno['nome_cliente'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> E-mail</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           value="<?php echo htmlspecialchars($infoAluno['email'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="telefone"><i class="fas fa-phone"></i> Telefone</label>
                    <input type="text" id="telefone" name="telefone" class="form-control" 
                           value="<?php echo htmlspecialchars($infoAluno['telefone'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="endereco"><i class="fas fa-map-marker-alt"></i> Endereço</label>
                    <input type="text" id="endereco" name="endereco" class="form-control" 
                           value="<?php echo htmlspecialchars($infoAluno['endereco'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="cpf"><i class="fas fa-id-card"></i> CPF</label>
                    <input type="text" id="cpf" class="form-control readonly-field" 
                           value="<?php echo htmlspecialchars($infoAluno['cpf'] ?? ''); ?>" readonly>
                    <small style="color: var(--cor-texto-secundario); font-size: 12px;">
                        CPF não pode ser alterado
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="genero"><i class="fas fa-venus-mars"></i> Gênero</label>
                    <input type="text" id="genero" class="form-control readonly-field" 
                           value="<?php echo htmlspecialchars($infoAluno['genero'] ?? ''); ?>" readonly>
                </div>
                
                <div class="form-group">
                    <label for="data_cadastro"><i class="fas fa-calendar-plus"></i> Data de Cadastro</label>
                    <input type="text" id="data_cadastro" class="form-control readonly-field" 
                           value="<?php echo isset($infoAluno['data_cadastro']) ? date('d/m/Y H:i', strtotime($infoAluno['data_cadastro'])) : 'Não informada'; ?>" readonly>
                </div>
                
                <button type="submit" class="btn">
                    <i class="fas fa-save"></i> Salvar Alterações
                </button>
            </form>
            
            <a href="dashboard_aluno.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Voltar para o Dashboard
            </a>
        </div>
    </div>
    
    <script>
        // Máscara para telefone
        document.getElementById('telefone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                if (value.length <= 2) {
                    value = value.replace(/(\d{0,2})/, '($1');
                } else if (value.length <= 7) {
                    value = value.replace(/(\d{2})(\d{0,5})/, '($1) $2');
                } else {
                    value = value.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
                }
                e.target.value = value;
            }
        });
        
        // Formatar CPF ao carregar
        document.addEventListener('DOMContentLoaded', function() {
            const cpfInput = document.getElementById('cpf');
            if (cpfInput) {
                let cpf = cpfInput.value.replace(/\D/g, '');
                if (cpf.length === 11) {
                    cpf = cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
                    cpfInput.value = cpf;
                }
            }
        });
    </script>
</body>
</html>