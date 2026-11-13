USE pampa_serra;

CREATE TABLE IF NOT EXISTS trem_rota (
    id_trem INT NOT NULL PRIMARY KEY,
    id_rota INT NOT NULL,
    CONSTRAINT fk_trem_rota_trem FOREIGN KEY (id_trem) REFERENCES trens (id_trem),
    CONSTRAINT fk_trem_rota_rota FOREIGN KEY (id_rota) REFERENCES rota (id_rota)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
