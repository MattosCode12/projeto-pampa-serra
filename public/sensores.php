<?php
require_once "verificar_acesso.php";
exigir_login();
require_once "../conexao.php";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    exigir_admin();
    validar_csrf();
    $id = filter_input(
        INPUT_POST,
        "id_sensor",
        FILTER_VALIDATE_INT
    );
    if ($id) {
        $stmt = $conexao->prepare(
            "DELETE FROM sensores WHERE id_sensor = ?"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
    header("Location: sensores.php");
    exit;
}
$busca = trim($_GET["busca"] ?? "");
$termo = "%" . $busca . "%";
$stmt = $conexao->prepare(
    "SELECT * FROM sensores
     WHERE nome LIKE ?
        OR localizacao LIKE ?
        OR tipo_dado LIKE ?
     ORDER BY id_sensor"
);
$stmt->bind_param("sss", $termo, $termo, $termo);
$stmt->execute();
$resultado = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Sensores - Pampa Serra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
          rel="stylesheet">
    <link rel="stylesheet" href="../style/style.css">
</head>
<body class="sensores-page">
<nav class="navbar-sensores">
    <a href="home.php">
        <img
            src="../assets/img/Logo.png"
            id="logo-sensores"
            alt="Logo Pampa Serra">
    </a>
    <form class="buscar-sensor" method="GET">
        <input
            type="text"
            name="busca"
            placeholder="Buscar"
            value="<?= h($busca) ?>">
        <button type="submit" aria-label="Buscar">
            <i class="bi bi-search"></i>
        </button>
    </form>
    <div class="botoes-topo">
        <?php if (eh_admin()): ?>
            <a href="criar_sensor.php"
               class="btn btn-topo">
                Criar
            </a>
        <?php endif; ?>
        <a href="home.php" class="btn btn-topo">
            Voltar
        </a>
    </div>
</nav>
<main class="sensores-container">
    <div class="row g-4 justify-content-center">
        <?php while ($sensor = $resultado->fetch_assoc()): ?>
            <div class="col-12 col-md-6 col-lg-4 d-flex justify-content-center">
                <div class="sensor-card">
                    <div class="sensor-linha sensor-titulo">
                        <span>
                            <a
                                class="text-dark text-decoration-none"
                                href="tela_sensores.php?id=<?= (int)$sensor["id_sensor"] ?>">
                                <?= h($sensor["nome"]) ?>
                            </a>
                        </span>
                        <?php if (eh_admin()): ?>
                            <form
                                method="POST"
                                style="position:absolute;right:0;top:0;height:100%;z-index:2"
                                onsubmit="return confirm('Excluir este sensor?')">
                                <?= campo_csrf() ?>
                                <input
                                    type="hidden"
                                    name="id_sensor"
                                    value="<?= (int)$sensor["id_sensor"] ?>">
                                <button
                                    type="submit"
                                    style="position:static;height:100%"
                                    aria-label="Excluir sensor">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                    <div class="sensor-linha">
                        <u><?= h($sensor["localizacao"]) ?></u>
                    </div>
                    <div class="sensor-linha">
                        <?= h($sensor["tipo_dado"]) ?>
                    </div>
                    <div class="sensor-linha sensor-desc">
                        <?= nl2br(h($sensor["descricao"])) ?>
                    </div>
                    <div class="sensor-linha">
                        <a
                            href="tela_sensores.php?id=<?= (int)$sensor["id_sensor"] ?>"
                            class="btn btn-primary btn-sm">
                            Detalhes
                        </a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
    <?php if ($resultado->num_rows === 0): ?>
        <p class="text-white text-center mt-4">
            Nenhum sensor encontrado.
        </p>
    <?php endif; ?>
</main>
<script src="../script/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>