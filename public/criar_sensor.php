<?php
require_once "verificar_acesso.php";
exigir_admin();
require_once "../conexao.php";
$erro = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    validar_csrf();
    $nome = trim($_POST["nome"] ?? "");
    $localizacao = trim($_POST["localizacao"] ?? "");
    $tipo = trim($_POST["tipo_dado"] ?? "");
    $descricao = trim($_POST["descricao"] ?? "");
    if ($nome === "" || $localizacao === "" || $tipo === "") {
        $erro = "Preencha os campos obrigatórios.";
    } elseif (
        strlen($nome) > 100 ||
        strlen($localizacao) > 100 ||
        strlen($tipo) > 150
    ) {
        $erro = "Um campo ultrapassou o tamanho permitido.";
    } else {
        $stmt = $conexao->prepare(
            "INSERT INTO sensores
             (nome, localizacao, tipo_dado, descricao)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "ssss",
            $nome,
            $localizacao,
            $tipo,
            $descricao
        );
        $stmt->execute();
        header("Location: sensores.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Criar Sensor - Pampa Serra</title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../style/style.css">
</head>
<body>
<div class="tela-criar-sensor">
    <div class="topo-criar">
        <div class="buscar-criar">
            <input
                type="text"
                placeholder="Cadastrar sensor"
                disabled>
        </div>
        <a
            href="sensores.php"
            class="btn-voltar-criar text-decoration-none">
            Voltar
        </a>
    </div>
    <div class="card-criar-sensor">
        <img
            src="../assets/img/Logo.png"
            class="logo-criar-sensor"
            alt="Logo Pampa Serra">
        <?php if ($erro): ?>
            <div class="alert alert-danger">
                <?= h($erro) ?>
            </div>
        <?php endif; ?>
        <form method="POST">
            <?= campo_csrf() ?>
            <label for="sensor">Sensor:</label>
            <input
                type="text"
                id="sensor"
                name="nome"
                maxlength="100"
                class="form-control input-criar-sensor"
                required>
            <label for="localizacao">Localização:</label>
            <input
                type="text"
                id="localizacao"
                name="localizacao"
                maxlength="100"
                class="form-control input-criar-sensor"
                required>
            <label for="tipo">Tipo de dado:</label>
            <input
                type="text"
                id="tipo"
                name="tipo_dado"
                maxlength="150"
                class="form-control input-criar-sensor"
                required>
            <label for="descricao">Descrição:</label>
            <textarea
                id="descricao"
                name="descricao"
                class="form-control input-criar-sensor"
                style="height:75px"></textarea>
            <button type="submit" id="btn-criar-sensor">
                Criar
            </button>
        </form>
    </div>
</div>
<script src="../script/script.js"></script>
</body>
</html>