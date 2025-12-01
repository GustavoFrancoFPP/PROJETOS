create database academia;
use academia;

CREATE TABLE cliente (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    nome_cliente VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    endereco VARCHAR(200) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    genero VARCHAR(10) NOT NULL,
    cpf VARCHAR(15) NOT NULL
);

CREATE TABLE avaliacao (
    id_avaliacao INT AUTO_INCREMENT PRIMARY KEY,
    descricao VARCHAR(255) NOT NULL,
    data_avaliacao DATETIME
);

CREATE TABLE agendamento (
    id_agendamento INT AUTO_INCREMENT PRIMARY KEY,
    tipo_aula VARCHAR(255) NOT NULL,
    data_agendamento DATETIME,
    id_cliente INT,
    id_avaliacao INT,
    FOREIGN KEY (id_cliente) REFERENCES cliente (id_cliente),
    FOREIGN KEY (id_avaliacao) REFERENCES avaliacao (id_avaliacao)
);

CREATE TABLE produtos (
    id_produtos INT AUTO_INCREMENT PRIMARY KEY,
    nome_produto VARCHAR(255) NOT NULL,
    tipo_produto VARCHAR(255) NOT NULL,
    categoria VARCHAR(255) NOT NULL,
    preco DECIMAL(7,2),
    quantidade INT,
    id_cliente INT,
    FOREIGN KEY (id_cliente) REFERENCES cliente (id_cliente)
);

CREATE TABLE suporte (
    id_suporte INT AUTO_INCREMENT PRIMARY KEY,
    descricao VARCHAR(255) NOT NULL,
    categoria_suporte VARCHAR(255) NOT NULL,
    id_cliente INT,
    FOREIGN KEY (id_cliente) REFERENCES cliente (id_cliente)
);

CREATE TABLE funcionario (
    id_funcionario INT AUTO_INCREMENT PRIMARY KEY,
    nome_funcionario VARCHAR(255) NOT NULL,
    cpf VARCHAR(15) NOT NULL,
    salario DECIMAL(7,2),
    carga_horaria DATETIME,
    id_cliente INT,
    id_suporte INT,
    id_avaliacao INT,
    FOREIGN KEY (id_cliente) REFERENCES cliente (id_cliente),
    FOREIGN KEY (id_suporte) REFERENCES suporte (id_suporte),
    FOREIGN KEY (id_avaliacao) REFERENCES avaliacao (id_avaliacao)
);

CREATE TABLE planos (
    id_planos INT AUTO_INCREMENT PRIMARY KEY,
    nome_planos VARCHAR(255) NOT NULL,
    descricao VARCHAR(255) NOT NULL,
    valor DECIMAL(7,2),
    id_cliente INT,
    id_funcionario INT,
    FOREIGN KEY (id_cliente) REFERENCES cliente (id_cliente),
    FOREIGN KEY (id_funcionario) REFERENCES funcionario (id_funcionario)
);

CREATE TABLE possui (
    id_cliente INT,
    id_avaliacao INT,
    PRIMARY KEY (id_cliente, id_avaliacao),
    FOREIGN KEY (id_cliente) REFERENCES cliente (id_cliente),
    FOREIGN KEY (id_avaliacao) REFERENCES avaliacao (id_avaliacao)
);

insert into cliente (nome_cliente, email, endereco, telefone, genero, cpf)
VALUES
('Carlos Pereira', 'carlos.pereira@gmail.com', 'Av. Brasil, 450', '55 11 98234-5678', 'Masculino', '321.654.987-00'),
('Lucas Andrade', 'lucas.andrade@hotmail.com', 'Rua das Flores, 87', '55 21 97654-3321', 'Masculino', '789.123.456-11'),
('Rogério Souza', 'rogerio.souza@yahoo.com', 'Rua XV de Novembro, 300', '55 41 99544-2211', 'Masculino', '654.321.789-22'),
('Douglas Martins', 'douglas.martins@gmail.com', 'Av. Independência, 1220', '55 31 98432-8876', 'Masculino', '987.654.321-33'),
('Nicolas Rocha', 'nicolas.rocha@outlook.com', 'Rua das Acácias, 55', '55 51 98123-4455', 'Masculino', '456.789.123-44');

select * from cliente;

insert into avaliacao (descricao, data_avaliacao) VALUES
('Avaliação física inicial', '2025-01-10 10:00:00'),
('Avaliação de resistência', '2025-02-15 14:30:00'),
('Avaliação de força', '2025-03-20 09:15:00'),
('Reavaliação trimestral', '2025-06-05 11:00:00'),
('Avaliação nutricional', '2025-07-18 16:00:00');

 select * from avaliacao;
 
 insert into agendamento (tipo_aula,data_agendamento,id_cliente, id_avaliacao)values
