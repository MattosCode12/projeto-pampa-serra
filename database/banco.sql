CREATE DATABASE pampa_serra;

USE pampa-serra;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL
);

INSERT INTO usuarios (nome, email, senha)
VALUES (
    'admin',
    'admin@pampaserra.com',
    '123456'
);