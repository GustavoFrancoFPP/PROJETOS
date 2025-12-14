<?php
session_start();
require_once __DIR__ . '/../config/Connection.php';

// Impede acesso sem login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$mensagem_alerta = '';
$id_cliente = $_SESSION['user_id'];

// Processa o agendamento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agendar_aula'])) {
    try {
        $conn = Connection::getInstance();

        $tipo_aula = trim($_POST['nome_aula'] ?? '');
        $data_agendamento = trim($_POST['data_agendamento'] ?? '');
        $horario = trim($_POST['horario'] ?? '');

        if ($tipo_aula === '' || $data_agendamento === '' || $horario === '') {
            $mensagem_alerta = 'Preencha todos os campos para agendar.';
        } elseif (strtotime($data_agendamento) < strtotime(date('Y-m-d'))) {
            $mensagem_alerta = 'Não é possível agendar em datas passadas.';
        } else {
            // Busca a aula no banco para verificar vagas
            $stmt = $conn->prepare('SELECT id_aula, vagas_totais, vagas_ocupadas FROM aulas WHERE nome_aula = ? AND horario = ? AND status = "ativa"');
            $stmt->execute([$tipo_aula, $horario]);
            $aula = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$aula) {
                $mensagem_alerta = 'Aula não encontrada ou inativa.';
            } elseif ($aula['vagas_ocupadas'] >= $aula['vagas_totais']) {
                $mensagem_alerta = 'Não há vagas disponíveis para esta aula.';
            } else {
                // Insere agendamento
                $dataHoraCompleta = $data_agendamento . ' ' . $horario;
                $stmt = $conn->prepare('INSERT INTO agendamento (id_cliente, tipo_aula, data_agendamento) VALUES (?, ?, ?)');
                $stmt->execute([$id_cliente, $tipo_aula, $dataHoraCompleta]);
                
                // Aumenta vagas_ocupadas da aula
                $stmt = $conn->prepare('UPDATE aulas SET vagas_ocupadas = vagas_ocupadas + 1 WHERE id_aula = ?');
                $stmt->execute([$aula['id_aula']]);
                
                $mensagem_alerta = 'Aula agendada com sucesso! Aguarde confirmação.';
            }
        }
    } catch (Exception $e) {
        $mensagem_alerta = 'Erro ao agendar: ' . $e->getMessage();
    }
}

// Busca aulas ativas do banco de dados
try {
    $conn = Connection::getInstance();
    $stmt = $conn->query('SELECT nome_aula, horario, vagas_totais, vagas_ocupadas, (vagas_totais - vagas_ocupadas) as vagas_disponiveis FROM aulas WHERE status = "ativa" ORDER BY horario');
    $grade_aulas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formata para o layout esperado
    $grade_aulas = array_map(function($aula) {
        return [
            'horario' => date('H:i', strtotime($aula['horario'])),
            'nome' => $aula['nome_aula'],
            'vagas' => $aula['vagas_disponiveis']
        ];
    }, $grade_aulas);
} catch (Exception $e) {
    // Se der erro, usa array vazio
    $grade_aulas = [];
}

