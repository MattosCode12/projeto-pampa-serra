<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


function exigir_login() {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: ../index.php'); // sua tela de login
        exit;
    }
}


function exigir_admin() {
    exigir_login
    ();
    if ($_SESSION['tipo_usuario'] != 1) {
        header('Location: home.php');
        exit;
    }
}
?>