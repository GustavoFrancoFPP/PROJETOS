<?php
require_once 'UsuarioController.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Verifica se é aluno (não funcionário)
if (isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'funcionario') {
    header('Location: painel_administrativo.php');
    exit;
}

// Dados do aluno da sessão
$nomeAluno = $_SESSION['user_nome'] ?? 'Aluno';
$emailAluno = $_SESSION['user_email'] ?? '';
$idAluno = $_SESSION['user_id'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TECHFIT - Dashboard do Aluno</title>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="login.css">
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .dashboard-header {
            background: var(--cor-card-escuro);
            backdrop-filter: blur(15px);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            border-top: 4px solid var(--cor-ciano-principal);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .dashboard-header h1 {
            color: var(--cor-ciano-principal);
            font-size: 28px;
            margin: 0;
        }

        .dashboard-header p {
            color: var(--cor-texto-secundario);
            margin: 5px 0 0;
        }

        .header-actions {
            display: flex;
            gap: 15px;
        }

        .btn-logout, .btn-editar {
            background: linear-gradient(135deg, var(--cor-ciano-principal), #00d4ff);
            color: var(--cor-fundo-escuro);
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            font-family: var(--font-principal);
        }

        .btn-logout:hover, .btn-editar:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 209, 178, 0.4);
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .dashboard-card {
            background: var(--cor-card-escuro);
            backdrop-filter: blur(15px);
            border-radius: 15px;
            padding: 25px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
            border-color: var(--cor-ciano-principal);
        }

        .dashboard-card h2 {
            color: var(--cor-ciano-principal);
            font-size: 18px;
            margin: 0 0 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .dashboard-card i {
            font-size: 24px;
        }

        .dashboard-card p {
            color: var(--cor-texto-secundario);
            margin: 10px 0;
            line-height: 1.6;
        }

        .dashboard-card .card-value {
            color: var(--cor-ciano-principal);
            font-size: 20px;
            font-weight: 700;
            margin: 10px 0;
        }

        .btn-primary-small {
            background: rgba(0, 240, 225, 0.2);
            border: 1px solid var(--cor-ciano-principal);
            color: var(--cor-ciano-principal);
            padding: 10px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            font-family: var(--font-principal);
            margin-top: 10px;
        }

        .btn-primary-small:hover {
            background: var(--cor-ciano-principal);
            color: var(--cor-fundo-escuro);
        }

        .info-section {
            background: var(--cor-card-escuro);
            backdrop-filter: blur(15px);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            border-left: 4px solid var(--cor-ciano-principal);
        }

        .info-section h3 {
            color: var(--cor-ciano-principal);
            margin-top: 0;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--cor-texto-primario);
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            color: var(--cor-texto-secundario);
        }
    </style>
</head>
<body>
    <header class="techfit-header">
        <div class="header-container">
            <a href="inicio.html" class="header-logo">
                <div class="logo-text">TECH<span>FIT</span></div>
            </a>
            <nav class="main-navigation">
                <ul class="nav-links">
                    <li><a href="inicio.html" class="nav-link">Início</a></li>
                    <li><a href="pagina_1.html" class="nav-link">Academias</a></li>
                    <li><a href="produtos.html" class="nav-link">Produtos</a></li>
                    <li><a href="agendamento2.html" class="nav-link">Agendamento</a></li>
                    <li><a href="suporte.html" class="nav-link">Suporte</a></li>
                </ul>
            </nav>
            <button class="hamburger-menu" aria-label="Menu">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>
        </div>
    </header>

    <main>
        <div class="dashboard-container">
            <!-- Header do Dashboard -->
            <div class="dashboard-header">
                <div>
                    <h1>Bem-vindo, <?php echo htmlspecialchars($nomeAluno); ?>! 👋</h1>
                    <p>Gerenciador da sua jornada fitness TECHFIT</p>
                </div>
                <div class="header-actions">
                    <a href="editar_perfil.php" class="btn-editar"><i class="fas fa-edit"></i> Editar Perfil</a>
                    <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Sair</a>
                </div>
            </div>

            <!-- Grid de Cartões -->
            <div class="dashboard-grid">
                <!-- Card de Plano -->
                <div class="dashboard-card">
                    <h2><i class="fas fa-dumbbell"></i> Seu Plano</h2>
                    <div class="card-value">Não definido</div>
                    <p>Escolha um plano para começar sua jornada fitness com a gente!</p>
                    <button class="btn-primary-small">Escolher Plano</button>
                </div>

                <!-- Card de Agendamento -->
                <div class="dashboard-card">
                    <h2><i class="fas fa-calendar-alt"></i> Próximas Aulas</h2>
                    <p>Você não tem aulas agendadas no momento.</p>
                    <button class="btn-primary-small">Agendar Aula</button>
                </div>

                <!-- Card de Treino -->
                <div class="dashboard-card">
                    <h2><i class="fas fa-chart-line"></i> Seu Treino</h2>
                    <p>Acompanhe seu progresso e confira seu programa de treino personalizado.</p>
                    <button class="btn-primary-small">Ver Treino</button>
                </div>

                <!-- Card de Unidades -->
                <div class="dashboard-card">
                    <h2><i class="fas fa-map-marker-alt"></i> Unidades</h2>
                    <p>Visite qualquer uma de nossas academias em todo o Brasil.</p>
                    <button class="btn-primary-small">Ver Unidades</button>
                </div>

                <!-- Card de Suporte -->
                <div class="dashboard-card">
                    <h2><i class="fas fa-headset"></i> Suporte</h2>
                    <p>Tem dúvidas? Entre em contato com nosso time de suporte.</p>
                    <button class="btn-primary-small">Contatar Suporte</button>
                </div>

                <!-- Card de Histórico -->
                <div class="dashboard-card">
                    <h2><i class="fas fa-history"></i> Histórico</h2>
                    <p>Confira seu histórico de aulas e treinos realizados.</p>
                    <button class="btn-primary-small">Ver Histórico</button>
                </div>
            </div>

            <!-- Seção de Informações -->
            <div class="info-section">
                <h3><i class="fas fa-user-circle"></i> Informações da Conta</h3>
                <div class="info-item">
                    <span class="info-label">Nome:</span>
                    <span><?php echo htmlspecialchars($nomeAluno); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email:</span>
                    <span><?php echo htmlspecialchars($emailAluno); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">ID Aluno:</span>
                    <span><?php echo htmlspecialchars($idAluno); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status:</span>
                    <span style="color: var(--cor-ciano-principal);">✓ Ativo</span>
                </div>
            </div>
        </div>
    </main>

    <footer class="main-footer">
        <div class="container">
            <div class="footer-brand">
                <div class="logo-text">TECH<span>FIT</span></div>
                <p>Sua jornada fitness começa aqui</p>
            </div>
            <div class="footer-copyright">
                &copy; 2024 TECHFIT. Todos os direitos reservados.
            </div>
        </div>
    </footer>

    <script src="header-carrinho-simples.js"></script>
</body>
</html>
