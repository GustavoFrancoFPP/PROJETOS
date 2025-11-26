<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Conexão com banco
$servidor = "localhost";
$usuario = "root";
$senha = "senaisp";
$banco = "academia";

$conexao = new mysqli($servidor, $usuario, $senha, $banco);
if ($conexao->connect_error) {
    die("Erro na conexão: " . $conexao->connect_error);
}
$conexao->set_charset("utf8");

// Processar agendamento
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['aula_id'])) {
    $aula_id = $_POST['aula_id'];
    $data_aula = $_POST['data_aula'];
    $horario_aula = $_POST['horario_aula'];
    $id_cliente = $_SESSION['usuario_id'];
    
    // Buscar nome da aula
    $sql_aula = "SELECT nome_aula FROM aulas WHERE id_aula = ?";
    $stmt_aula = $conexao->prepare($sql_aula);
    $stmt_aula->bind_param("i", $aula_id);
    $stmt_aula->execute();
    $aula = $stmt_aula->get_result()->fetch_assoc();
    
    // Verificar se já existe agendamento no mesmo horário
    $sql_check = "SELECT * FROM agendamento WHERE id_cliente = ? AND data_aula = ? AND horario_aula = ?";
    $stmt_check = $conexao->prepare($sql_check);
    $stmt_check->bind_param("iss", $id_cliente, $data_aula, $horario_aula);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows > 0) {
        $erro = "❌ Você já tem um agendamento para este horário!";
    } else {
        // Inserir agendamento
        $sql = "INSERT INTO agendamento (tipo_aula, data_aula, horario_aula, id_cliente) 
                VALUES (?, ?, ?, ?)";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("sssi", $aula['nome_aula'], $data_aula, $horario_aula, $id_cliente);
        
        if ($stmt->execute()) {
            $_SESSION['agendamento_sucesso'] = "✅ Aula agendada com sucesso para " . date('d/m/Y', strtotime($data_aula)) . " às $horario_aula";
            header('Location: agendamento.php');
            exit();
        } else {
            $erro = "❌ Erro ao agendar aula: " . $conexao->error;
        }
    }
}

// Mostrar notificação de sucesso
if (isset($_SESSION['agendamento_sucesso'])) {
    $msg = $_SESSION['agendamento_sucesso'];
    unset($_SESSION['agendamento_sucesso']);
}

// Buscar aulas disponíveis
$sql_aulas = "SELECT * FROM aulas WHERE status = 'ativa'";
$aulas = $conexao->query($sql_aulas);

// Buscar agendamentos do usuário
$id_cliente = $_SESSION['usuario_id'];
$sql_meus_agendamentos = "SELECT * FROM agendamento WHERE id_cliente = ? ORDER BY data_aula DESC, horario_aula DESC";
$stmt_agendamentos = $conexao->prepare($sql_meus_agendamentos);
$stmt_agendamentos->bind_param("i", $id_cliente);
$stmt_agendamentos->execute();
$meus_agendamentos = $stmt_agendamentos->get_result();

// Gerar datas dos próximos 7 dias
$datas = [];
for ($i = 0; $i < 7; $i++) {
    $datas[] = [
        'data' => date('Y-m-d', strtotime("+$i days")),
        'dia_semana' => date('D', strtotime("+$i days")),
        'dia_mes' => date('d', strtotime("+$i days")),
        'mes' => date('M', strtotime("+$i days"))
    ];
}

