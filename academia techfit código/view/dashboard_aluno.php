<?php
// Conexão direta para garantir funcionamento
require_once 'Connection.php';
require_once 'AlunoDAO.php'; // Mantemos para as outras funções

if (session_status() == PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$idAluno = $_SESSION['user_id'];
$conn = Connection::getInstance();

// CONSULTA MANUAL DE AGENDAMENTOS (Para garantir que puxe os dados recém criados)
$sqlAgendamentos = "SELECT * FROM agendamento WHERE id_cliente = :id ORDER BY id_agendamento DESC";
$stmt = $conn->prepare($sqlAgendamentos);
$stmt->execute([':id' => $idAluno]);
$agendamentosAluno = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalAgendamentos = count($agendamentosAluno);

// Buscando dados do aluno para o Header
$alunoDAO = new AlunoDAO();
$infoAluno = $alunoDAO->buscarAlunoPorId($idAluno);
$nomeAluno = $infoAluno['nome_cliente'] ?? 'Aluno';

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TECHFIT - Dashboard</title>
    <link rel="stylesheet" href="login.css"> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <style>
        :root { --cor-ciano: #00F0E1; --cor-bg: #0f1525; --cor-card: rgba(20, 25, 40, 0.9); }
        body { font-family: sans-serif; background: var(--cor-bg); color: white; margin: 0; }
        .container-dash { max-width: 1200px; margin: 0 auto; padding: 20px; }
        
        /* Card Específico de Aulas */
        .card-aulas {
            background: var(--cor-card);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 15px;
            padding: 20px;
            margin-top: 20px;
        }
        .aula-item {
            background: rgba(255,255,255,0.05);
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.85em;
            font-weight: bold;
        }
        .pendente { background: #f39c12; color: #fff; }
        .aceito { background: #2ecc71; color: #fff; }
        .recusado { background: #e74c3c; color: #fff; }
        
        .btn-voltar { display: inline-block; margin-bottom: 20px; color: var(--cor-ciano); text-decoration: none; }
    </style>
</head>
<body>
    <header style="background: rgba(0,0,0,0.5); padding: 20px; text-align: center;">
        <h1 style="color: var(--cor-ciano);">TECHFIT - Área do Aluno</h1>
    </header>

    <div class="container-dash">
        <h2>Olá, <?php echo htmlspecialchars($nomeAluno); ?></h2>
        
        <div class="card-aulas">
            <h3 style="color: var(--cor-ciano); border-bottom: 1px solid #333; padding-bottom: 10px;">
                <i class="fas fa-calendar-alt"></i> Meus Agendamentos
            </h3>

            <?php if ($totalAgendamentos > 0): ?>
                <?php foreach ($agendamentosAluno as $aula): ?>
                    <div class="aula-item">
                        <div>
                            <strong style="font-size: 1.1em;"><?php echo $aula['tipo_aula']; ?></strong><br>
                            <span style="color: #ccc; font-size: 0.9em;">
                                <?php 
                                    // Tenta pegar a data da aula ou data do pedido
                                    $dataExibir = !empty($aula['data_aula']) ? $aula['data_aula'] : $aula['data_agendamento'];
                                    echo date('d/m/Y', strtotime($dataExibir)); 
                                ?> 
                                às <?php echo $aula['horario_aula'] ?? '00:00'; ?>
                            </span>
                        </div>
                        
                        <?php if ($aula['status'] == 'pendente'): ?>
                            <span class="status-badge pendente">⏳ PENDENTE</span>
                        <?php elseif ($aula['status'] == 'aceito'): ?>
                            <span class="status-badge aceito">✅ CONFIRMADO</span>
                        <?php elseif ($aula['status'] == 'recusado'): ?>
                            <span class="status-badge recusado">❌ RECUSADO</span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align: center; padding: 20px; color: #888;">Nenhuma aula agendada ainda.</p>
            <?php endif; ?>

            <div style="text-align: center; margin-top: 20px;">
                <a href="agendamento.php" style="background: var(--cor-ciano); color: #000; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">Agendar Nova Aula</a>
            </div>
        </div>

        <div style="margin-top: 30px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="card-aulas">
                <h3><i class="fas fa-crown"></i> Planos</h3>
                <p>Gerencie seus planos na aba Planos.</p>
            </div>
            <div class="card-aulas">
                <h3><i class="fas fa-shopping-bag"></i> Produtos</h3>
                <p>Histórico de compras disponível.</p>
            </div>
        </div>

    </div>
</body>
</html>