// Próximos 30 dias para o calendário
$diasDisponiveis = [];
$hoje = new DateTime();
for ($i = 0; $i < 30; $i++) {
    $data = clone $hoje;
    $data->modify("+{$i} day");
    $diasDisponiveis[] = $data;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TECHFIT - Agendamento</title>
    <link rel="stylesheet" href="assets/css/agendamento.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        form { display: inline; }
        .full-btn { background-color: #555; color: #ccc; border: none; padding: 10px 20px; border-radius: 5px; cursor: not-allowed; font-weight: bold; }
        .calendar-box { background: rgba(255,255,255,0.05); border: 1px solid rgba(0,240,225,0.2); border-radius: 12px; padding: 16px; margin-bottom: 20px; }
        .calendar-header { display: flex; justify-content: space-between; align-items: center; color: #00F0E1; font-weight: 600; margin-bottom: 12px; }
        .dias-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(90px, 1fr)); gap: 8px; }
        .dia-btn { background: rgba(255,255,255,0.05); border: 1px solid rgba(0,240,225,0.25); color: #b0b7d9; padding: 10px 6px; border-radius: 8px; cursor: pointer; text-align: center; transition: all 0.2s ease; }
        .dia-btn:hover { background: rgba(0,240,225,0.1); color: #00F0E1; border-color: #00F0E1; }
        .dia-btn.selected { background: #00F0E1; color: #0f172a; border-color: #00F0E1; font-weight: 700; }
        .selected-label { margin-top: 10px; color: #b0b7d9; font-size: 0.95rem; }
        .selected-label span { color: #00F0E1; font-weight: 600; }
        .alerta-msg { padding: 12px 14px; border-radius: 8px; margin-bottom: 18px; font-weight: 600; text-align: center; }
        .alerta-msg.sucesso { background: rgba(0,240,225,0.15); border: 1px solid rgba(0,240,225,0.5); color: #00F0E1; }
        .alerta-msg.erro { background: rgba(255,99,99,0.15); border: 1px solid rgba(255,99,99,0.5); color: #ff6666; }
        .schedule-btn.disabled { opacity: 0.55; cursor: not-allowed; background: #2b2b2b; border: 1px dashed rgba(255,255,255,0.25); color: #aaa; }
        .selected-label.feedback { display: flex; align-items: center; gap: 6px; margin-top: 8px; }
        .selected-label.feedback.error { color: #ff7b7b; }
    </style>
    <link rel="icon" type="image/x-icon" href="assets/images/imagens/favicon.ico">
</head>
<body>

    <header class="techfit-header">
        <div class="header-container">
            <!-- Logo -->
            <a href="inicio.html" class="header-logo">
                <img src="assets/images/imagens/WhatsApp Image 2025-10-02 at 15.15.22.jpeg" 
                     alt="TechFit - Academia Inteligente" 
                     class="logo-image">
                <div class="logo-text">TECH<span>FIT</span></div>
            </a>

            <!-- Navegação -->
            <nav class="main-navigation">
                <ul class="nav-links">
                    <li><a href="inicio.html" class="nav-link">Início</a></li>
                    <li><a href="pagina_1.html" class="nav-link">Academias</a></li>
                    <li><a href="produtos_loja.php" class="nav-link">Produtos</a></li>
                    <li><a href="agendamento.php" class="nav-link active">Agendamento</a></li>
                    <li><a href="suporte.html" class="nav-link">Suporte</a></li>
                </ul>

                <!-- Botão de Ação -->
                <div class="header-cta">
                    <!-- Botão do Carrinho (será adicionado pelo JavaScript) -->
                    <a href="dashboard_aluno.php" class="cta-button">Dashboard</a>
                </div>
            </nav>

            <!-- Menu Hambúrguer (Mobile) -->
            <button class="hamburger-menu" aria-label="Menu">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>
        </div>
    </header>

    <main class="main-content">
        <section class="booking">
            <div class="section-header">
                <h2>Agendamento de Aulas</h2>
            </div>

            <?php if ($mensagem_alerta !== ''): ?>
                <div class="alerta-msg <?php echo strpos($mensagem_alerta, 'sucesso') !== false ? 'sucesso' : 'erro'; ?>">
                    <?php echo htmlspecialchars($mensagem_alerta); ?>
                </div>
            <?php endif; ?>

            <div class="calendar-box">
                <div class="calendar-header">
                    <span><i class="fas fa-calendar-alt"></i> Escolha a data</span>
                    <span>Próximos 30 dias</span>
                </div>
                <div class="dias-grid">
                    <?php foreach ($diasDisponiveis as $dataObj): ?>
                        <?php $dataStr = $dataObj->format('Y-m-d'); ?>
                        <button type="button" class="dia-btn" data-data="<?php echo $dataStr; ?>">
                            <div><?php echo $dataObj->format('d/m'); ?></div>
                            <small><?php echo $dataObj->format('D'); ?></small>
                        </button>
                    <?php endforeach; ?>
                </div>
                <div class="selected-label">Data selecionada: <span id="data-selecionada-label">nenhuma</span></div>
                <div class="selected-label feedback" id="calendar-feedback"></div>
            </div>

            <ul class="class-list">
                <?php foreach ($grade_aulas as $aula): ?>
                    <li class="class-item">
                        <span class="time"><?php echo $aula['horario']; ?></span>
                        <div class="class-details">
                            <h3 class="name"><?php echo htmlspecialchars($aula['nome']); ?></h3>
                            <p class="spots"><?php echo $aula['vagas'] > 0 ? $aula['vagas'] . ' vagas' : 'Esgotado'; ?></p>
                        </div>
                        <?php if ($aula['vagas'] > 0): ?>
                            <form method="POST" class="form-agendar">
                                <input type="hidden" name="nome_aula" value="<?php echo htmlspecialchars($aula['nome']); ?>">
                                <input type="hidden" name="horario" value="<?php echo $aula['horario']; ?>">
                                <input type="hidden" name="data_agendamento" class="input-data-agendamento" value="">
                                <button type="submit" name="agendar_aula" class="schedule-btn" aria-disabled="true">Agendar</button>
                            </form>
                        <?php else: ?>
                            <button class="full-btn" disabled>LOTADO</button>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    </main>

    <script>
        const dayButtons = document.querySelectorAll('.dia-btn');
        const dataLabel = document.getElementById('data-selecionada-label');
        const feedback = document.getElementById('calendar-feedback');
        const scheduleButtons = document.querySelectorAll('.schedule-btn');
        let dataSelecionada = '';

        // Começa com botões desabilitados até escolher uma data
        scheduleButtons.forEach(btn => {
            btn.classList.add('disabled');
            btn.setAttribute('disabled', 'disabled');
            btn.setAttribute('title', 'Escolha uma data no calendário primeiro');
        });

        dayButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                dayButtons.forEach(b => b.classList.remove('selected'));
                btn.classList.add('selected');
                dataSelecionada = btn.dataset.data;
                dataLabel.textContent = new Date(dataSelecionada + 'T00:00:00').toLocaleDateString('pt-BR', { weekday: 'short', day: '2-digit', month: '2-digit', year: 'numeric' });
                document.querySelectorAll('.input-data-agendamento').forEach(input => {
                    input.value = dataSelecionada;
                });
                feedback.textContent = '';
                scheduleButtons.forEach(button => {
                    button.classList.remove('disabled');
                    button.removeAttribute('disabled');
                    button.removeAttribute('aria-disabled');
                    button.removeAttribute('title');
                });
            });
        });

        document.querySelectorAll('.form-agendar').forEach(form => {
            form.addEventListener('submit', (e) => {
                const hiddenDate = form.querySelector('.input-data-agendamento');
                if (!hiddenDate.value) {
                    e.preventDefault();
                    feedback.textContent = 'Escolha a data para liberar o agendamento.';
                    feedback.classList.add('error');
                    feedback.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } else {
                    const selected = new Date(hiddenDate.value + 'T00:00:00');
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    if (selected < today) {
                        e.preventDefault();
                        feedback.textContent = 'Não é possível agendar para uma data passada.';
                        feedback.classList.add('error');
                        feedback.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });
        });
    </script>
    <script src="assets/js/script.js"></script>
    <script src="assets/js/header-carrinho-simples.js"></script>
</body>
</html>
