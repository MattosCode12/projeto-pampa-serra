<?php
$servidor = "localhost";
$usuario = "root";
$senha = "";
$banco = "pampa_serra";
try {
    $conexao = new mysqli(
        $servidor,
        $usuario,
        $senha,
        $banco,
        3306
    );
    $conexao->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit("Erro ao conectar com o banco de dados.");
}

....
.........