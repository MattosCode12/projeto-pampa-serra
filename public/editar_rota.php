
<?php
require_once __DIR__ . "/verificar_acesso.php";
require_once __DIR__ . "/../conexao.php";

exigir_admin();

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: rotas.php");
    exit;
}

$stmt = $conexao->prepare(
    "SELECT * FROM rota WHERE id_rota = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$rota = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$rota) {
    http_response_code(404);
    exit("Rota não encontrada.");
}

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    validar_csrf();

    $campos = [
        "codigo", "origem", "destino", "parada",
        "velocidade_min", "velocidade_max",
        "consumo_min", "consumo_max", "ativa"
    ];

    foreach ($campos as $campo) {
        $valor = $_POST[$campo] ?? "";
        $rota[$campo] = is_string($valor) ? trim($valor) : "";
    }

    $vmin = filter_var($rota["velocidade_min"], FILTER_VALIDATE_FLOAT);
    $vmax = filter_var($rota["velocidade_max"], FILTER_VALIDATE_FLOAT);
    $cmin = filter_var($rota["consumo_min"], FILTER_VALIDATE_FLOAT);
    $cmax = filter_var($rota["consumo_max"], FILTER_VALIDATE_FLOAT);

    if (
        $rota["codigo"] === "" ||
        mb_strlen($rota["codigo"]) > 100 ||
        $rota["origem"] === "" ||
        mb_strlen($rota["origem"]) > 150 ||
        $rota["destino"] === "" ||
        mb_strlen($rota["destino"]) > 150 ||
        mb_strlen($rota["parada"]) > 150 ||
        $vmin === false ||
        $vmax === false ||
        $cmin === false ||
        $cmax === false ||
        $vmin < 0 ||
        $cmin < 0 ||
        $vmax < $vmin ||
        $cmax < $cmin ||
        !in_array($rota["ativa"], ["0", "1"], true)
    ) {
        $erro = "Preencha os campos corretamente.";
    } else {
        $ativa = (int)$rota["ativa"];

        try {
            $stmt = $conexao->prepare(
                "UPDATE rota SET
                    codigo = ?,
                    origem = ?,
                    destino = ?,
                    parada = ?,
                    velocidade_min = ?,
                    velocidade_max = ?,
                    consumo_min = ?,
                    consumo_max = ?,
                    ativa = ?
                 WHERE id_rota = ?"
            );

            $stmt->bind_param(
                "ssssddddii",
                $rota["codigo"],
                $rota["origem"],
                $rota["destino"],
                $rota["parada"],
                $vmin,
                $vmax,
                $cmin,
                $cmax,
                $ativa,
                $id
            );

            $stmt->execute();
            $stmt->close();

            header("Location: tela_rotas.php?id=" . $id);
            exit;

        } catch (mysqli_sql_exception $e) {
            error_log($e->getMessage());
            $erro = (int)$e->getCode() === 1062
                ? "Já existe uma rota com esse código."
                : "Erro ao atualizar rota.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Rota - Pampa Serra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/style.css">
    <style>
        body { background: #f3f3f3; }
        .topo { background: #174eae; color: white; padding: 25px; }
        .painel { background: white; border-radius: 15px; padding: 30px; max-width: 800px; margin: 30px auto; }
    </style>
</head>
<body>
<header class="topo">
    <div class="container">
        <h1>Editar Rota</h1>
    </div>
</header>

<main class="container">
    <section class="painel">

        <?php if ($erro !== ""): ?>
            <div class="alert alert-danger"><?= h($erro) ?></div>
        <?php endif; ?>

        <form method="POST" class="row g-3">
            <?= campo_csrf() ?>

            <?php
            $textos = [
                "codigo" => "Código",
                "origem" => "Origem",
                "destino" => "Destino",
                "parada" => "Parada"
            ];
            ?>

            <?php foreach ($textos as $campo => $titulo): ?>
                <div class="col-md-6">
                    <label for="<?= h($campo) ?>" class="form-label">
                        <?= h($titulo) ?>
                    </label>
                    <input
                        type="text"
                        id="<?= h($campo) ?>"
                        name="<?= h($campo) ?>"
                        class="form-control"
                        maxlength="<?= $campo === "codigo" ? 100 : 150 ?>"
                        value="<?= h($rota[$campo] ?? "") ?>"
                        <?= $campo === "parada" ? "" : "required" ?>
                    >
                </div>
            <?php endforeach; ?>

            <?php
            $numericos = [
                "velocidade_min" => "Velocidade mínima (km/h)",
                "velocidade_max" => "Velocidade máxima (km/h)",
                "consumo_min" => "Consumo mínimo (L/km)",
                "consumo_max" => "Consumo máximo (L/km)"
            ];
            ?>

            <?php foreach ($numericos as $campo => $titulo): ?>
                <div class="col-md-6">
                    <label for="<?= h($campo) ?>" class="form-label">
                        <?= h($titulo) ?>
                    </label>
                    <input
                        type="number"
                        id="<?= h($campo) ?>"
                        name="<?= h($campo) ?>"
                        class="form-control"
                        step="0.01"
                        min="0"
                        value="<?= h($rota[$campo]) ?>"
                        required
                    >
                </div>
            <?php endforeach; ?>

            <div class="col-12">
                <label for="ativa" class="form-label">Situação</label>
                <select id="ativa" name="ativa" class="form-select">
                    <option value="1" <?= (int)$rota["ativa"] === 1 ? "selected" : "" ?>>Ativa</option>
                    <option value="0" <?= (int)$rota["ativa"] === 0 ? "selected" : "" ?>>Inativa</option>
                </select>
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    Salvar alterações
                </button>
                <a href="tela_rotas.php?id=<?= (int)$id ?>" class="btn btn-secondary">
                    Cancelar
                </a>
            </div>
        </form>

    </section>
</main>
</body>
</html>
