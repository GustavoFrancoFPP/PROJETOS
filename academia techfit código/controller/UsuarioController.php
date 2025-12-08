<?php
class UsuarioController {
    private $conn;

    public function __construct() {
        require_once __DIR__ . '/../config/Connection.php';
        $this->conn = Connection::getInstance();
    }

    public function cadastrar($nome, $email, $senha, $endereco, $telefone, $genero, $cpf) {
        try {
            if (!$this->conn) {
                return "Erro: Não foi possível conectar ao banco de dados";
            }

            // Verifica se email já existe
            $stmt = $this->conn->prepare("SELECT id_cliente FROM cliente WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                return "Erro: Email já cadastrado!";
            }

            // Verifica se CPF já existe
            $stmt = $this->conn->prepare("SELECT id_cliente FROM cliente WHERE cpf = ?");
            $stmt->execute([$cpf]);
            if ($stmt->rowCount() > 0) {
                return "Erro: CPF já cadastrado!";
            }

            // Insere o cliente
            $stmt = $this->conn->prepare("INSERT INTO cliente (nome_cliente, email, endereco, telefone, genero, cpf, status) VALUES (?, ?, ?, ?, ?, ?, 'ativo')");
            $stmt->execute([$nome, $email, $endereco, $telefone, $genero, $cpf]);
            $idCliente = $this->conn->lastInsertId();

            // Cria o login
            $nomeUsuario = $this->gerarNomeUsuario($nome);
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            
            $stmt = $this->conn->prepare("INSERT INTO login (nome_usuario, senha_usuario, tipo_usuario, id_cliente, id_funcionario) VALUES (?, ?, 'cliente', ?, NULL)");
            $stmt->execute([$nomeUsuario, $senhaHash, $idCliente]);

            return $nomeUsuario; // Retorna o nome de usuário gerado

        } catch (PDOException $e) {
            error_log("Erro no cadastro: " . $e->getMessage());
            return "Erro no cadastro: " . $e->getMessage();
        }
    }

    public function login($nomeUsuario, $senha) {
        try {
            if (!$this->conn) {
                error_log("Conexão não estabelecida");
                return false;
            }

            // Busca usuário no login
            $stmt = $this->conn->prepare("SELECT l.*, c.email, c.nome_cliente, f.nome_funcionario 
                                   FROM login l 
                                   LEFT JOIN cliente c ON l.id_cliente = c.id_cliente 
                                   LEFT JOIN funcionario f ON l.id_funcionario = f.id_funcionario 
                                   WHERE l.nome_usuario = ?");
            $stmt->execute([$nomeUsuario]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                error_log("Usuário não encontrado: " . $nomeUsuario);
                return false;
            }

            if (password_verify($senha, $usuario['senha_usuario'])) {
                $this->iniciarSessaoUsuario($usuario);
                error_log("Login bem-sucedido para: " . $nomeUsuario);
                return true;
            } else {
                error_log("Senha incorreta para: " . $nomeUsuario);
                return false;
            }

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
        
        // Atualiza o último acesso
        try {
            $stmt = $this->conn->prepare("UPDATE login SET ultimo_acesso = NOW() WHERE nome_usuario = ?");
            $stmt->execute([$usuario['nome_usuario']]);
        } catch (PDOException $e) {
            error_log("Erro ao atualizar ultimo_acesso: " . $e->getMessage());
        }
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