('Musculação', '2025-01-12 09:00:00', 1, 1),
('Spinning', '2025-02-17 08:00:00', 2, 2),
('Pilates', '2025-03-22 10:00:00', 3, 3),
('Crossfit', '2025-06-07 07:30:00', 4, 4),
('Yoga', '2025-07-20 18:00:00', 5, 5);

select * from agendamento;

insert into produtos (nome_produto, tipo_produto, categoria, preco, quantidade, id_cliente) VALUES
('Whey Protein', 'Suplemento', 'Nutrição', 199.90, 1, 1),
('Camiseta Dry Fit', 'Vestuário', 'Roupas', 79.90, 2, 2),
('Creatina Monohidratada', 'Suplemento', 'Nutrição', 149.90, 1, 3),
('Luvas de Musculação', 'Acessório', 'Equipamentos', 59.90, 1, 4),
('Garrafa Térmica', 'Acessório', 'Utilidades', 39.90, 1, 5);

select * from produtos;

insert into suporte (descricao, categoria_suporte, id_cliente) values
('Dificuldade em acessar o aplicativo', 'Tecnologia', 1),
('Problema na cobrança da mensalidade', 'Financeiro', 2),
('Dúvida sobre treino personalizado', 'Treinamento', 3),
('Solicitação de troca de horário', 'Agendamento', 4),
('Reclamação sobre equipamento', 'Infraestrutura', 5);

select * from produtos;

insert into funcionario(nome_funcionario,cpf,salario,carga_horaria, id_cliente,id_suporte, id_avaliacao) values
('Mariana Oliveira', '123.456.789-10', 3500.00, '2025-01-01 08:00:00', 1, 1, 1),
('João Santos', '987.654.321-00', 4200.00, '2025-01-01 09:00:00', 2, 2, 2),
('Fernanda Costa', '654.987.321-55', 3900.00, '2025-01-01 10:00:00', 3, 3, 3),
('Rafael Lima', '111.222.333-44', 4100.00, '2025-01-01 11:00:00', 4, 4, 4),
('Beatriz Nunes', '555.666.777-88', 3700.00, '2025-01-01 12:00:00', 5, 5, 5);

select * from funcionario;

insert into planos (nome_planos, descricao, valor, id_cliente, id_funcionario) values
('Plano Mensal', 'Acesso livre por 30 dias', 120.00, 1, 1),
('Plano Trimestral', 'Acesso por 3 meses com desconto', 330.00, 2, 2),
('Plano Semestral', 'Acesso por 6 meses e avaliação gratuita', 600.00, 3, 3),
('Plano Anual', 'Acesso ilimitado por 12 meses', 1100.00, 4, 4),
('Plano Premium', 'Acesso VIP + personal trainer', 1500.00, 5, 5);

select * from planos;

INSERT INTO possui (id_cliente, id_avaliacao) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4),
(5, 5);

select * from possui;

show tables;

create table login (
nome_usuario varchar(100) not null,
senha_usuario varchar(200) not null,
tipo_usuario enum('cliente', 'funcionario'),
id_cliente int null,
id_funcionario int null,
foreign key (id_funcionario) references funcionario (id_funcionario),
foreign key (id_cliente) REFERENCES cliente (id_cliente)
);

-- nessa parte do bcd inserimos os valores  para tabela login onde definimos o nome e senha
-- alem disso ultilizamos o sha para criptografia da senha no banco de dados onde caso alguem queira
-- ver a senha do usuario só a vera criptografada e os 256 é em quantos bits ele criptografara no caso
-- o quão extenso vai ficar a criptografia

insert into login (nome_usuario, senha_usuario, tipo_usuario,id_cliente, id_funcionario) values
('carlos_p', sha2('senha123',224), 'cliente', 1, null),
('mariana_ol', sha2('admin123', 224), 'funcionario', null, 1);

select * from login;
-- adiciona os staus de ativo ou inativo nas tabelas cliente, funcionario e planos.
alter table cliente add status enum('ativo', 'inativo') default 'ativo';
alter table funcionario add status enum('ativo', 'inativo') default 'ativo';
alter table planos add status enum('ativo', 'inativo') default 'ativo';

select * from cliente where status = 'ativo';
select * from cliente where status = 'inativo';

