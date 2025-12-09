<?php
session_start();
require_once __DIR__ . '/../config/Connection.php';

// --- 1. LÓGICA PHP (Processamento) ---
// Fica tudo aqui em cima, mas SEM dar echo em nada ainda

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$mensagem_alerta = ""; // Variável para guardar a mensagem

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agendar_aula'])) {
    try {
        $conn = Connection::getInstance();
        
        $id_cliente = $_SESSION['user_id'];
        $tipo_aula  = $_POST['nome_aula'];
        $horario    = $_POST['horario'];
        $dia_semana = $_POST['dia_semana'];

        $dias_map = [
            'Segunda' => 'Monday', 'Terça' => 'Tuesday', 'Quarta' => 'Wednesday', 
            'Quinta' => 'Thursday', 'Sexta' => 'Friday', 'Sábado' => 'Saturday', 'Domingo' => 'Sunday'
        ];
        $diaIngles = $dias_map[$dia_semana] ?? 'Monday';
        
        $hoje_ingles = date('l');
        if ($hoje_ingles == $diaIngles) {
             $data_aula = date('Y-m-d');
        } else {
             $data_aula = date('Y-m-d', strtotime("next $diaIngles"));
        }

        // A tabela agendamento tem: tipo_aula, data_agendamento, id_cliente, id_avaliacao
        // NOTA: Não existem as colunas data_aula, horario_aula, status no banco atual
        // Usando data_agendamento (DATETIME) que combina data e hora
        $dataHoraCompleta = $data_aula . ' ' . $horario . ':00';
        
        $sql = "INSERT INTO agendamento (id_cliente, tipo_aula, data_agendamento) 
                VALUES (:id_cliente, :tipo_aula, :data_agendamento)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':id_cliente' => $id_cliente, 
            ':tipo_aula' => $tipo_aula, 
            ':data_agendamento' => $dataHoraCompleta
        ]);

        $mensagem_alerta = "Pedido enviado com sucesso! Aguarde a confirmação do professor.";

    } catch (PDOException $e) {
        $mensagem_alerta = "Erro ao agendar: " . $e->getMessage();
    }
}

// Dados da grade para visualização
$grade_aulas = [
    ['horario' => '07:00', 'nome' => 'Yoga Matinal',     'vagas' => 21],
    ['horario' => '09:00', 'nome' => 'Yoga Matinal',     'vagas' => 15],
    ['horario' => '13:00', 'nome' => 'Spinning Class',   'vagas' => 22],
    ['horario' => '15:00', 'nome' => 'Pilates',          'vagas' => 14],
    ['horario' => '17:50', 'nome' => 'Treino Funcional', 'vagas' => 38],
    ['horario' => '19:00', 'nome' => 'Pilates',          'vagas' => 17],
    ['horario' => '21:00', 'nome' => 'HIIT',             'vagas' => 0]
];
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
        .week-days { display: flex; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px; }
        .week-days span { flex: 1; text-align: center; color: #b0b7d9; cursor: pointer; padding: 5px; }
        .week-days span.active { color: #00F0E1; font-weight: bold; border-bottom: 2px solid #00F0E1; }
        form { display: inline; }
        .full-btn { background-color: #555; color: #ccc; border: none; padding: 10px 20px; border-radius: 5px; cursor: not-allowed; font-weight: bold; }
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
                    <li><a href="dashboard_aluno.php" class="nav-link">Dashboard</a></li>
                    <li><a href="Produto.HTML" class="nav-link">Produtos</a></li>
                    <li><a href="agendamento.php" class="nav-link active">Agendamento</a></li>
                    <li><a href="suporte.html" class="nav-link">Suporte</a></li>
                    <li><a href="logout.php" class="nav-link" style="color: #ff4444;">Sair</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <section class="booking">
            <div class="section-header">
                <h2>Agendamento de Aulas</h2>
                <div class="date-nav">
                    <span class="current-date"><?php echo date('D, d M'); ?></span>
                </div>
                
                <div class="week-days">
                    <span>Seg</span><span>Ter</span><span>Qua</span><span>Qui</span>
                    <span class="active">Sex</span><span>Sab</span><span>Dom</span>
                </div>
            </div>

            <ul class="class-list">
                <?php foreach ($grade_aulas as $aula): ?>
                    <li class="class-item">
                        <span class="time"><?php echo $aula['horario']; ?></span>
                        <div class="class-details">
                            <h3 class="name"><?php echo $aula['nome']; ?></h3>
                            <p class="spots"><?php echo $aula['vagas'] > 0 ? $aula['vagas'] . ' vagas' : 'Esgotado'; ?></p>
                        </div>
                        <?php if($aula['vagas'] > 0): ?>
                            <form method="POST">
                                <input type="hidden" name="nome_aula" value="<?php echo $aula['nome']; ?>">
                                <input type="hidden" name="horario" value="<?php echo $aula['horario'] . ':00'; ?>">
                                <input type="hidden" name="dia_semana" value="Sexta">
                                <button type="submit" name="agendar_aula" class="schedule-btn">Agendar</button>
                            </form>
                        <?php else: ?>
                            <button class="full-btn" disabled>LOTADO</button>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    </main>

    <?php if (!empty($mensagem_alerta)): ?>
    <script>
        // Pequeno atraso de 100ms para garantir que o navegador desenhou a tela
        setTimeout(function() {
            alert("<?php echo $mensagem_alerta; ?>");
            // Limpa o histórico para não reenviar formulário ao atualizar
            if ( window.history.replaceState ) {
                window.history.replaceState( null, null, window.location.href );
            }
        }, 100);
    </script>
    <?php endif; ?>

    <script src="assets/js/script.js"></script>
</body>
</html>