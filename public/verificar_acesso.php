<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists("h")) {
    function h($valor) {
        return htmlspecialchars(
            (string)$valor,
            ENT_QUOTES,
            "UTF-8"
        );
    }
}

if (!function_exists("campo_csrf")) {
    function campo_csrf() {
        if (empty($_SESSION["csrf"])) {
            $_SESSION["csrf"] = bin2hex(random_bytes(32));
        }

        return '<input type="hidden" name="csrf" value="' .
            h($_SESSION["csrf"]) . '">';
    }
}

if (!function_exists("validar_csrf")) {
    function validar_csrf() {
        $token = $_POST["csrf"] ?? "";

        if (
            !is_string($token) ||
            empty($_SESSION["csrf"]) ||
            !hash_equals($_SESSION["csrf"], $token)
        ) {
            http_response_code(403);
            exit("Requisição inválida. Atualize a página.");
        }
    }
}

function exigir_login() {
    if (!isset($_SESSION["usuario_id"])) {
        header("Location: ../index.php");
        exit;
    }
}

function exigir_admin() {
    exigir_login();

    if (
        !isset($_SESSION["tipo_usuario"]) ||
        (int)$_SESSION["tipo_usuario"] !== 1
    ) {
        http_response_code(403);
        exit("Acesso negado. Somente administradores.");
    }
}