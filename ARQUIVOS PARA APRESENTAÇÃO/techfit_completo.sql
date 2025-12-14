-- ====================================================================================================
-- TECHFIT - SISTEMA DE GESTÃO DE ACADEMIA
-- Script SQL Completo e Unificado
-- ====================================================================================================

DROP DATABASE IF EXISTS academia;
CREATE DATABASE academia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE academia;

-- ====================================================================================================
-- TABELAS PRINCIPAIS
-- ====================================================================================================

-- Cliente
CREATE TABLE cliente (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    nome_cliente VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    endereco VARCHAR(200) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    genero VARCHAR(10) NOT NULL,
    cpf VARCHAR(15) NOT NULL UNIQUE,
    status VARCHAR(20) DEFAULT 'ativo',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Funcionário
CREATE TABLE funcionario (
    id_funcionario INT AUTO_INCREMENT PRIMARY KEY,
    nome_funcionario VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    cpf VARCHAR(14) UNIQUE NOT NULL,
    telefone VARCHAR(20),
    cargo ENUM('admin', 'personal_trainer', 'recepcionista', 'gerente') NOT NULL,
    salario DECIMAL(10,2),
    data_admissao DATE,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Login (Autenticação)
CREATE TABLE login (
    id_login INT AUTO_INCREMENT PRIMARY KEY,
    nome_usuario VARCHAR(100) NOT NULL UNIQUE,
    senha_usuario VARCHAR(255) NOT NULL,
    tipo_usuario ENUM('cliente', 'funcionario') DEFAULT 'cliente',
    id_cliente INT,
    id_funcionario INT,
    ultimo_acesso TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Planos
CREATE TABLE planos (
    id_planos INT AUTO_INCREMENT PRIMARY KEY,
    nome_planos VARCHAR(255) NOT NULL,
    descricao VARCHAR(255) NOT NULL,
    valor DECIMAL(7,2) NOT NULL,
    id_cliente INT,
    id_funcionario INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Aulas (Grade de Horários)
CREATE TABLE aulas (
    id_aula INT AUTO_INCREMENT PRIMARY KEY,
    nome_aula VARCHAR(200) NOT NULL,
    dia_semana ENUM('Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo') NOT NULL,
    horario TIME NOT NULL,
    professor VARCHAR(200) NOT NULL,
    vagas_totais INT NOT NULL DEFAULT 20,
    vagas_ocupadas INT NOT NULL DEFAULT 0,
    descricao TEXT,
    status ENUM('ativa', 'cancelada') DEFAULT 'ativa',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Agendamento
CREATE TABLE agendamento (
    id_agendamento INT AUTO_INCREMENT PRIMARY KEY,
    tipo_aula VARCHAR(255) NOT NULL,
    data_agendamento DATETIME NOT NULL,
    id_cliente INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Produtos
CREATE TABLE produtos (
    id_produtos INT AUTO_INCREMENT PRIMARY KEY,
    nome_produto VARCHAR(255) NOT NULL,
    tipo_produto VARCHAR(100) NOT NULL,
    categoria VARCHAR(100),
    preco DECIMAL(10,2) NOT NULL,
    quantidade_estoque INT NOT NULL DEFAULT 0,
    url_imagem VARCHAR(500),
    descricao TEXT,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Venda
CREATE TABLE venda (
    id_venda INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_produtos INT NOT NULL,
    quantidade INT NOT NULL,
    data_venda DATETIME DEFAULT CURRENT_TIMESTAMP,
    valor_total DECIMAL(10,2) NOT NULL
) ENGINE=InnoDB;

-- Pedidos (Histórico completo de compras)
CREATE TABLE pedidos (
    id_pedido INT AUTO_INCREMENT PRIMARY KEY,
    numero_pedido VARCHAR(50) DEFAULT NULL,
    id_cliente INT DEFAULT NULL,
    dados_cliente JSON DEFAULT NULL,
    itens JSON DEFAULT NULL,
    subtotal DECIMAL(10,2) DEFAULT 0,
    frete DECIMAL(10,2) DEFAULT 0,
    desconto DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) DEFAULT 0,
    metodo_pagamento VARCHAR(50) DEFAULT NULL,
    dados_pagamento JSON DEFAULT NULL,
    status VARCHAR(50) DEFAULT 'pendente',
    data_pedido DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Forma de Pagamento
CREATE TABLE forma_pagamento (
    id_forma_pagamento INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('cartao', 'pix', 'boleto', 'dinheiro') NOT NULL,
    descricao VARCHAR(255)
) ENGINE=InnoDB;

-- Pagamento (Planos)
CREATE TABLE pagamento (
    id_pagamento INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_planos INT NOT NULL,
    data_pagamento DATETIME DEFAULT CURRENT_TIMESTAMP,
    valor_pago DECIMAL(7,2) NOT NULL,
    status_pagamento ENUM('pago', 'pendente') DEFAULT 'pendente',
    id_forma_pagamento INT
) ENGINE=InnoDB;

-- Notificação
CREATE TABLE notificacao (
    id_notificacao INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    mensagem TEXT NOT NULL,
    tipo ENUM('geral', 'individual') DEFAULT 'geral',
    id_cliente INT NULL,
    data_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('lida', 'não lida') DEFAULT 'não lida',
    prioridade ENUM('baixa', 'media', 'alta') DEFAULT 'media'
) ENGINE=InnoDB;

-- Avaliação
CREATE TABLE avaliacao (
    id_avaliacao INT AUTO_INCREMENT PRIMARY KEY,
    descricao VARCHAR(255) NOT NULL,
    data_avaliacao DATETIME,
    id_cliente INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Suporte
CREATE TABLE suporte (
    id_suporte INT AUTO_INCREMENT PRIMARY KEY,
    descricao VARCHAR(255) NOT NULL,
    categoria_suporte VARCHAR(255) NOT NULL,
    id_cliente INT,
    status_suporte VARCHAR(50) DEFAULT 'pendente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Treinos
CREATE TABLE treinos (
    id_treino INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_funcionario INT,
    nome_treino VARCHAR(200) NOT NULL,
    objetivo VARCHAR(100),
    duracao INT,
    data_inicio DATE,
    data_fim DATE
) ENGINE=InnoDB;

-- Exercício
CREATE TABLE exercicio (
    id_exercicio INT AUTO_INCREMENT PRIMARY KEY,
    id_treino INT NOT NULL,
    nome_exercicio VARCHAR(200) NOT NULL,
    serie INT,
    repeticoes INT,
    carga DECIMAL(5,2)
) ENGINE=InnoDB;

-- Presença
CREATE TABLE presenca (
    id_presenca INT AUTO_INCREMENT PRIMARY KEY,
    id_agendamento INT NOT NULL,
    id_cliente INT NOT NULL,
    data_presenca DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('presente', 'faltou') DEFAULT 'presente'
) ENGINE=InnoDB;

-- ====================================================================================================
-- FOREIGN KEYS
-- ====================================================================================================

ALTER TABLE login
    ADD FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente) ON DELETE CASCADE,
    ADD FOREIGN KEY (id_funcionario) REFERENCES funcionario(id_funcionario) ON DELETE CASCADE;

ALTER TABLE planos
    ADD FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente) ON DELETE CASCADE,
    ADD FOREIGN KEY (id_funcionario) REFERENCES funcionario(id_funcionario) ON DELETE SET NULL;

ALTER TABLE agendamento
    ADD FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente) ON DELETE CASCADE;

ALTER TABLE venda
    ADD FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente) ON DELETE CASCADE,
    ADD FOREIGN KEY (id_produtos) REFERENCES produtos(id_produtos) ON DELETE CASCADE;

ALTER TABLE pagamento
    ADD FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente) ON DELETE CASCADE,
    ADD FOREIGN KEY (id_planos) REFERENCES planos(id_planos) ON DELETE CASCADE,
    ADD FOREIGN KEY (id_forma_pagamento) REFERENCES forma_pagamento(id_forma_pagamento) ON DELETE SET NULL;

ALTER TABLE notificacao
    ADD FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente) ON DELETE CASCADE;

ALTER TABLE avaliacao
    ADD FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente) ON DELETE CASCADE;

ALTER TABLE suporte
    ADD FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente) ON DELETE CASCADE;

ALTER TABLE treinos
    ADD FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente) ON DELETE CASCADE,
    ADD FOREIGN KEY (id_funcionario) REFERENCES funcionario(id_funcionario) ON DELETE SET NULL;