// Horários disponíveis
$horarios = ['06:00', '07:00', '08:00', '09:00', '10:00', '11:00', '12:00', 
             '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendamento - TECHFIT</title>
    <style>
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #333;
            min-height: 100vh;
        }
        
        .header { 
            background: #2c3e50; 
            color: white; 
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .btn { 
            background: #3498db; 
            color: white; 
            padding: 10px 20px; 
            text-decoration: none; 
            border-radius: 5px; 
            border: none;
            cursor: pointer;
            font-size: 14px;
            display: inline-block;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #2980b9;
        }
        
        .btn.success { 
            background: #27ae60; 
        }
        
        .btn.success:hover {
            background: #219a52;
        }
        
        .btn.danger { 
            background: #e74c3c; 
        }
        
        .btn.danger:hover {
            background: #c0392b;
        }
        
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 2rem 20px;
        }
        
        .card { 
            background: white; 
            padding: 30px; 
            border-radius: 15px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            border: 1px solid #e0e0e0;
        }
        
        .message {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            text-align: center;
            font-weight: 500;
            animation: slideIn 0.5s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .success { 
            background: #d4edda; 
            color: #155724; 
            border-left: 4px solid #27ae60;
        }
        
        .error { 
            background: #f8d7da; 
            color: #721c24; 
            border-left: 4px solid #e74c3c;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 16px;
        }
        
        select, input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #bdc3c7;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
            background: white;
        }
        
        select:focus, input:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
        
        .btn-submit {
            background: #27ae60;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
            transition: all 0.3s;
        }
        
        .btn-submit:hover {
            background: #219a52;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(39, 174, 96, 0.3);
        }
        
        .info-box {
            background: #e8f4fd;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #3498db;
            margin-top: 20px;
        }
        
        .info-box h4 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .info-box ul {
            list-style: none;
            padding-left: 0;
        }
        
        .info-box li {
            padding: 5px 0;
            color: #34495e;
        }
        
        .info-box li::before {
            content: "• ";
            color: #3498db;
            font-weight: bold;
            margin-right: 8px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }
        
        th {
            background: #34495e;
            color: white;
            font-weight: 600;
            font-size: 14px;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .status-agendado { 
            background: #d4edda; 
            color: #155724; 
            padding: 6px 12px; 
            border-radius: 20px; 
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-cancelado { 
            background: #f8d7da; 
            color: #721c24; 
            padding: 6px 12px; 
            border-radius: 20px; 
            font-size: 12px;
            font-weight: 600;
        }
        
        .empty-message {
            text-align: center;
            color: #7f8c8d;
            padding: 40px 20px;
            font-style: italic;
        }
        
        .empty-message i {
            font-size: 48px;
            margin-bottom: 15px;
            display: block;
            color: #bdc3c7;
        }
        
        h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 28px;
            font-weight: 700;
        }
        
        h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 22px;
            font-weight: 600;
        }
        
        .date-display {
            background: #34495e;
            color: white;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 20px;
            font-size: 18px;
            font-weight: 600;
        }
        
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
            margin-bottom: 30px;
        }
        
        .calendar-day {
            background: white;
            padding: 15px 10px;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid transparent;
        }
        
        .calendar-day:hover {
            border-color: #3498db;
            transform: translateY(-2px);
        }
        
        .calendar-day.active {
            background: #3498db;
            color: white;
        }
        
        .day-name {
            font-size: 12px;
            color: #7f8c8d;
            margin-bottom: 5px;
        }
        
        .day-number {
            font-size: 18px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏋️‍♂️ TECHFIT - Agendamento</h1>
        <div>
            <a href="inicio.php" class="btn">🏠 Início</a>
            <a href="logout.php" class="btn danger">Sair</a>
        </div>
    </div>
    
    <div class="container">
        <?php if (isset($msg)): ?>
            <div class="message success"><?php echo $msg; ?></div>
        <?php endif; ?>
        
        <?php if (isset($erro)): ?>
            <div class="message error"><?php echo $erro; ?></div>
        <?php endif; ?>

        <div class="card">
            <h2>📅 Agendar Nova Aula</h2>
            
            <!-- Calendário -->
            <div class="date-display">
                🗓️ Próximos 7 Dias
            </div>
            
            <div class="calendar-grid">
                <?php foreach($datas as $index => $data): ?>
                <div class="calendar-day <?php echo $index == 0 ? 'active' : ''; ?>" 
                     onclick="selectDate('<?php echo $data['data']; ?>', this)">
                    <div class="day-name">
                        <?php 
                        $dias = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'];
                        echo $dias[date('w', strtotime($data['data']))];
                        ?>
                    </div>
                    <div class="day-number"><?php echo $data['dia_mes']; ?></div>
                    <div class="month"><?php echo $data['mes']; ?></div>
                </div>
                <?php endforeach; ?>
            </div>

            <form method="POST" id="agendamentoForm">
                <input type="hidden" name="data_aula" id="data_aula" value="<?php echo $datas[0]['data']; ?>" required>
                
                <div class="form-group">
                    <label for="aula_id">🎯 Selecione a Aula:</label>
                    <select id="aula_id" name="aula_id" required>
                        <option value="">-- Escolha uma aula --</option>
                        <?php while($aula = $aulas->fetch_assoc()): ?>
                        <option value="<?php echo $aula['id_aula']; ?>">
                            <?php echo $aula['nome_aula']; ?> 
                            (<?php echo $aula['duracao_minutos']; ?> min)
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="horario_aula">⏰ Selecione o Horário:</label>
                    <select id="horario_aula" name="horario_aula" required>
                        <option value="">-- Escolha um horário --</option>
                        <?php foreach($horarios as $horario): ?>
                        <option value="<?php echo $horario; ?>"><?php echo $horario; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn-submit">
                    ✅ Confirmar Agendamento
                </button>
            </form>
            
            <div class="info-box">
                <h4>📋 Informações Importantes</h4>
                <ul>
                    <li><strong>Horário de Funcionamento:</strong> 06:00 às 22:00</li>
                    <li><strong>Duração das Aulas:</strong> 60 minutos cada</li>
                    <li><strong>Chegar:</strong> 15 minutos antes do horário agendado</li>
                    <li><strong>Cancelamentos:</strong> Até 2 horas antes da aula</li>
                    <li><strong>Vestuário:</strong> Roupas confortáveis e tênis apropriados</li>
                    <li><strong>Equipamentos:</strong> Toalha e garrafa de água obrigatórios</li>
                </ul>
            </div>
        </div>

        <!-- Meus Agendamentos -->
        <div class="card">
            <h3>📋 Meus Agendamentos</h3>
            
            <?php if ($meus_agendamentos->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Aula</th>
                            <th>Data</th>
                            <th>Horário</th>
                            <th>Status</th>
                            <th>Data do Agendamento</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($agendamento = $meus_agendamentos->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo $agendamento['tipo_aula']; ?></strong></td>
                            <td><?php echo date('d/m/Y', strtotime($agendamento['data_aula'])); ?></td>
                            <td><?php echo $agendamento['horario_aula']; ?></td>
                            <td>
                                <span class="status-<?php echo $agendamento['status']; ?>">
                                    <?php 
                                    $status_text = [
                                        'agendado' => '✅ Agendado',
                                        'cancelado' => '❌ Cancelado',
                                        'realizado' => '✓ Realizado'
                                    ];
                                    echo $status_text[$agendamento['status']] ?? ucfirst($agendamento['status']);
                                    ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($agendamento['data_agendamento'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-message">
                    <i>📅</i>
                    <h4>Nenhum agendamento encontrado</h4>
                    <p>Que tal fazer seu primeiro agendamento? Escolha uma aula acima!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function selectDate(data, element) {
            // Remove active class from all days
            document.querySelectorAll('.calendar-day').forEach(day => {
                day.classList.remove('active');
            });
            
            // Add active class to clicked day
            element.classList.add('active');
            
            // Update hidden input
            document.getElementById('data_aula').value = data;
        }
        
        // Auto-close success message after 5 seconds
        <?php if (isset($msg)): ?>
            setTimeout(() => {
                const message = document.querySelector('.message.success');
                if (message) {
                    message.style.opacity = '0';
                    message.style.transform = 'translateY(-20px)';
                    setTimeout(() => message.remove(), 500);
                }
            }, 5000);
        <?php endif; ?>
        
        // Auto-close error message after 5 seconds
        <?php if (isset($erro)): ?>
            setTimeout(() => {
                const message = document.querySelector('.message.error');
                if (message) {
                    message.style.opacity = '0';
                    message.style.transform = 'translateY(-20px)';
                    setTimeout(() => message.remove(), 500);
                }
            }, 5000);
        <?php endif; ?>
    </script>
</body>
</html>