-- adiciona a coluna data de cadastro no cliente,define ela como como datetime
-- alem disso faz com que quando o usuario log automaticamente fica registrado o primeiro
-- acesso dele
alter table cliente add data_cadastro datetime default current_timestamp;
alter table cliente add data_atualizacao datetime default current_timestamp on update current_timestamp;

alter table cliente add data_nascimento datetime;
insert into cliente(nome_cliente, email, endereco, telefone, genero, cpf, data_nascimento) values
('João Silva', 'joao@email.com', 'Rua A, 123', '19 99999 9999', 'Masculino', '123.456.789-00', '2000-05-10 00:00:00');

select nome_cliente, data_nascimento from cliente;
DESCRIBE cliente;
UPDATE cliente SET data_nascimento = '1990-05-12' WHERE id_cliente = 1;
UPDATE cliente SET data_nascimento = '1988-07-23' WHERE id_cliente = 2;
UPDATE cliente SET data_nascimento = '1995-11-02' WHERE id_cliente = 3;
UPDATE cliente SET data_nascimento = '1992-03-19' WHERE id_cliente = 4;
UPDATE cliente SET data_nascimento = '1998-09-08' WHERE id_cliente = 5;

select  * from cliente;

CREATE TABLE log_acesso (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    tipo_usuario ENUM('cliente', 'funcionario'),
    data_login DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip_usuario VARCHAR(50)
);

INSERT INTO log_acesso (id_usuario, tipo_usuario, ip_usuario)
VALUES (1, 'cliente', '192.168.0.10');

CREATE TABLE venda (
    id_venda INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT,
    id_produtos INT,
    quantidade INT,
    data_venda DATETIME DEFAULT CURRENT_TIMESTAMP,
    valor_total DECIMAL(10,2),
    FOREIGN KEY (id_cliente) REFERENCES cliente (id_cliente),
    FOREIGN KEY (id_produtos) REFERENCES produtos (id_produtos)
);

select * from venda;

CREATE TABLE mensagem (
    id_mensagem INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    assunto VARCHAR(255) NOT NULL,
    mensagem TEXT NOT NULL,
    data_envio DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pagamento (
    id_pagamento INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT,
    id_planos INT,
    data_pagamento DATETIME DEFAULT CURRENT_TIMESTAMP,
    valor_pago DECIMAL(7,2),
    status_pagamento ENUM('pago', 'pendente') DEFAULT 'pendente',
    FOREIGN KEY (id_cliente) REFERENCES cliente (id_cliente),
    FOREIGN KEY (id_planos) REFERENCES planos (id_planos)
);

INSERT INTO avaliacao (descricao, data_avaliacao) VALUES
('Avaliação pós-treino', '2025-08-22 15:00:00'),
('Teste de flexibilidade', '2025-09-10 09:30:00'),
('Avaliação nutricional de retorno', '2025-10-05 11:45:00');

INSERT INTO agendamento (tipo_aula, data_agendamento, id_cliente, id_avaliacao) VALUES
('Zumba', '2025-08-25 19:00:00', 1, 6),
('Treino Funcional', '2025-09-15 08:30:00', 2, 7),
('Alongamento', '2025-10-07 17:15:00', 3, 8);

INSERT INTO produtos (nome_produto, tipo_produto, categoria, preco, quantidade, id_cliente) VALUES
('Corda de Pular', 'Acessório', 'Equipamentos', 29.90, 1, 1),
('Toalha Fitness', 'Acessório', 'Utilidades', 25.00, 2, 2),
('Pré-treino Explosive', 'Suplemento', 'Nutrição', 159.90, 1, 3);

INSERT INTO suporte (descricao, categoria_suporte, id_cliente) VALUES
('Erro ao visualizar histórico de treinos', 'Tecnologia', 1),
('Problema com o débito automático', 'Financeiro', 2),
('Dúvida sobre progressão de carga', 'Treinamento', 3);

INSERT INTO funcionario (nome_funcionario, cpf, salario, carga_horaria, id_cliente, id_suporte, id_avaliacao) VALUES
('Camila Torres', '222.333.444-55', 4000.00, '2025-02-01 08:00:00', 1, 1, 6),
('Paulo Henrique', '333.444.555-66', 3800.00, '2025-02-01 09:00:00', 2, 2, 7),
('Larissa Mendes', '444.555.666-77', 4200.00, '2025-02-01 10:00:00', 3, 3, 8);

INSERT INTO planos (nome_planos, descricao, valor, id_cliente, id_funcionario) VALUES
('Plano Família', 'Permite 2 usuários adicionais', 1600.00, 1, 6),
('Plano Corporativo', 'Acesso para empresas e grupos', 2800.00, 2, 7),
('Plano Intermediário', 'Acesso livre + acompanhamento mensal', 850.00, 3, 8);

INSERT INTO venda (id_cliente, id_produtos, quantidade, valor_total) VALUES
(1, 6, 1, 29.90),
(2, 7, 2, 50.00),
(3, 8, 1, 159.90);

INSERT INTO pagamento (id_cliente, id_planos, valor_pago, status_pagamento) VALUES
(1, 6, 1600.00, 'pago'),
(2, 7, 2800.00, 'pendente'),
(3, 8, 850.00, 'pago');

INSERT INTO mensagem (nome, email, assunto, mensagem) VALUES
('Carlos Pereira', 'carlos.pereira@gmail.com', 'Treino personalizado', 'Gostaria de solicitar um novo plano de treino.'),
('Lucas Andrade', 'lucas.andrade@hotmail.com', 'Problema no app', 'O aplicativo está travando ao tentar abrir o histórico de treinos.'),
('Douglas Martins', 'douglas.martins@gmail.com', 'Cancelamento de plano', 'Quero cancelar o plano atual e trocar para o semestral.');


-- 📦 FORNECEDORES
CREATE TABLE fornecedores (
    id_fornecedor INT AUTO_INCREMENT PRIMARY KEY,
    nome_fornecedor VARCHAR(200) NOT NULL,
    cnpj_fornecedor VARCHAR(14) NOT NULL,
    telefone_fornecedor VARCHAR(16) NOT NULL,
    email VARCHAR(100) NOT NULL,
    endereco VARCHAR(200) NOT NULL
);

-- 🏋️‍♂️ TREINOS
CREATE TABLE treinos (
    id_treino INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT,
    id_funcionario INT,
    nome_treino VARCHAR(200) NOT NULL,
    objetivo VARCHAR(100),
    duracao INT,
    data_inicio DATE,
    data_fim DATE,
    FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente),
    FOREIGN KEY (id_funcionario) REFERENCES funcionario(id_funcionario)
);

