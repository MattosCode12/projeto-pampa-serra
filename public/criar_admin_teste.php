<?php
require_once __DIR__ . "/conexao.php";

$nome = "Admin Teste";
$email = "teste@gmail.com";
$senha = password_hash("12345678", PASSWORD_DEFAULT);
$tipo = 1;

$stmt = $conexao->prepare(
    "INSERT INTO usuarios (nome, email, senha, tipo_usuario)
     VALUES (?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE
        nome = VALUES(nome),
        senha = VALUES(senha),
        tipo_usuario = VALUES(tipo_usuario)"
);

$stmt->bind_param("sssi", $nome, $email, $senha, $tipo);
$stmt->execute();
$stmt->close();

echo "Administrador cadastrado com sucesso!";