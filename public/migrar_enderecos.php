<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require __DIR__ . '/../conexao.php';
$schema = file_get_contents(__DIR__ . '/../database/banco.sql');
if ($schema === false || !preg_match('/^CREATE TABLE IF NOT EXISTS usuario_enderecos \([\s\S]*?;/m', $schema, $matches)) {
    fwrite(STDERR, "Definição de usuario_enderecos não encontrada em database/banco.sql.\n");
    exit(1);
}
$conexao->query($matches[0]);
echo "Estrutura de endereços pronta.\n";
