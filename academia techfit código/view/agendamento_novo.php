<?php
session_start();
require_once __DIR__ . '/../config/Connection.php';

// Verificar login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$mensagem = "";
$id_cliente = $_SESSION['user_id'];

// Processar agendamento
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agendar_aula'])) {
    try {
        $conn = Connection::getInstance();
        
        $tipo_aula = $_POST['tipo_aula'] ?? '';
        $data_agendamento = $_POST['data_agendamento'] ?? '';
        $horario = $_POST['horario'] ?? '';
        
        // Validações
        if (empty($tipo_aula) || empty($data_agendamento) || empty($horario)) {
            $mensagem = "⚠️ Preencha todos os campos!";
        } elseif (strtotime($data_agendamento) < strtotime(date('Y-m-d'))) {
            $mensagem = "❌ Não é possível agendar em datas passadas!";
        } else {
            $data_hora_completa = $data_agendamento . ' ' . $horario;
            
            $stmt = $conn->prepare("INSERT INTO agendamento (id_cliente, tipo_aula, data_agendamento) 
                                  VALUES (?, ?, ?)");
            $stmt->execute([$id_cliente, $tipo_aula, $data_hora_completa]);
            
            $mensagem = "✅ Aula agendada com sucesso! Aguarde confirmação.";
        }
    } catch (Exception $e) {
        $mensagem = "❌ Erro ao agendar: " . $e->getMessage();
    }
}

// Buscar aulas disponíveis do banco
try {
    $conn = Connection::getInstance();
    $stmt = $conn->query("SELECT DISTINCT nome_aula FROM aulas WHERE status = 'ativa' ORDER BY nome_aula");
    $aulasDisponiveis = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $aulasDisponiveis = ['Musculação', 'Yoga', 'Pilates', 'Spinning', 'Crossfit', 'Funcional', 'HIIT', 'Zumba'];
}

// Gerar próximos 30 dias
$dataAtual = new DateTime();
$diasDisponiveis = [];
for ($i = 1; $i <= 30; $i++) {
    $data = clone $dataAtual;
    $data->modify("+$i day");
    $diasDisponiveis[] = $data->format('Y-m-d');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendamento - TECHFIT</title>
    <link rel="stylesheet" href="assets/css/agendamento.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #fff;
            font-family: 'Poppins', sans-serif;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 40px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        h1 {
            text-align: center;
            color: #00F0E1;
            margin-bottom: 10px;
            font-size: 2.5rem;
        }
        
        .subtitle {
            text-align: center;
            color: #b0b7d9;
            margin-bottom: 40px;
        }
        
        .mensagem {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }
        
        .mensagem.success {
            background: rgba(0, 240, 225, 0.2);
            color: #00F0E1;
            border: 1px solid #00F0E1;
        }
        
        .mensagem.error {
            background: rgba(255, 100, 100, 0.2);
            color: #ff6464;
            border: 1px solid #ff6464;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            color: #b0b7d9;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }
        
        select, input[type="date"], input[type="time"] {
            width: 100%;
            padding: 12px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(0, 240, 225, 0.3);
            border-radius: 8px;
            color: #fff;
            font-size: 1rem;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
        }
        
        select:focus, input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.08);
            border-color: #00F0E1;
            box-shadow: 0 0 10px rgba(0, 240, 225, 0.3);
        }
        
        select option {
            background: #1a1a2e;
            color: #fff;
        }
        
        .calendario {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid rgba(0, 240, 225, 0.2);
        }
        
        .calendario h3 {
            color: #00F0E1;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .dias-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
            gap: 8px;
        }
        
        .dia-btn {
            padding: 10px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(0, 240, 225, 0.3);
            border-radius: 8px;
            color: #b0b7d9;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .dia-btn:hover {
            background: rgba(0, 240, 225, 0.1);
            border-color: #00F0E1;
            color: #00F0E1;
            transform: translateY(-2px);
        }
        
        .dia-btn.selected {
            background: #00F0E1;
            color: #1a1a2e;
            border-color: #00F0E1;
            font-weight: bold;
        }
        
        .btn-agendar {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #00F0E1 0%, #00d4b4 100%);
            color: #1a1a2e;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
        }
        
        .btn-agendar:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 240, 225, 0.3);
        }
        
        .btn-agendar:active {
            transform: translateY(0);
        }
        
        .voltar {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: rgba(0, 240, 225, 0.1);
            color: #00F0E1;
            border: 1px solid #00F0E1;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .voltar:hover {
            background: rgba(0, 240, 225, 0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-calendar-check"></i> Agendar Aula</h1>
        <p class="subtitle">Escolha a aula, data e horário desejados</p>
        
        <?php if ($mensagem): ?>
            <div class="mensagem <?php echo strpos($mensagem, '✅') ? 'success' : 'error'; ?>">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <!-- Tipo de Aula -->
            <div class="form-group">
                <label for="tipo_aula"><i class="fas fa-dumbbell"></i> Tipo de Aula</label>
                <select name="tipo_aula" id="tipo_aula" required>
                    <option value="">Selecione uma aula...</option>
                    <?php foreach ($aulasDisponiveis as $aula): ?>
                        <option value="<?php echo htmlspecialchars($aula); ?>">
                            <?php echo htmlspecialchars($aula); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Calendário (Seleção de Data) -->
            <div class="form-group">
                <label><i class="fas fa-calendar"></i> Selecione a Data</label>
                <div class="calendario">
                    <h3>Próximos 30 dias</h3>
                    <div class="dias-grid">
                        <?php foreach ($diasDisponiveis as $data): ?>
                            <button type="button" class="dia-btn" data-data="<?php echo $data; ?>">
                                <?php 
                                    $objData = new DateTime($data);
                                    echo $objData->format('d/m');
                                    echo '<br>';
                                    echo $objData->format('D');
                                ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <input type="date" name="data_agendamento" id="data_agendamento" required style="display: none;">
                </div>
            </div>
            
            <!-- Horário -->
            <div class="form-group">
                <label for="horario"><i class="fas fa-clock"></i> Horário</label>
                <input type="time" name="horario" id="horario" required>
            </div>
            
            <button type="submit" name="agendar_aula" class="btn-agendar">
                <i class="fas fa-check-circle"></i> Agendar Aula
            </button>
        </form>
        
        <a href="dashboard_aluno.php" class="voltar">
            <i class="fas fa-arrow-left"></i> Voltar ao Dashboard
        </a>
    </div>
    
    <script>
        // Selecionar data no calendário
        document.querySelectorAll('.dia-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remove seleção anterior
                document.querySelectorAll('.dia-btn').forEach(b => b.classList.remove('selected'));
                
                // Marca novo selecionado
                this.classList.add('selected');
                
                // Atualiza input hidden
                document.getElementById('data_agendamento').value = this.dataset.data;
            });
        });
        
        // Validar data no formulário
        document.querySelector('form').addEventListener('submit', function(e) {
            const dataInput = document.getElementById('data_agendamento');
            if (!dataInput.value) {
                e.preventDefault();
                alert('Por favor, selecione uma data!');
            }
        });
    </script>
</body>
</html>
