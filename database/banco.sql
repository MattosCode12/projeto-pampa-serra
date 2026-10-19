CREATE DATABASE IF NOT EXISTS pampa_serra
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE pampa_serra;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    tipo_usuario TINYINT UNSIGNED NOT NULL DEFAULT 0,
    CONSTRAINT chk_tipo_usuario CHECK (tipo_usuario IN (0,1))
);

CREATE TABLE IF NOT EXISTS rota (
    id_rota INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(30) NOT NULL UNIQUE,
    origem VARCHAR(100) NOT NULL,
    destino VARCHAR(100) NOT NULL,
    parada VARCHAR(100),
    velocidade_min DECIMAL(8,2) NOT NULL DEFAULT 0,
    velocidade_max DECIMAL(8,2) NOT NULL DEFAULT 0,
    consumo_min DECIMAL(8,2) NOT NULL DEFAULT 0,
    consumo_max DECIMAL(8,2) NOT NULL DEFAULT 0,
    ativa TINYINT(1) NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS sensores (
    id_sensor INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    localizacao VARCHAR(100) NOT NULL,
    tipo_dado VARCHAR(150) NOT NULL,
    descricao TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO rota
(codigo, origem, destino, parada,
 velocidade_min, velocidade_max,
 consumo_min, consumo_max, ativa)
VALUES
('3207','Porto Alegre','Gramado','Novo Hamburgo',
 30,40,3,5,1),
('4180','Gramado','Porto Alegre','Novo Hamburgo',
 30,40,3,5,1)
ON DUPLICATE KEY UPDATE codigo = VALUES(codigo);

INSERT INTO sensores
(nome, localizacao, tipo_dado, descricao)
SELECT d.nome, d.localizacao, d.tipo_dado, d.descricao
FROM (
    SELECT 'Sensor 1.21' nome, 'KM 0 - Porto Alegre' localizacao,
    'GPS + Sensor de partida' tipo_dado,
    'Início da viagem e acompanhamento da rota' descricao

    UNION ALL SELECT 'Sensor 1.47','KM 22 - Aeroporto',
    'Sensor de aproximação','Controle do trem em área urbana'

    UNION ALL SELECT 'Sensor 1.82','KM 35 - São Leopoldo',
    'Sensor de obstáculos','Aumentar a segurança nos trilhos'

    UNION ALL SELECT 'Sensor 1.93','KM 52 - Novo Hamburgo',
    'Passagem e peso dos vagões','Parada intermediária da linha'

    UNION ALL SELECT 'Sensor 2.03','KM 62 - Sapiranga',
    'Sensor de temperatura','Monitoramento dos freios e motor'

    UNION ALL SELECT 'Sensor 2.19','KM 74 - Parobé',
    'Sensor de energia','Controle do consumo do trem'

    UNION ALL SELECT 'Sensor 2.34','KM 84 - Taquara',
    'Sensor de presença','Detectar a passagem do trem'

    UNION ALL SELECT 'Sensor 2.51','KM 96 - Igrejinha',
    'Sensor climático','Detectar chuva e neblina'

    UNION ALL SELECT 'Sensor 2.79','KM 106 - Três Coroas',
    'Sensor de vibração','Monitorar desgaste dos trilhos'

    UNION ALL SELECT 'Sensor 3.12','KM 114 - Canela',
    'Sensor de velocidade','Controle em curvas'

    UNION ALL SELECT 'Sensor 3.36','KM 120 - Gramado',
    'Sensor final e GPS','Encerramento e registro da chegada'
) AS d
WHERE NOT EXISTS (
    SELECT 1 FROM sensores s WHERE s.nome = d.nome
);