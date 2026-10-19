<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function exigir_login() {
    if (!isset($_SESSION["usuario_id"])) {
        header("Location: ../index.php");
        exit;
    }
}

function eh_admin() {
    return isset($_SESSION["usuario_id"])
        && (int)($_SESSION["tipo_usuario"] ?? 0) === 1;
}

function exigir_admin() {
    exigir_login();

    if (!eh_admin()) {
        header("Location: tela_bloqueio_usuarios.php");
        exit;
    }
}

function h($valor) {
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES | ENT_SUBSTITUTE,
        "UTF-8"
    );
}

function token_csrf() {
    if (empty($_SESSION["csrf"])) {
        $_SESSION["csrf"] = bin2hex(random_bytes(32));
    }

    return $_SESSION["csrf"];
}

function campo_csrf() {
    return '<input type="hidden" name="csrf" value="' .
        h(token_csrf()) . '">';
}

function validar_csrf() {
    $token = $_POST["csrf"] ?? "";

    if (
        !is_string($token) ||
        !hash_equals(token_csrf(), $token)
    ) {
        http_response_code(403);
        exit("Solicitação inválida.");
    }
}