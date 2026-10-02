<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();

}

function protegerPagina($nivel_acesso){

if (!isset($_SESSION['usuario_id'])) {
header("location: index.php?erro=nao_logado");
exit;
}

}
if ($nivel_acesso === '1' && $_SESSION['tipo_usuario'] !== '1'){
header("location:cadastro_usuarios.php?erro=sem_permissao");

}





?>