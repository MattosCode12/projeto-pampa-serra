<?php
require_once __DIR__ . "/verificar_acesso.php";
require_once __DIR__ . "/../conexao.php";

exigir_admin();

$erro = "";

$rota = [
    "codigo" => "",
    "origem" => "",
    "destino" => "",
    "parada" => "",
    "velocidade_min" => "",
    "velocidade_max" => "",
    "consumo_min" => "",
    "consumo_max" => "",
    "ativa" => "1"
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    validar_csrf();

    foreach ($rota as $campo => $valor) {
        $entrada = $_POST[$campo] ?? "";
        $rota[$campo] = is_string($entrada) ? trim($entrada) : "";
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
        $erro = "Preencha os dados corretamente.";
    } else {
        $ativa = (int)$rota["ativa"];

        try {
            $stmt = $conexao->prepare(
                "INSERT INTO rota
                 (codigo, origem, destino, parada,
                  velocidade_min, velocidade_max,
                  consumo_min, consumo_max, ativa)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "ssssddddi",
                $rota["codigo"],
                $rota["origem"],
                $rota["destino"],
                $rota["parada"],
                $vmin,
                $vmax,
                $cmin,
                $cmax,
                $ativa
            );

            $stmt->execute();
            $stmt->close();

            header("Location: cadastrar_rota.php?sucesso=cadastrado#rotas-cadastradas");
            exit;
        } catch (mysqli_sql_exception $e) {
            error_log($e->getMessage());

            $erro = (int)$e->getCode() === 1062
                ? "Já existe uma rota com esse código."
                : "Não foi possível cadastrar a rota.";
        }
    }
}

$erroLista = "";
$admin = true;
$busca = $_GET["busca"] ?? "";
$busca = is_string($busca) ? trim($busca) : "";
$termo = "%" . $busca . "%";
$rotas = [];

try {
    $stmt = $conexao->prepare(
        "SELECT * FROM rota
         WHERE codigo LIKE ?
            OR origem LIKE ?
            OR destino LIKE ?
         ORDER BY id_rota DESC"
    );

    $stmt->bind_param("sss", $termo, $termo, $termo);
    $stmt->execute();
    $rotas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());
    $erroLista = "Erro ao carregar rotas.";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Rota - Pampa Serra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/style.css">
    <link rel="stylesheet" href="../style/padrao-paginas.css">
</head>
<body class="ps-pagina">

<header class="ps-cabecalho">
    <div class="ps-cabecalho-conteudo">
        <img src="../assets/img/logo.png" alt="Pampa Serra" class="ps-logo">

        <h1 class="ps-titulo">Cadastrar Rota</h1>

        <a href="rotas.php" class="ps-voltar" aria-label="Voltar às rotas cadastradas">
            <span aria-hidden="true">←</span> Voltar
        </a>
    </div>
</header>

<main class="ps-conteudo flex-column gap-4">
    <div class="ps-moldura ps-moldura-cadastro">
        <section class="ps-painel">
            <h2 class="mb-4">Cadastrar Rota</h2>

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
                            value="<?= h($rota[$campo]) ?>"
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
                        <option value="1" <?= $rota["ativa"] === "1" ? "selected" : "" ?>>
                            Ativa
                        </option>
                        <option value="0" <?= $rota["ativa"] === "0" ? "selected" : "" ?>>
                            Inativa
                        </option>
                    </select>
                </div>

                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        Cadastrar Rota
                    </button>
                    <a href="rotas.php" class="btn btn-secondary">
                        Cancelar
                    </a>
                </div>
            </form>
        </section>
    </div>

    <div class="ps-moldura" id="rotas-cadastradas">
        <section class="ps-painel">
            <?php if ($erroLista !== ""): ?>
                <div class="alert alert-danger"><?= h($erroLista) ?></div>
            <?php endif; ?>

            <?php if (($_GET["sucesso"] ?? "") === "cadastrado"): ?>
                <div class="alert alert-success">
                    Rota cadastrada com sucesso!
                </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <h2>Rotas cadastradas</h2>
            </div>

            <form
                action="cadastrar_rota.php#rotas-cadastradas"
                method="GET"
                class="input-group mb-4"
            >
                <input
                    type="search"
                    name="busca"
                    class="form-control"
                    placeholder="Buscar por código, origem ou destino"
                    value="<?= h($busca) ?>"
                >
                <button type="submit" class="btn btn-primary">
                    Buscar
                </button>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Origem</th>
                            <th>Destino</th>
                            <th>Parada</th>
                            <th>Vel. mínima</th>
                            <th>Vel. máxima</th>
                            <th>Cons. mínimo</th>
                            <th>Cons. máximo</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rotas as $rotaListada): ?>
                            <tr>
                                <td><?= h($rotaListada["codigo"]) ?></td>
                                <td><?= h($rotaListada["origem"]) ?></td>
                                <td><?= h($rotaListada["destino"]) ?></td>
                                <td><?= h($rotaListada["parada"] ?? "") ?></td>
                                <td><?= h($rotaListada["velocidade_min"]) ?></td>
                                <td><?= h($rotaListada["velocidade_max"]) ?></td>
                                <td><?= h($rotaListada["consumo_min"]) ?></td>
                                <td><?= h($rotaListada["consumo_max"]) ?></td>
                                <td>
                                    <?= (int)$rotaListada["ativa"] === 1 ? "Ativa" : "Inativa" ?>
                                </td>
                                <td>
                                    <div class="acoes">
                                        <a
                                            class="btn btn-primary btn-sm"
                                            href="tela_rotas.php?id=<?= (int)$rotaListada["id_rota"] ?>"
                                        >
                                            Ver
                                        </a>

                                        <?php if ($admin): ?>
                                            <a
                                                class="btn btn-warning btn-sm"
                                                href="editar_rota.php?id=<?= (int)$rotaListada["id_rota"] ?>"
                                            >
                                                Editar
                                            </a>

                                            <form
                                                action="rotas.php"
                                                method="POST"
                                                onsubmit="return confirm('Deseja excluir esta rota?');"
                                            >
                                                <?= campo_csrf() ?>

                                                <input type="hidden" name="acao" value="excluir">
                                                <input
                                                    type="hidden"
                                                    name="id"
                                                    value="<?= (int)$rotaListada["id_rota"] ?>"
                                                >

                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    Excluir
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (!$rotas): ?>
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    Nenhuma rota encontrada.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>

</body>
</html>