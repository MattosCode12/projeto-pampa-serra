CREATE TABLE IF NOT EXISTS usuario_enderecos (
    usuario_id INT NOT NULL PRIMARY KEY,
    cep CHAR(8) NOT NULL,
    logradouro VARCHAR(150) NOT NULL DEFAULT '',
    bairro VARCHAR(100) NOT NULL DEFAULT '',
    cidade VARCHAR(100) NOT NULL,
    uf CHAR(2) NOT NULL,
    numero VARCHAR(20) NOT NULL DEFAULT '',
    complemento VARCHAR(100) NOT NULL DEFAULT '',
    CONSTRAINT fk_endereco_usuario FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
