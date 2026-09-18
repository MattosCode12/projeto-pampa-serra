<?php

$servidor = "localhost";
$usuario = "root";
$senha = "";
$banco = "pampa-serra";
$porta = 8080;

$conexao = new mysqli(
    $servidor,
    $usuario,
    $senha,
    $banco
);

if ($conexao->connect_error) {
    die("Erro na conexão com o banco: " . $conexao->connect_error);
}

$conexao->set_charset("utf8");

?>