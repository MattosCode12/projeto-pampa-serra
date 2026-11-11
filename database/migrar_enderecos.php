<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require __DIR__ . '/../conexao.php';
$conexao->query(file_get_contents(__DIR__ . '/endereco_usuarios.sql'));
echo "Estrutura de endereços pronta.\n";
