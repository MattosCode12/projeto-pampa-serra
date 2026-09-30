CREATE DATABASE pampa_serra;

USE pampa_serra;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    -- tipo_usuario ENUM('admin', 'usuario') NOT NULL DEFAULT 'usuario',
    tipo_usuario TINYINT UNSIGNED NOT NULL CHECK (tipo_usuario IN (0, 1))
);

INSERT INTO usuarios (nome, email, senha, telefone, tipo_usuario)
VALUES (
    'admin',
    'admin@pampaserra.com',
    '123456',
    NULL,
    1
);