ALTER TABLE exercicio
    ADD FOREIGN KEY (id_treino) REFERENCES treinos(id_treino) ON DELETE CASCADE;

ALTER TABLE presenca
    ADD FOREIGN KEY (id_agendamento) REFERENCES agendamento(id_agendamento) ON DELETE CASCADE,
    ADD FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente) ON DELETE CASCADE;

-- ====================================================================================================
-- ÍNDICES PARA PERFORMANCE
-- ====================================================================================================

CREATE INDEX idx_cliente_email ON cliente(email);
CREATE INDEX idx_cliente_cpf ON cliente(cpf);
CREATE INDEX idx_aulas_dia_horario ON aulas(dia_semana, horario);
CREATE INDEX idx_agendamento_data ON agendamento(data_agendamento);
CREATE INDEX idx_produtos_categoria ON produtos(categoria);
CREATE INDEX idx_produtos_status ON produtos(status);
CREATE INDEX idx_login_usuario ON login(nome_usuario);
CREATE INDEX idx_venda_cliente ON venda(id_cliente);
CREATE INDEX idx_pagamento_cliente ON pagamento(id_cliente);
CREATE INDEX idx_notificacao_status ON notificacao(status);

-- ====================================================================================================
-- DADOS INICIAIS
-- ====================================================================================================

