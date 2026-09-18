<?php

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $sensor = trim($_POST["sensor"] ?? "");
    $localizacao = trim($_POST["localizacao"] ?? "");
    $tipo = trim($_POST["tipo"] ?? "");

    if (
        empty($sensor) ||
        empty($localizacao) ||
        empty($tipo)
    ) {

        $mensagem = "Preencha todos os campos.";

    } else {

        $mensagem = "Sensor preenchido com sucesso.";

    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Criar Sensor - Pampa Serra</title>

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
    >

    <link
        rel="stylesheet"
        href="../style/style.css"
    >

</head>

<body>

    <div class="tela-criar-sensor">

        <div class="topo-criar">

            <div class="buscar-criar">

                <input
                    type="text"
                    placeholder="Buscar"
                >

                <button type="button">

                    <i class="bi bi-search"></i>

                </button>

            </div>

            <button
                class="btn-voltar-criar"
                onclick="window.location.href='sensores.php'"
            >
                Voltar
            </button>

        </div>

        <div class="card-criar-sensor">

            <img
                src="../assets/img/Logo.png"
                class="logo-criar-sensor"
                alt="Logo Pampa Serra"
            >

            <form method="POST">

                <label for="sensor">
                    Sensor:
                </label>

                <input
                    type="text"
                    id="sensor"
                    name="sensor"
                    class="form-control input-criar-sensor"
                    required
                >

                <label for="localizacao">
                    Localização:
                </label>

                <input
                    type="text"
                    id="localizacao"
                    name="localizacao"
                    class="form-control input-criar-sensor"
                    required
                >

                <label for="tipo">
                    Tipo de dado:
                </label>

                <input
                    type="text"
                    id="tipo"
                    name="tipo"
                    class="form-control input-criar-sensor"
                    required
                >

                <button
                    type="submit"
                    id="btn-criar-sensor"
                >
                    Criar
                </button>

            </form>

            <?php if (!empty($mensagem)) { ?>

                <p class="mt-3 text-center">
                    <?= htmlspecialchars($mensagem) ?>
                </p>

            <?php } ?>

        </div>

    </div>

    <script src="../script/script.js"></script>

</body>

</html>