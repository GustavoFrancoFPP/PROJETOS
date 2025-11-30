<?php
class UsuarioController {
    private $db;

    public function __construct() {
        require_once 'Database.php';
        $this->db = new Database();
    }

    public function cadastrar($nome, $email, $senha, $endereco, $telefone, $genero, $cpf) {
        try {
            $conn = $this->db->getConnection();
            
            if (!$conn) {
                return "Erro: Não foi possível conectar ao banco de dados";
            }

            // Verifica se email já existe
            $stmt = $conn->prepare("SELECT id_cliente FROM cliente WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                return "Email já cadastrado!";
            }

            // Verifica se CPF já existe
            $stmt = $conn->prepare("SELECT id_cliente FROM cliente WHERE cpf = ?");
            $stmt->execute([$cpf]);
            if ($stmt->rowCount() > 0) {
                return "CPF já cadastrado!";
            }

            // Insere o cliente
            $stmt = $conn->prepare("INSERT INTO cliente (nome_cliente, email, endereco, telefone, genero, cpf, status) VALUES (?, ?, ?, ?, ?, ?, 'ativo')");
            $stmt->execute([$nome, $email, $endereco, $telefone, $genero, $cpf]);
            $idCliente = $conn->lastInsertId();

            // Cria o login
            $nomeUsuario = $this->gerarNomeUsuario($nome);
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("INSERT INTO login (nome_usuario, senha_usuario, tipo_usuario, id_cliente, id_funcionario) VALUES (?, ?, 'cliente', ?, NULL)");
            $stmt->execute([$nomeUsuario, $senhaHash, $idCliente]);

            return $nomeUsuario; // Retorna o nome de usuário gerado

        } catch (PDOException $e) {
            error_log("Erro no cadastro: " . $e->getMessage());
            return "Erro no cadastro: " . $e->getMessage();
        }
    }

    public function login($nomeUsuario, $senha) {
        try {
            $conn = $this->db->getConnection();
            
            if (!$conn) {
                return false;
            }

            // Busca usuário no login
            $stmt = $conn->prepare("SELECT l.*, c.email, c.nome_cliente, f.nome_funcionario 
                                   FROM login l 
                                   LEFT JOIN cliente c ON l.id_cliente = c.id_cliente 
                                   LEFT JOIN funcionario f ON l.id_funcionario = f.id_funcionario 
                                   WHERE l.nome_usuario = ?");
            $stmt->execute([$nomeUsuario]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario && password_verify($senha, $usuario['senha_usuario'])) {
                $this->iniciarSessaoUsuario($usuario);
                return true;
            }

            return false;

        } catch (PDOException $e) {
            error_log("Erro no login: " . $e->getMessage());
            return false;
        }
    }

    private function iniciarSessaoUsuario($usuario) {
        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = $usuario['id_cliente'] ?? $usuario['id_funcionario'];
        $_SESSION['user_nome'] = $usuario['nome_cliente'] ?? $usuario['nome_funcionario'];
        $_SESSION['user_email'] = $usuario['email'];
        $_SESSION['tipo_usuario'] = $usuario['tipo_usuario'];
        $_SESSION['nome_usuario'] = $usuario['nome_usuario'];
        
        // Debug - mostra na tela (remova depois)
        echo "<script>console.log('Sessão:', " . json_encode([
            'user_id' => $_SESSION['user_id'],
            'user_nome' => $_SESSION['user_nome'],
            'tipo_usuario' => $_SESSION['tipo_usuario']
        ]) . ")</script>";
    }

    private function gerarNomeUsuario($nome) {
        $nomeUsuario = strtolower(trim($nome));
        $nomeUsuario = preg_replace('/[^a-z0-9]/', '_', $nomeUsuario);
        $nomeUsuario = preg_replace('/_+/', '_', $nomeUsuario);
        $random = rand(100, 999);
        return $nomeUsuario . '_' . $random;
    }
}
?>