-- Funcionários
INSERT INTO funcionario (nome_funcionario, email, cpf, telefone, cargo, data_admissao) VALUES
('Admin Master', 'admin@techfit.com', '000.000.000-00', '(19) 99999-9999', 'admin', '2024-01-01'),
('Ana Silva', 'ana.silva@techfit.com', '111.111.111-11', '(19) 98888-8888', 'personal_trainer', '2024-01-15'),
('Carlos Mendes', 'carlos.mendes@techfit.com', '222.222.222-22', '(19) 97777-7777', 'personal_trainer', '2024-02-01');

-- Clientes
INSERT INTO cliente (nome_cliente, email, endereco, telefone, genero, cpf) VALUES
('Carlos Pereira', 'carlos.pereira@gmail.com', 'Av. Brasil, 450', '11 98234-5678', 'Masculino', '321.654.987-00'),
('Lucas Andrade', 'lucas.andrade@hotmail.com', 'Rua das Flores, 87', '21 97654-3321', 'Masculino', '789.123.456-11'),
('Rogério Souza', 'rogerio.souza@yahoo.com', 'Rua XV de Novembro, 300', '41 99544-2211', 'Masculino', '654.321.789-22');

-- Logins (Senha padrão: "123456" para clientes | "admin123" para admin)
INSERT INTO login (nome_usuario, senha_usuario, tipo_usuario, id_cliente, id_funcionario) VALUES
('admin', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhkO', 'funcionario', NULL, 1),
('carlos_pereira', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHeFt6nqXqZ3qJdJGqVJnLVFqp1xEOyGay', 'cliente', 1, NULL),
('lucas_andrade', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHeFt6nqXqZ3qJdJGqVJnLVFqp1xEOyGay', 'cliente', 2, NULL),
('rogerio_souza', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHeFt6nqXqZ3qJdJGqVJnLVFqp1xEOyGay', 'cliente', 3, NULL);

-- Aulas
INSERT INTO aulas (nome_aula, dia_semana, horario, professor, vagas_totais, descricao) VALUES
('Yoga Matinal', 'Segunda', '07:00:00', 'Prof. Ana Silva', 20, 'Aula de Yoga para iniciantes'),
('HIIT Intenso', 'Segunda', '18:00:00', 'Prof. Carlos Mendes', 25, 'Treino intervalado de alta intensidade'),
('Pilates', 'Terça', '09:00:00', 'Prof. Marina Costa', 15, 'Fortalecimento e alongamento'),
('Spinning', 'Terça', '19:00:00', 'Prof. Roberto Lima', 30, 'Ciclismo indoor'),
('Funcional', 'Quarta', '18:30:00', 'Prof. João Pedro', 20, 'Treino funcional'),
('Zumba', 'Quinta', '19:00:00', 'Prof. Juliana Santos', 35, 'Dança fitness'),
('Crossfit', 'Sexta', '17:00:00', 'Prof. Marcos Vieira', 15, 'Treino de alta performance');

-- Produtos
INSERT INTO produtos (nome_produto, tipo_produto, categoria, preco, quantidade_estoque, url_imagem, descricao) VALUES
('Whey Protein 1kg', 'suplemento', 'Proteína', 89.90, 50, 'imagens/whey.jpg', 'Proteína concentrada para ganho de massa'),
('Creatina 300g', 'suplemento', 'Energia', 45.00, 30, 'imagens/creatina.jpg', 'Aumenta força e resistência'),
('BCAA 120 cápsulas', 'suplemento', 'Recuperação', 65.00, 25, 'imagens/bcaa.jpg', 'Aminoácidos essenciais'),
('Camiseta Dry Fit', 'roupa', 'Vestuário', 49.90, 100, 'imagens/camiseta.jpg', 'Camiseta esportiva respirável'),
('Shorts de Treino', 'roupa', 'Vestuário', 59.90, 80, 'imagens/shorts.jpg', 'Shorts confortável para treino'),
('Luvas de Treino', 'acessorio', 'Equipamento', 35.00, 40, 'imagens/luvas.jpg', 'Proteção para mãos'),
('Garrafa Térmica', 'acessorio', 'Hidratação', 29.90, 60, 'imagens/garrafa.jpg', 'Mantém água gelada por 12h');

-- Notificações
INSERT INTO notificacao (titulo, mensagem, tipo, prioridade) VALUES
('Bem-vindo à TechFit!', 'Seja bem-vindo à melhor academia da região! Aproveite nossos equipamentos de última geração.', 'geral', 'media'),
('Manutenção Programada', 'Academia fechada no dia 25/12 para manutenção. Feliz Natal!', 'geral', 'alta'),
('Nova Modalidade', 'Agora temos aulas de CrossFit! Venha experimentar.', 'geral', 'media');

-- Formas de Pagamento
INSERT INTO forma_pagamento (tipo, descricao) VALUES
('cartao', 'Cartão de crédito Visa ou Mastercard'),
('pix', 'Transferência via PIX'),
('boleto', 'Pagamento por boleto bancário'),
('dinheiro', 'Pagamento em espécie na recepção');

-- ====================================================================================================
-- VIEWS
-- ====================================================================================================

-- Aulas com ocupação
CREATE OR REPLACE VIEW vw_aulas_ocupacao AS
SELECT 
    id_aula, nome_aula, dia_semana, horario, professor,
    vagas_totais, vagas_ocupadas,
    (vagas_totais - vagas_ocupadas) as vagas_disponiveis,
    ROUND((vagas_ocupadas / vagas_totais) * 100, 2) as taxa_ocupacao
FROM aulas WHERE status = 'ativa';

-- Produtos com baixo estoque
CREATE OR REPLACE VIEW vw_produtos_baixo_estoque AS
SELECT id_produtos, nome_produto, tipo_produto, quantidade_estoque, preco
FROM produtos
WHERE quantidade_estoque < 10 AND status = 'ativo';

-- Estatísticas gerais
CREATE OR REPLACE VIEW vw_estatisticas_geral AS
SELECT 
    (SELECT COUNT(*) FROM cliente) as total_alunos,
    (SELECT COUNT(*) FROM funcionario WHERE status = 'ativo') as total_funcionarios,
    (SELECT COUNT(*) FROM aulas WHERE status = 'ativa') as total_aulas,
    (SELECT COUNT(*) FROM produtos WHERE status = 'ativo') as total_produtos,
    (SELECT COALESCE(SUM(valor_pago), 0) FROM pagamento WHERE MONTH(data_pagamento) = MONTH(CURRENT_DATE())) as faturamento_mes;

-- ====================================================================================================
-- TRIGGERS
-- ====================================================================================================

-- Atualizar vagas ao inserir agendamento
DELIMITER //
CREATE TRIGGER trg_atualizar_vagas_aula
AFTER INSERT ON agendamento
FOR EACH ROW
BEGIN
    UPDATE aulas 
    SET vagas_ocupadas = vagas_ocupadas + 1
    WHERE nome_aula = NEW.tipo_aula
    AND vagas_ocupadas < vagas_totais
    LIMIT 1;
END//
DELIMITER ;

-- Atualizar estoque ao realizar venda
DELIMITER //
CREATE TRIGGER trg_atualizar_estoque_venda
AFTER INSERT ON venda
FOR EACH ROW
BEGIN
    UPDATE produtos
    SET quantidade_estoque = quantidade_estoque - NEW.quantidade
    WHERE id_produtos = NEW.id_produtos;
END//
DELIMITER ;

-- ====================================================================================================
-- CREDENCIAIS DE TESTE
-- ====================================================================================================
-- ADMIN: usuario = admin | senha = admin123
-- CLIENTE: usuario = carlos_pereira | senha = 123456
-- ====================================================================================================
