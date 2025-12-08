-- ========================================
-- SISTEMA TECHFIT - ACADEMIA INTELIGENTE
-- Script de Criação do Banco de Dados
-- Versão: 2.0 (Completo e Corrigido)
-- ========================================

-- Criação do banco de dados
DROP DATABASE IF EXISTS academia;
CREATE DATABASE academia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE academia;

-- ========================================
-- TABELAS PRINCIPAIS
-- ========================================

-- Tabela: Cliente
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

-- Tabela: Funcionário
CREATE TABLE funcionario (
    id_funcionario INT AUTO_INCREMENT PRIMARY KEY,
    nome_funcionario VARCHAR(255) NOT NULL,
    cpf VARCHAR(15) NOT NULL UNIQUE,
    salario DECIMAL(7,2),
    carga_horaria DATETIME,
    id_cliente INT,
    id_suporte INT,
    id_avaliacao INT,
    data_contratacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela: Avaliação
CREATE TABLE avaliacao (
    id_avaliacao INT AUTO_INCREMENT PRIMARY KEY,
    descricao VARCHAR(255) NOT NULL,
    data_avaliacao DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela: Planos
CREATE TABLE planos (
    id_planos INT AUTO_INCREMENT PRIMARY KEY,
    nome_planos VARCHAR(255) NOT NULL,
    descricao VARCHAR(255) NOT NULL,
    valor DECIMAL(7,2) NOT NULL,
    id_cliente INT,
    id_funcionario INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela: Agendamento
CREATE TABLE agendamento (
    id_agendamento INT AUTO_INCREMENT PRIMARY KEY,
    tipo_aula VARCHAR(255) NOT NULL,
    data_agendamento DATETIME NOT NULL,
    id_cliente INT,
    id_avaliacao INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela: Produtos
CREATE TABLE produtos (
    id_produtos INT AUTO_INCREMENT PRIMARY KEY,
    nome_produto VARCHAR(255) NOT NULL,
    tipo_produto VARCHAR(255) NOT NULL,
    categoria VARCHAR(255) NOT NULL,
    preco DECIMAL(7,2) NOT NULL,
    quantidade INT DEFAULT 0,
    id_cliente INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela: Suporte
CREATE TABLE suporte (
    id_suporte INT AUTO_INCREMENT PRIMARY KEY,
    descricao VARCHAR(255) NOT NULL,
    categoria_suporte VARCHAR(255) NOT NULL,
    id_cliente INT,
    status_suporte VARCHAR(50) DEFAULT 'pendente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ========================================
-- TABELAS DE TRANSAÇÕES
-- ========================================

-- Tabela: Venda
CREATE TABLE venda (
    id_venda INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_produtos INT NOT NULL,
    quantidade INT NOT NULL,
    data_venda DATETIME DEFAULT CURRENT_TIMESTAMP,
    valor_total DECIMAL(10,2) NOT NULL
) ENGINE=InnoDB;

-- Tabela: Forma de Pagamento
CREATE TABLE forma_pagamento (
    id_forma_pagamento INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('cartao', 'pix', 'boleto', 'dinheiro') NOT NULL,
    descricao VARCHAR(255)
) ENGINE=InnoDB;

-- Tabela: Pagamento
CREATE TABLE pagamento (
    id_pagamento INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_planos INT NOT NULL,
    data_pagamento DATETIME DEFAULT CURRENT_TIMESTAMP,
    valor_pago DECIMAL(7,2) NOT NULL,
    status_pagamento ENUM('pago', 'pendente') DEFAULT 'pendente',
    id_forma_pagamento INT
) ENGINE=InnoDB;

-- Tabela: Fatura
CREATE TABLE fatura (
    id_fatura INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_pagamento INT,
    valor_total DECIMAL(10,2) NOT NULL,
    vencimento DATE NOT NULL,
    status ENUM('aberta','paga','vencida') DEFAULT 'aberta',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ========================================
-- TABELAS DE TREINO
-- ========================================

-- Tabela: Treinos
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

-- Tabela: Exercício
CREATE TABLE exercicio (
    id_exercicio INT AUTO_INCREMENT PRIMARY KEY,
    id_treino INT NOT NULL,
    nome_exercicio VARCHAR(200) NOT NULL,
    serie INT,
    repeticoes INT,
    carga DECIMAL(5,2)
) ENGINE=InnoDB;

-- ========================================
-- TABELAS DE CONTROLE
-- ========================================

-- Tabela: Login (ESSENCIAL para autenticação)
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

-- Tabela: Presença
CREATE TABLE presenca (
    id_presenca INT AUTO_INCREMENT PRIMARY KEY,
    id_agendamento INT NOT NULL,
    id_cliente INT NOT NULL,
    data_presenca DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('presente', 'faltou') DEFAULT 'presente'
) ENGINE=InnoDB;

-- Tabela: Notificação
CREATE TABLE notificacao (
    id_notificacao INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    mensagem TEXT NOT NULL,
    data_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('lida', 'não lida') DEFAULT 'não lida'
) ENGINE=InnoDB;

-- Tabela: Log de Acesso
CREATE TABLE log_acesso (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    tipo_usuario ENUM('cliente', 'funcionario') NOT NULL,
    data_login DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip_usuario VARCHAR(50)
) ENGINE=InnoDB;

-- Tabela: Log de Atividade
CREATE TABLE log_atividade (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    tipo_usuario ENUM('cliente', 'funcionario') NOT NULL,
    acao VARCHAR(255) NOT NULL,
    data_acao DATETIME DEFAULT CURRENT_TIMESTAMP,
    descricao TEXT
) ENGINE=InnoDB;

-- Tabela: Mensagem/Contato
CREATE TABLE mensagem (
    id_mensagem INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    assunto VARCHAR(255) NOT NULL,
    mensagem TEXT NOT NULL,
    data_envio DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela: Carrinho
CREATE TABLE carrinho (
    id_carrinho INT AUTO_INCREMENT PRIMARY KEY,
    id_produtos INT NOT NULL,
    quantidade_carrinho INT NOT NULL,
    valor_unitario DECIMAL(7,2) NOT NULL
) ENGINE=InnoDB;

-- Tabela: Fornecedores
CREATE TABLE fornecedores (
    id_fornecedor INT AUTO_INCREMENT PRIMARY KEY,
    nome_fornecedor VARCHAR(200) NOT NULL,
    cnpj_fornecedor VARCHAR(14) NOT NULL UNIQUE,
    telefone_fornecedor VARCHAR(16) NOT NULL,
    email VARCHAR(100) NOT NULL,
    endereco VARCHAR(200) NOT NULL
) ENGINE=InnoDB;

-- Tabela: Possui (Relacionamento Cliente-Avaliação)
CREATE TABLE possui (
    id_cliente INT,
    id_avaliacao INT,
    PRIMARY KEY (id_cliente, id_avaliacao)
) ENGINE=InnoDB;

-- ========================================
-- FOREIGN KEYS (Relacionamentos)
-- ========================================

ALTER TABLE agendamento
    ADD FOREIGN KEY (id_cliente) REFERENCES cliente (id_cliente) ON DELETE CASCADE,
    ADD FOREIGN KEY (id_avaliacao) REFERENCES avaliacao (id_avaliacao) ON DELETE SET NULL;

ALTER TABLE produtos
    ADD FOREIGN KEY (id_cliente) REFERENCES cliente (id_cliente) ON DELETE SET NULL;

ALTER TABLE suporte
    ADD FOREIGN KEY (id_cliente) REFERENCES cliente (id_cliente) ON DELETE CASCADE;

ALTER TABLE funcionario
    ADD FOREIGN KEY (id_cliente) REFERENCES cliente (id_cliente) ON DELETE SET NULL,
    ADD FOREIGN KEY (id_suporte) REFERENCES suporte (id_suporte) ON DELETE SET NULL,
    ADD FOREIGN KEY (id_avaliacao) REFERENCES avaliacao (id_avaliacao) ON DELETE SET NULL;

ALTER TABLE planos
    ADD FOREIGN KEY (id_cliente) REFERENCES cliente (id_cliente) ON DELETE CASCADE,
    ADD FOREIGN KEY (id_funcionario) REFERENCES funcionario (id_funcionario) ON DELETE SET NULL;

ALTER TABLE venda
    ADD FOREIGN KEY (id_cliente) REFERENCES cliente (id_cliente) ON DELETE CASCADE,
    ADD FOREIGN KEY (id_produtos) REFERENCES produtos (id_produtos) ON DELETE CASCADE;

ALTER TABLE pagamento
    ADD FOREIGN KEY (id_cliente) REFERENCES cliente (id_cliente) ON DELETE CASCADE,
    ADD FOREIGN KEY (id_planos) REFERENCES planos (id_planos) ON DELETE CASCADE,
    ADD FOREIGN KEY (id_forma_pagamento) REFERENCES forma_pagamento (id_forma_pagamento) ON DELETE SET NULL;

ALTER TABLE fatura
    ADD FOREIGN KEY (id_cliente) REFERENCES cliente (id_cliente) ON DELETE CASCADE,
    ADD FOREIGN KEY (id_pagamento) REFERENCES pagamento (id_pagamento) ON DELETE SET NULL;

ALTER TABLE treinos
    ADD FOREIGN KEY (id_cliente) REFERENCES cliente (id_cliente) ON DELETE CASCADE,
    ADD FOREIGN KEY (id_funcionario) REFERENCES funcionario (id_funcionario) ON DELETE SET NULL;

ALTER TABLE exercicio
    ADD FOREIGN KEY (id_treino) REFERENCES treinos (id_treino) ON DELETE CASCADE;

ALTER TABLE login
    ADD FOREIGN KEY (id_cliente) REFERENCES cliente (id_cliente) ON DELETE CASCADE,
    ADD FOREIGN KEY (id_funcionario) REFERENCES funcionario (id_funcionario) ON DELETE CASCADE;

ALTER TABLE presenca
    ADD FOREIGN KEY (id_agendamento) REFERENCES agendamento (id_agendamento) ON DELETE CASCADE,
    ADD FOREIGN KEY (id_cliente) REFERENCES cliente (id_cliente) ON DELETE CASCADE;

ALTER TABLE notificacao
    ADD FOREIGN KEY (id_cliente) REFERENCES cliente (id_cliente) ON DELETE CASCADE;

ALTER TABLE carrinho
    ADD FOREIGN KEY (id_produtos) REFERENCES produtos (id_produtos) ON DELETE CASCADE;

ALTER TABLE possui
    ADD FOREIGN KEY (id_cliente) REFERENCES cliente (id_cliente) ON DELETE CASCADE,
    ADD FOREIGN KEY (id_avaliacao) REFERENCES avaliacao (id_avaliacao) ON DELETE CASCADE;

-- ========================================
-- DADOS INICIAIS
-- ========================================

-- Clientes
INSERT INTO cliente (nome_cliente, email, endereco, telefone, genero, cpf) VALUES
('Carlos Pereira', 'carlos.pereira@gmail.com', 'Av. Brasil, 450', '55 11 98234-5678', 'Masculino', '321.654.987-00'),
('Lucas Andrade', 'lucas.andrade@hotmail.com', 'Rua das Flores, 87', '55 21 97654-3321', 'Masculino', '789.123.456-11'),
('Rogério Souza', 'rogerio.souza@yahoo.com', 'Rua XV de Novembro, 300', '55 41 99544-2211', 'Masculino', '654.321.789-22'),
('Douglas Martins', 'douglas.martins@gmail.com', 'Av. Independência, 1220', '55 31 98432-8876', 'Masculino', '987.654.321-33'),
('Nicolas Rocha', 'nicolas.rocha@outlook.com', 'Rua das Acácias, 55', '55 51 98123-4455', 'Masculino', '456.789.123-44');

-- Avaliações
INSERT INTO avaliacao (descricao, data_avaliacao) VALUES
('Avaliação física inicial', '2025-01-10 10:00:00'),
('Avaliação de resistência', '2025-02-15 14:30:00'),
('Avaliação de força', '2025-03-20 09:15:00'),
('Reavaliação trimestral', '2025-06-05 11:00:00'),
('Avaliação nutricional', '2025-07-18 16:00:00');

-- Agendamentos
INSERT INTO agendamento (tipo_aula, data_agendamento, id_cliente, id_avaliacao) VALUES
('Musculação', '2025-12-15 09:00:00', 1, 1),
('Spinning', '2025-12-16 08:00:00', 2, 2),
('Pilates', '2025-12-17 10:00:00', 3, 3),
('Crossfit', '2025-12-18 07:30:00', 4, 4),
('Yoga', '2025-12-19 18:00:00', 5, 5);

-- Produtos
INSERT INTO produtos (nome_produto, tipo_produto, categoria, preco, quantidade, id_cliente) VALUES
('Whey Protein', 'Suplemento', 'Nutrição', 199.90, 50, NULL),
('Camiseta Dry Fit', 'Vestuário', 'Roupas', 79.90, 100, NULL),
('Creatina Monohidratada', 'Suplemento', 'Nutrição', 149.90, 30, NULL),
('Luvas de Musculação', 'Acessório', 'Equipamentos', 59.90, 20, NULL),
('Garrafa Térmica', 'Acessório', 'Utilidades', 39.90, 80, NULL);

-- Suporte
INSERT INTO suporte (descricao, categoria_suporte, id_cliente) VALUES
('Dificuldade em acessar o aplicativo', 'Tecnologia', 1),
('Problema na cobrança da mensalidade', 'Financeiro', 2),
('Dúvida sobre treino personalizado', 'Treinamento', 3);

-- Funcionários
INSERT INTO funcionario (nome_funcionario, cpf, salario, carga_horaria, id_cliente, id_suporte, id_avaliacao) VALUES
('Mariana Oliveira', '123.456.789-10', 3500.00, '2025-01-01 08:00:00', 1, 1, 1),
('João Santos', '987.654.321-00', 4200.00, '2025-01-01 09:00:00', 2, 2, 2),
('Fernanda Costa', '654.987.321-55', 3900.00, '2025-01-01 10:00:00', 3, 3, 3);

-- Planos
INSERT INTO planos (nome_planos, descricao, valor, id_cliente, id_funcionario) VALUES
('Plano Mensal', 'Acesso livre por 30 dias', 120.00, 1, 1),
('Plano Trimestral', 'Acesso por 3 meses com desconto', 330.00, 2, 2),
('Plano Semestral', 'Acesso por 6 meses e avaliação gratuita', 600.00, 3, 3);

-- Formas de Pagamento
INSERT INTO forma_pagamento (tipo, descricao) VALUES
('cartao', 'Cartão de crédito Visa ou Mastercard'),
('pix', 'Transferência via PIX'),
('boleto', 'Pagamento por boleto bancário'),
('dinheiro', 'Pagamento em espécie na recepção');

-- Pagamentos
INSERT INTO pagamento (id_cliente, id_planos, valor_pago, status_pagamento, id_forma_pagamento) VALUES
(1, 1, 120.00, 'pago', 1),
(2, 2, 330.00, 'pago', 2),
(3, 3, 600.00, 'pago', 1);

-- Vendas
INSERT INTO venda (id_cliente, id_produtos, quantidade, valor_total) VALUES
(1, 1, 1, 199.90),
(2, 2, 2, 159.80),
(3, 3, 1, 149.90);

-- Faturas
INSERT INTO fatura (id_cliente, id_pagamento, valor_total, vencimento, status) VALUES
(1, 1, 120.00, '2025-12-30', 'paga'),
(2, 2, 330.00, '2026-01-15', 'aberta'),
(3, 3, 600.00, '2026-02-10', 'aberta');

-- Logins (IMPORTANTE: Senhas devem ser password_hash no PHP)
-- Senha padrão para testes: 123456
-- Senhas: carlos_pereira/lucas_andrade/rogerio_souza = "123456" | admin = "admin123"
INSERT INTO login (nome_usuario, senha_usuario, tipo_usuario, id_cliente, id_funcionario) VALUES
('carlos_pereira', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHeFt6nqXqZ3qJdJGqVJnLVFqp1xEOyGay', 'cliente', 1, NULL),
('lucas_andrade', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHeFt6nqXqZ3qJdJGqVJnLVFqp1xEOyGay', 'cliente', 2, NULL),
('rogerio_souza', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHeFt6nqXqZ3qJdJGqVJnLVFqp1xEOyGay', 'cliente', 3, NULL),
('admin', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhkO', 'funcionario', NULL, 1);

-- Notificações
INSERT INTO notificacao (id_cliente, titulo, mensagem, status) VALUES
(1, 'Bem-vindo à TechFit!', 'Obrigado por se cadastrar. Aproveite nossos serviços!', 'não lida'),
(2, 'Agendamento confirmado', 'Sua aula de Spinning foi confirmada para amanhã.', 'lida'),
(3, 'Novo treino disponível', 'Um novo plano de treino foi criado para você!', 'não lida');

-- Log de Acesso
INSERT INTO log_acesso (id_usuario, tipo_usuario, ip_usuario) VALUES
(1, 'cliente', '192.168.0.10'),
(2, 'cliente', '192.168.0.15'),
(1, 'funcionario', '192.168.0.1');

-- ========================================
-- ÍNDICES PARA PERFORMANCE
-- ========================================

CREATE INDEX idx_cliente_email ON cliente(email);
CREATE INDEX idx_cliente_cpf ON cliente(cpf);
CREATE INDEX idx_agendamento_data ON agendamento(data_agendamento);
CREATE INDEX idx_login_usuario ON login(nome_usuario);
CREATE INDEX idx_venda_cliente ON venda(id_cliente);
CREATE INDEX idx_pagamento_cliente ON pagamento(id_cliente);

-- ========================================
-- FIM DO SCRIPT
-- ========================================

-- Credenciais de Teste:
-- ADMIN: usuario = admin | senha = admin123
-- CLIENTE: usuario = carlos_pereira | senha = 123456
