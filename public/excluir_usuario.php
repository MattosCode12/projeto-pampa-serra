<?php
session_start();
require_once "../conexao.php";

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: listar_cadastro.php");
    exit;
}

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if (!$id) {
    header("Location: listar_cadastro.php?erro=ID inválido");
    exit;
}

if ($id == $_SESSION["id_usuario"]) {
    header("Location: listar_cadastro.php?erro=Você não pode excluir seu próprio usuário");
    exit;
}

$sql = "SELECT id, tipo_usuario FROM usuarios WHERE id = ?";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    header("Location: listar_cadastro.php?erro=Usuário não encontrado");
    exit;
}

$usuario = $resultado->fetch_assoc();

if ($usuario["tipo_usuario"] === "admin") {
    header("Location: listar_cadastro.php?erro=Administradores não podem ser excluídos");
    exit;
}

$sql = "DELETE FROM usuarios WHERE id = ?";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: listar_cadastro.php?sucesso=1");
    exit;
}

header("Location: listar_cadastro.php?erro=Erro ao excluir usuário");
exit;
?>