CREATE DATABASE felixbus_db;
USE felixbus_db;

CREATE TABLE utilizador (
	id_utilizador INT AUTO_INCREMENT PRIMARY KEY,
	nome VARCHAR(100) NOT NULL,
	data_nascimento DATE,
	email VARCHAR(100) UNIQUE NOT NULL,
	password VARCHAR(255) NOT NULL,
	telefone VARCHAR(20),
	tipo_utilizador ENUM('cliente', 'funcionario', 'administrador') NOT NULL,
	estado ENUM ('ativo', 'inativo', 'pendente') DEFAULT 'pendente' NOT NULL,
	data_registo DATETIME DEFAULT CURRENT_TIMESTAMP,
	ultima_atualiazacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE carteira (
	id_utilizador INT PRIMARY KEY,
	saldo DECIMAL(10,2) DEFAULT 0 NOT NULL,
	FOREIGN KEY (id_utilizador) REFERENCES utilizador(id_utilizador) ON DELETE CASCADE
);

CREATE TABLE rota (
	id_rota INT AUTO_INCREMENT PRIMARY KEY,
	origem VARCHAR(100) NOT NULL,
	destino VARCHAR(100) NOT NULL,
	distancia INT,
	duracao_estimada TIME,
	preco_base DECIMAL(10,2)
);

CREATE TABLE paragem (
	id_paragem INT AUTO_INCREMENT PRIMARY KEY,
	id_rota INT NOT NULL,
	nome_local VARCHAR(100) NOT NULL,
	ordem INT NOT NULL,
	FOREIGN KEY (id_rota) REFERENCES rota(id_rota) ON DELETE CASCADE
);

CREATE TABLE viagem(
	id_viagem INT AUTO_INCREMENT PRIMARY KEY,
	id_rota INT NOT NULL,
	dia_semana ENUM('Domingo', 'Segunda', 'Terca', 'Quarta', 'Quinta', 'Sexta', 'Sabado') NOT NULL,
	hora TIME NOT NULL,
	preco DECIMAL(10,2) NOT NULL,
	capacidade_max INT,
	estado ENUM('ativa','cancelada') DEFAULT 'ativa',
	FOREIGN KEY (id_rota) REFERENCES rota(id_rota) ON DELETE CASCADE
);

CREATE TABLE bilhete (
	id_bilhete INT AUTO_INCREMENT PRIMARY KEY,
	id_utilizador INT NOT NULL,
	id_viagem INT NOT NULL,
	codigo_validacao VARCHAR(50) UNIQUE NOT NULL,
	preco DECIMAL(10,2),
	data_compra DATETIME DEFAULT CURRENT_TIMESTAMP,
	estado ENUM('valido', 'usado', 'cancelado') DEFAULT 'valido',
	FOREIGN KEY (id_utilizador) REFERENCES utilizador(id_utilizador) ON DELETE CASCADE,
	FOREIGN KEY (id_viagem) REFERENCES viagem(id_viagem) ON DELETE CASCADE
);

CREATE TABLE promocao (
	id_promocao INT AUTO_INCREMENT PRIMARY KEY,
	descricao TEXT,
	valor DECIMAL(10,2),
	data_inicio DATE,
	data_fim DATE
);

CREATE TABLE bilhete_promocao (
	id_bilhete INT,
	id_promocao INT,
	PRIMARY KEY (id_bilhete, id_promocao),
	FOREIGN KEY (id_bilhete) REFERENCES bilhete(id_bilhete) ON DELETE CASCADE,
	FOREIGN KEY (id_promocao) REFERENCES promocao(id_promocao) ON DELETE CASCADE
);

CREATE TABLE transacao (
	id_transacao INT AUTO_INCREMENT PRIMARY KEY,
	id_origem INT NOT NULL,
	id_destino INT NOT NULL,
	valor DECIMAL(10,2) NOT NULL,
	dataOperacao DATETIME DEFAULT CURRENT_TIMESTAMP,
	descricao TEXT,
	FOREIGN KEY (id_origem) REFERENCES carteira(id_utilizador) ON DELETE CASCADE,
	FOREIGN KEY (id_destino) REFERENCES carteira(id_utilizador) ON DELETE CASCADE
);

CREATE TABLE auditoria (
	id_auditoria INT AUTO_INCREMENT PRIMARY KEY,
	id_utilizador INT NOT NULL,
	data_registo DATETIME DEFAULT CURRENT_TIMESTAMP,
	tipo_operacao VARCHAR(50),
	valor DECIMAL(10,2),
	FOREIGN KEY (id_utilizador) REFERENCES utilizador(id_utilizador) ON DELETE CASCADE
);

/*
id_utilizador, nome, data_nascimento , email ,password ,telefone,
tipo_utilizador ENUM('cliente', 'funcionario', 'administrador') ,
estado ENUM ('ativo', 'inativo', 'pendente') DEFAULT 'pendente' ,
data_registo ,
ultima_atualiazacao
*/
INSERT INTO utilizador (nome, data_nascimento, email, password, telefone, tipo_utilizador, estado)
VALUES
('FelixBus', NULL, 'empresa@felixbus.com', 'felix', NULL, 'administrador', 'ativo'),
('Cliente','1990-01-01', 'cliente@exemplo.com','cliente', '912345678','cliente', 'ativo'),
('Funcionario','1985-05-10', 'funcionario@felixbus.com', 'cliente', '913456789', 'funcionario', 'ativo'),
('Administrador', '1980-03-20', 'admin@felixbus.com','admin', '914567890', 'administrador', 'ativo');

INSERT INTO carteira (id_utilizador, saldo)
VALUES
(1, 0.00),
(2, 0.00),
(3, 0.00),
(4, 0.00);