-- 💪 EXERCICIOS
CREATE TABLE exercicio (
    id_exercicio INT AUTO_INCREMENT PRIMARY KEY,
    id_treino INT,
    nome_exercicio VARCHAR(200) NOT NULL,
    serie INT,
    repeticoes INT,
    carga DECIMAL(5,2),
    FOREIGN KEY (id_treino) REFERENCES treinos(id_treino)
);

-- 💰 FATURA
CREATE TABLE fatura (
    id_fatura INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT,
    id_pagamento INT,
    valor_total DECIMAL(10,2),
    vencimento DATE,
    status ENUM('aberta','paga','vencida') DEFAULT 'aberta',
    FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente),
    FOREIGN KEY (id_pagamento) REFERENCES pagamento(id_pagamento)
);

-- 💳 FORMA DE PAGAMENTO
CREATE TABLE forma_pagamento (
    id_forma_pagamento INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('cartao', 'pix', 'boleto', 'dinheiro'),
    descricao VARCHAR(255)
);

-- 🔗 ADICIONA A CHAVE ESTRANGEIRA NA TABELA PAGAMENTO
ALTER TABLE pagamento
ADD id_forma_pagamento INT,
ADD FOREIGN KEY (id_forma_pagamento) REFERENCES forma_pagamento(id_forma_pagamento);

CREATE TABLE notificacao (
    id_notificacao INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT,
    titulo VARCHAR(255),
    mensagem TEXT,
    data_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('lida', 'não lida') DEFAULT 'não lida',
    FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente)
);

CREATE TABLE presenca (
    id_presenca INT AUTO_INCREMENT PRIMARY KEY,
    id_agendamento INT,
    id_cliente INT,
    data_presenca DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('presente', 'faltou') DEFAULT 'presente',
    FOREIGN KEY (id_agendamento) REFERENCES agendamento(id_agendamento),
    FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente));
   

INSERT INTO fornecedores (nome_fornecedor, cnpj_fornecedor, telefone_fornecedor, email, endereco) VALUES
('Max Suplementos', '12345678000199', '11 99876-1122', 'contato@maxsuplementos.com', 'Av. Paulista, 1000 - São Paulo/SP'),
('FitWear Roupas Esportivas', '98765432000177', '21 98888-5566', 'vendas@fitwear.com', 'Rua das Laranjeiras, 500 - Rio de Janeiro/RJ'),
('NutriPower', '45678912000133', '31 97777-3344', 'suporte@nutripower.com', 'Av. Afonso Pena, 800 - Belo Horizonte/MG'),
('H2O Purificadores', '32165498000155', '19 98555-6677', 'contato@h2opurificadores.com', 'Rua Carlos Gomes, 250 - Campinas/SP'),
('SportEquip', '74185296000122', '51 98444-2211', 'vendas@sportequip.com', 'Av. Ipiranga, 700 - Porto Alegre/RS');

