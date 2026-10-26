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
    CONSTRAINT chk_tipo_usuario CHECK (tipo_usuario IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS sensores (
    id_sensor INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    localizacao VARCHAR(100) NOT NULL,
    tipo_dado VARCHAR(150) NOT NULL,
    descricao TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS rota (
    id_rota INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(100) NOT NULL UNIQUE,
    origem VARCHAR(150) NOT NULL,
    destino VARCHAR(150) NOT NULL,
    parada VARCHAR(150),
    velocidade_min DECIMAL(10,2) NOT NULL,
    velocidade_max DECIMAL(10,2) NOT NULL,
    consumo_min DECIMAL(10,2) NOT NULL,
    consumo_max DECIMAL(10,2) NOT NULL,
    ativa TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS trens (
    id_trem INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    modelo VARCHAR(100) NOT NULL,
    status VARCHAR(10) NOT NULL DEFAULT 'Normal',
    id_usuario INT NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_status_trem CHECK (status IN ('Normal', 'Alerta', 'Falha')),
    CONSTRAINT fk_trens_usuario FOREIGN KEY (id_usuario)
        REFERENCES usuarios (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tentativas_login (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    ip VARCHAR(45) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tentativas_email (email, criado_em),
    INDEX idx_tentativas_ip (ip, criado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO sensores
(nome, localizacao, tipo_dado, descricao)
SELECT
    dados.nome,
    dados.localizacao,
    dados.tipo_dado,
    dados.descricao
FROM (
    SELECT
        'Sensor 1.21' AS nome,
        'KM 0 - Porto Alegre' AS localizacao,
        'GPS + Sensor de partida' AS tipo_dado,
        'Início da viagem e acompanhamento da rota' AS descricao
    UNION ALL SELECT
        'Sensor 1.47',
        'KM 22 - Aeroporto',
        'Sensor de aproximação',
        'Controle do trem em área urbana'
    UNION ALL SELECT
        'Sensor 1.82',
        'KM 35 - São Leopoldo',
        'Sensor de obstáculos',
        'Aumentar a segurança nos trilhos'
    UNION ALL SELECT
        'Sensor 1.93',
        'KM 52 - Novo Hamburgo',
        'Sensor de passagem e peso dos vagões',
        'Parada intermediária da linha'
    UNION ALL SELECT
        'Sensor 2.03',
        'KM 62 - Sapiranga',
        'Sensor de temperatura',
        'Monitoramento dos freios e motor'
    UNION ALL SELECT
        'Sensor 2.19',
        'KM 74 - Parobé',
        'Sensor de energia',
        'Controle do consumo do trem'
    UNION ALL SELECT
        'Sensor 2.34',
        'KM 84 - Taquara',
        'Sensor de presença',
        'Detectar a passagem do trem'
    UNION ALL SELECT
        'Sensor 2.51',
        'KM 96 - Igrejinha',
        'Sensor climático',
        'Detectar chuva e neblina'
    UNION ALL SELECT
        'Sensor 2.79',
        'KM 106 - Três Coroas',
        'Sensor de vibração',
        'Monitorar desgaste dos trilhos'
    UNION ALL SELECT
        'Sensor 3.12',
        'KM 114 - Canela',
        'Sensor de velocidade',
        'Controle em curvas'
    UNION ALL SELECT
        'Sensor 3.36',
        'KM 120 - Gramado',
        'Sensor final e GPS',
        'Encerramento da rota e registro da chegada'
) AS dados
WHERE NOT EXISTS (
    SELECT 1
    FROM sensores s
    WHERE s.nome = dados.nome
);