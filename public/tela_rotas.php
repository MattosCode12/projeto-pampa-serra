<?php
require_once "verificar_acesso.php";
exigir_login();
require_once "../conexao.php";
$id = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);
if (!$id) {
    header("Location: rotas.php");
    exit;
}
$stmt = $conexao->prepare(
    "SELECT * FROM rota WHERE id_rota = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$rota = $stmt->get_result()->fetch_assoc();
if (!$rota) {
    http_response_code(404);
    exit("Rota não encontrada.");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Monitoramento de Rotas</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet">
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
        rel="stylesheet">
    <link rel="stylesheet"
          href="../style/style.css">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom px-4">
    <a class="navbar-brand"
       href="home.php">
        <img
            src="../assets/img/logo.png"
            alt="Pampa Serra"
            class="logo"
            style="width:90px">
    </a>
    <div class="mx-auto search-container">
        <a href="rotas.php"
           class="btn btn-light">
            Voltar para rotas
        </a>
    </div>
</nav>
<div class="container-fluid mt-4">
    <div class="row justify-content-center g-4">
        <div class="col-lg-3">
            <div class="info-card text-center">
                <div class="alert-line danger">
                    Atenção Máxima
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <div class="alert-line warning">
                    Atenção
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <div class="alert-line success">
                    Esperado
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <small>
                    Legenda de alertas.
                    Sem leituras em tempo real.
                </small>
            </div>
        </div>
        <div class="col-lg-2">
            <div class="info-card text-center">
                <h2>Referência:</h2>
                <p>
                    <?= h($rota["velocidade_min"]) ?>
                    -
                    <?= h($rota["velocidade_max"]) ?>
                    km/h
                </p>
                <p>Consumo:</p>
                <p>
                    <?= h($rota["consumo_min"]) ?>
                    -
                    <?= h($rota["consumo_max"]) ?>
                    L/km
                </p>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="info-card text-center rotas-card">
                <a
                    href="rotas.php"
                    class="btn btn-light w-100 mb-3">
                    Rota <?= h($rota["codigo"]) ?>
                    -
                    <?= h($rota["origem"]) ?>
                    a
                    <?= h($rota["destino"]) ?>
                </a>
                <p>
                    Parada Obrigatória:
                    <?= h(
                        $rota["parada"] ?: "Não informada"
                    ) ?>
                </p>
                <p>
                    Status:
                    <?= $rota["ativa"]
                        ? "Ativa"
                        : "Inativa" ?>
                </p>
            </div>
        </div>
    </div>
    <div class="mapa-container mt-4">
        <a
            href="rotas.php"
            class="btn-mapa btn-left"
            aria-label="Voltar para as rotas">
            <i class="bi bi-arrow-left-circle"></i>
        </a>
        <div class="mapa-box">
            <img
                id="mapImage"
                src="../assets/img/image.png"
                alt="Ilustração da rota Porto Alegre e Serra Gaúcha">
        </div>
        <div class="info-mapa">
            <i
                class="bi bi-info-circle-fill text-success fs-1">
            </i>
            <h3>Velocidade cadastrada</h3>
            <h2>
                <?= h($rota["velocidade_min"]) ?>
                -
                <?= h($rota["velocidade_max"]) ?>
                km/h
            </h2>
            <h3>Consumo cadastrado</h3>
            <h2>
                <?= h($rota["consumo_min"]) ?>
                -
                <?= h($rota["consumo_max"]) ?>
                L/km
            </h2>
        </div>
    </div>
</div>
<script src="../script/script.js"></script>
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js">
</script>
</body>
</html>