INSERT INTO treinos (id_cliente, id_funcionario, nome_treino, objetivo, duracao, data_inicio, data_fim) VALUES
(1, 1, 'Treino Hipertrofia', 'Ganho de massa muscular', 60, '2025-01-15', '2025-03-15'),
(2, 2, 'Treino Emagrecimento', 'Perda de gordura corporal', 45, '2025-02-01', '2025-04-01'),
(3, 3, 'Treino Funcional', 'Melhorar mobilidade e resistência', 50, '2025-03-10', '2025-05-10'),
(4, 4, 'Treino Força', 'Aumentar força e resistência', 55, '2025-04-05', '2025-06-05'),
(5, 5, 'Treino Resistência', 'Melhorar desempenho cardiovascular', 40, '2025-05-20', '2025-07-20');

INSERT INTO exercicio (id_treino, nome_exercicio, serie, repeticoes, carga) VALUES
(1, 'Supino Reto', 4, 12, 50.0),
(1, 'Agachamento Livre', 4, 10, 80.0),
(2, 'Corda Naval', 3, 15, 0.0),
(3, 'Burpee', 4, 12, 0.0),
(4, 'Levantamento Terra', 5, 8, 100.0),
(5, 'Corrida Esteira', 1, 20, 0.0);

INSERT INTO fatura (id_cliente, id_pagamento, valor_total, vencimento, status) VALUES
(1, 1, 1600.00, '2025-08-30', 'paga'),
(2, 2, 2800.00, '2025-09-30', 'vencida'),
(3, 3, 850.00, '2025-10-30', 'aberta');

INSERT INTO forma_pagamento (tipo, descricao) VALUES
('cartao', 'Cartão de crédito Visa ou Mastercard'),
('pix', 'Transferência via PIX'),
('boleto', 'Pagamento por boleto bancário'),
('dinheiro', 'Pagamento em espécie na recepção');

INSERT INTO notificacao (id_cliente, titulo, mensagem, status) VALUES
(1, 'Treino disponível', 'Seu novo treino de hipertrofia foi liberado!', 'não lida'),
(2, 'Pagamento pendente', 'Seu plano está com fatura pendente.', 'lida'),
(3, 'Agendamento confirmado', 'Sua aula de funcional foi confirmada.', 'não lida'),
(4, 'Atualização de avaliação', 'Sua avaliação física foi atualizada.', 'lida'),
(5, 'Mensagem da academia', 'Participe da semana de treinos intensivos!', 'não lida');

INSERT INTO presenca (id_agendamento, id_cliente, status) VALUES
(1, 1, 'presente'),
(2, 2, 'presente'),
(3, 3, 'faltou'),
(4, 4, 'presente'),
(5, 5, 'faltou');

create table carrinho(
id_carrinho int auto_increment primary key,
id_produtos int,
quantidade_carrinho int,
valor_unitario decimal(7,2),
FOREIGN KEY (id_produtos) REFERENCES produtos(id_produtos)
);

INSERT INTO carrinho (id_produtos, quantidade_carrinho, valor_unitario) VALUES
(1, 10, 12.50),
(2, 5, 22.90),
(3, 3, 9.90),
(4, 7, 15.00),
(5, 2, 30.00);

CREATE TABLE log_atividade (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    tipo_usuario ENUM('cliente', 'funcionario') NOT NULL,
    acao VARCHAR(255) NOT NULL,
    data_acao DATETIME DEFAULT CURRENT_TIMESTAMP,
    descricao TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO log_atividade (id_usuario, tipo_usuario, acao, descricao) VALUES
(1, 'cliente', 'Login', 'Cliente acessou o sistema'),
(2, 'cliente', 'Agendamento', 'Cliente agendou aula de spinning'),
(3, 'funcionario', 'Cadastro', 'Funcionário cadastrou novo treino'),
(4, 'cliente', 'Pagamento', 'Cliente realizou pagamento via PIX'),
(5, 'funcionario', 'Alteração', 'Funcionário atualizou dados de treino');

select  * from login;
use academia;