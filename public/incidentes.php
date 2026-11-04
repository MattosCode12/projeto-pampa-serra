<?php
const INCIDENTE_TIPOS = [
    "Pane elétrica",
    "Pane no motor",
    "Superaquecimento dos freios",
    "Falha de comunicação",
    "Falha no GPS",
    "Obstáculo na via",
    "Descarrilamento",
    "Colisão / Acidente",
    "Excesso de velocidade",
    "Sobrecarga de peso",
    "Queda de energia",
    "Incêndio"
];

const INCIDENTE_GRAVIDADES = ["Baixa", "Média", "Alta", "Crítica"];

function garantir_tabela_incidentes(mysqli $conexao): void {
    $conexao->query(
        "CREATE TABLE IF NOT EXISTS incidentes (
            id_incidente INT AUTO_INCREMENT PRIMARY KEY,
            id_sensor INT NOT NULL,
            tipo VARCHAR(60) NOT NULL,
            gravidade VARCHAR(10) NOT NULL,
            descricao VARCHAR(255) NULL,
            resolvido TINYINT(1) NOT NULL DEFAULT 0,
            criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            resolvido_em DATETIME NULL,
            INDEX idx_incidentes_sensor (id_sensor),
            INDEX idx_incidentes_resolvido (resolvido)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

function incidente_classe(string $gravidade): string {
    $mapa = [
        "Baixa" => "text-bg-secondary",
        "Média" => "text-bg-warning",
        "Alta" => "text-bg-danger",
        "Crítica" => "text-bg-dark"
    ];

    return $mapa[$gravidade] ?? "text-bg-secondary";
}

function incidente_data($valor): string {
    if (!$valor) {
        return "-";
    }

    $tempo = strtotime((string)$valor);

    return $tempo ? date("d/m/Y H:i", $tempo) : (string)$valor;
}