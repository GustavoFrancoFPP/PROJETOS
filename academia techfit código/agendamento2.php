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
        $erro = "Você já tem um agendamento para este horário!";
    } else {
        // Inserir agendamento
        $sql = "INSERT INTO agendamento (tipo_aula, data_aula, horario_aula, id_cliente) 
                VALUES (?, ?, ?, ?)";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("sssi", $aula['nome_aula'], $data_aula, $horario_aula, $id_cliente);
        
        if ($stmt->execute()) {
            $_SESSION['agendamento_sucesso'] = "Aula agendada com sucesso para " . date('d/m/Y', strtotime($data_aula)) . " às $horario_aula";
            header('Location: agendamento.php');
            exit();
        } else {
            $erro = "Erro ao agendar aula: " . $conexao->error;
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
    $datas[] = date('Y-m-d', strtotime("+$i days"));
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
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #e3edf7, #cfd9df);
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        .header {
            background: #2c3e50;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
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
        }

        .btn:hover {
            background: #2980b9;
        }

        .container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 20px;
        }

        .card {
            background: #ffffff;
            padding: 30px;
            border-radius: 18px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            margin-bottom: 2rem;
            width: 100%;
        }

        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
        }

        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            color: #4a5568;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 500;
        }

        select, input {
            width: 100%;
            padding: 12px 15px;
            border: 1.8px solid #cbd5e0;
            border-radius: 8px;
            font-size: 16px;
            margin-bottom: 5px;
        }

        select:focus, input:focus {
            border-color: #3498db;
            outline: none;
        }

        .btn-submit {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #3182ce, #2563eb);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
        }

        .btn-submit:hover {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            background: white;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        th {
            background: #4a5568;
            color: white;
            font-weight: 600;
        }

        tr:hover {
            background: #f7fafc;
        }

        h2 {
            text-align: center;
            color: #2d3748;
            margin-bottom: 25px;
        }

        h3 {
            color: #2d3748;
            margin-bottom: 20px;
        }

        .date-selector {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }

        .date-option {
            padding: 10px;
            text-align: center;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            cursor: pointer;
            background: white;
        }

        .date-option.active {
            border-color: #3182ce;
            background: #ebf8ff;
        }

        .day-name {
            font-size: 12px;
            color: #718096;
        }

        .day-number {
            font-size: 16px;
            font-weight: bold;
            color: #2d3748;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>TECHFIT - Agendamento</h1>
        <div>
            <a href="inicio.php" class="btn">Início</a>
            <a href="logout.php" class="btn" style="background: #e74c3c;">Sair</a>
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
            <h2>Agendar Nova Aula</h2>

            <div class="date-selector">
                <?php foreach($datas as $index => $data): ?>
                <div class="date-option <?php echo $index == 0 ? 'active' : ''; ?>" 
                     onclick="selectDate('<?php echo $data; ?>', this)">
                    <div class="day-name">
                        <?php echo date('D', strtotime($data)); ?>
                    </div>
                    <div class="day-number"><?php echo date('d', strtotime($data)); ?></div>
                    <div class="month"><?php echo date('M', strtotime($data)); ?></div>
                </div>
                <?php endforeach; ?>
            </div>

            <form method="POST" id="agendamentoForm">
                <input type="hidden" name="data_aula" id="data_aula" value="<?php echo $datas[0]; ?>" required>
                
                <div class="form-group">
                    <label for="aula_id">Selecione a Aula:</label>
                    <select id="aula_id" name="aula_id" required>
                        <option value="">-- Escolha uma aula --</option>
                        <?php while($aula = $aulas->fetch_assoc()): ?>
                        <option value="<?php echo $aula['id_aula']; ?>">
                            <?php echo $aula['nome_aula']; ?> 
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="horario_aula">Selecione o Horário:</label>
                    <select id="horario_aula" name="horario_aula" required>
                        <option value="">-- Escolha um horário --</option>
                        <?php foreach($horarios as $horario): ?>
                        <option value="<?php echo $horario; ?>"><?php echo $horario; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn-submit">
                    Confirmar Agendamento
                </button>
            </form>
        </div>

        <div class="card">
            <h3>Meus Agendamentos</h3>
            
            <?php if ($meus_agendamentos->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Aula</th>
                            <th>Data</th>
                            <th>Horário</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($agendamento = $meus_agendamentos->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $agendamento['tipo_aula']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($agendamento['data_aula'])); ?></td>
                            <td><?php echo $agendamento['horario_aula']; ?></td>
                            <td>
                                <?php 
                                $status_text = [
                                    'agendado' => 'Agendado',
                                    'cancelado' => 'Cancelado',
                                    'realizado' => 'Realizado'
                                ];
                                echo $status_text[$agendamento['status']] ?? ucfirst($agendamento['status']);
                                ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align: center; color: #718096; padding: 20px;">
                    Nenhum agendamento encontrado.
                </p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function selectDate(data, element) {
            // Remove active class from all dates
            document.querySelectorAll('.date-option').forEach(date => {
                date.classList.remove('active');
            });
            
            // Add active class to clicked date
            element.classList.add('active');
            
            // Update hidden input
            document.getElementById('data_aula').value = data;
        }
        
        // Auto-close messages after 5 seconds
        setTimeout(() => {
            const messages = document.querySelectorAll('.message');
            messages.forEach(message => {
                message.style.display = 'none';
            });
        }, 5000);
    </script>
</body>
</html>