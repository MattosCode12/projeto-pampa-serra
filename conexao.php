<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$config = [
    "db_servidor" => "127.0.0.1",
    "db_usuario" => "root",
    "db_senha" => "",
    "db_nome" => "pampa_serra",
    "db_porta" => 3306
];

try {
    $conexao = new mysqli(
        $config["db_servidor"],
        $config["db_usuario"],
        $config["db_senha"],
        $config["db_nome"],
        (int)$config["db_porta"]
    );

    $conexao->set_charset("utf8mb4");

} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit("Erro ao conectar com o banco de dados.");
}