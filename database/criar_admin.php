<?php


if (PHP_SAPI !== "cli") {
    http_response_code(404);
    exit;
}

require __DIR__ . "/../includes/config.php";

[, $nome, $email] = $argv + [null, "", ""];
$email = mb_strtolower(trim($email), "UTF-8");

if (trim($nome) === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Uso: php database/criar_admin.php \"Nome\" email@exemplo.com\n");
    exit(1);
}

echo "Senha (mín. 8 caracteres, letras e números): ";
$senha = rtrim((string)fgets(STDIN), "\r\n");

if (strlen($senha) < 8 || strlen($senha) > 72 || !preg_match('/[A-Za-z]/', $senha) || !preg_match('/\d/', $senha)) {
    fwrite(STDERR, "Senha fraca: use de 8 a 72 caracteres, com letras e números.\n");
    exit(1);
}

require __DIR__ . "/../includes/conexao.php";

$hash = password_hash($senha, PASSWORD_DEFAULT);
$stmt = $conexao->prepare(
    "INSERT INTO usuarios (nome, email, senha, tipo_usuario)
     VALUES (?, ?, ?, 1)
     ON DUPLICATE KEY UPDATE nome = VALUES(nome), senha = VALUES(senha), tipo_usuario = 1"
);
$stmt->bind_param("sss", $nome, $email, $hash);
$stmt->execute();

echo "Administrador {$email} pronto para uso.\n";
