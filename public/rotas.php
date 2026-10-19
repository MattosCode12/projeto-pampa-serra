<?php
require_once "verificar_acesso.php";
exigir_login();
require_once "../conexao.php";
$erro = "";
$sucesso = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    exigir_admin();
    validar_csrf();
    $acao = $_POST["acao"] ?? "";
    if ($acao === "excluir") {
        $id = filter_input(
            INPUT_POST,
            "id",
            FILTER_VALIDATE_INT
        );
        if ($id) {
            $stmt = $conexao->prepare(
                "DELETE FROM rota WHERE id_rota = ?"
            );
            $stmt->bind_param("i", $id);
            $stmt->execute();
        }
        header("Location: rotas.php");
        exit;
    }
    if ($acao === "cadastrar") {
        $codigo = trim($_POST["codigo"] ?? "");
        $origem = trim($_POST["origem"] ?? "");
        $destino = trim($_POST["destino"] ?? "");
        $parada = trim($_POST["parada"] ?? "");
        $vmin = filter_var(
            $_POST["velocidade_min"] ?? null,
            FILTER_VALIDATE_FLOAT
        );
        $vmax = filter_var(
            $_POST["velocidade_max"] ?? null,
            FILTER_VALIDATE_FLOAT
        );
        $cmin = filter_var(
            $_POST["consumo_min"] ?? null,
            FILTER_VALIDATE_FLOAT
        );
        $cmax = filter_var(
            $_POST["consumo_max"] ?? null,
            FILTER_VALIDATE_FLOAT
        );
        if (
            $codigo === "" ||
            $origem === "" ||
            $destino === "" ||
            $vmin === false ||
            $vmax === false ||
            $cmin === false ||
            $cmax === false ||
            $vmin < 0 ||
            $cmin < 0 ||
            $vmax < $vmin ||
            $cmax < $cmin
        ) {
            $erro = "Verifique os dados da rota.";
        } else {
            try {
                $stmt = $conexao->prepare(
                    "INSERT INTO rota
                    (codigo, origem, destino, parada,
                     velocidade_min, velocidade_max,
                     consumo_min, consumo_max, ativa)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)"
                );
                $stmt->bind_param(
                    "ssssdddd",
                    $codigo,
                    $origem,
                    $destino,
                    $parada,
                    $vmin,
                    $vmax,
                    $cmin,
                    $cmax
                );
                $stmt->execute();
                header("Location: rotas.php?sucesso=1");
                exit;
            } catch (mysqli_sql_exception $e) {
                error_log($e->getMessage());
                $erro = "Não foi possível cadastrar. Verifique se o código já existe.";
            }
        }
    }
}
$busca = trim($_GET["busca"] ?? "");
$termo = "%" . $busca . "%";
$stmt = $conexao->prepare(
    "SELECT * FROM rota
     WHERE codigo LIKE ?
        OR origem LIKE ?
        OR destino LIKE ?
     ORDER BY id_rota DESC"
);
$stmt->bind_param(
    "sss",
    $termo,
    $termo,
    $termo
);
$stmt->execute();
$resultado = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Rotas - Pampa Serra</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet">
    <link rel="stylesheet"
          href="../style/style.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="d-flex justify-content-between mb-4">
        <h2>Gerenciamento de Rotas</h2>
        <a href="home.php"
           class="btn btn-dark">
            Voltar
        </a>
    </div>
    <?php if ($erro): ?>
        <div class="alert alert-danger">
            <?= h($erro) ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET["sucesso"])): ?>
        <div class="alert alert-success">
            Rota cadastrada com sucesso.
        </div>
    <?php endif; ?>
    <?php if (eh_admin()): ?>
        <div class="card shadow-sm p-4 mb-4">
            <h3>Cadastrar rota</h3>
            <form method="POST" class="row g-3">
                <?= campo_csrf() ?>
                <input
                    type="hidden"
                    name="acao"
                    value="cadastrar">
                <div class="col-md-4">
                    <label class="form-label">Código</label>
                    <input
                        class="form-control"
                        name="codigo"
                        maxlength="30"
                        required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Origem</label>
                    <input
                        class="form-control"
                        name="origem"
                        maxlength="100"
                        required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Destino</label>
                    <input
                        class="form-control"
                        name="destino"
                        maxlength="100"
                        required>
                </div>
                <div class="col-md-12">
                    <label class="form-label">
                        Parada obrigatória
                    </label>
                    <input
                        class="form-control"
                        name="parada"
                        maxlength="100">
                </div>
                <div class="col-md-3">
                    <label class="form-label">
                        Velocidade mínima
                    </label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        class="form-control"
                        name="velocidade_min"
                        required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">
                        Velocidade máxima
                    </label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        class="form-control"
                        name="velocidade_max"
                        required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">
                        Consumo mínimo
                    </label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        class="form-control"
                        name="consumo_min"
                        required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">
                        Consumo máximo
                    </label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        class="form-control"
                        name="consumo_max"
                        required>
                </div>
                <div class="col-12">
                    <button
                        type="submit"
                        class="btn btn-primary">
                        Cadastrar Rota
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>
    <form method="GET" class="mb-3">
        <div class="input-group">
            <input
                type="text"
                name="busca"
                class="form-control"
                placeholder="Buscar rota"
                value="<?= h($busca) ?>">
            <button
                type="submit"
                class="btn btn-dark">
                Buscar
            </button>
        </div>
    </form>
    <div class="card shadow-sm p-4">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Origem</th>
                        <th>Destino</th>
                        <th>Velocidade</th>
                        <th>Consumo</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($rota = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><?= h($rota["codigo"]) ?></td>
                        <td><?= h($rota["origem"]) ?></td>
                        <td><?= h($rota["destino"]) ?></td>
                        <td>
                            <?= h($rota["velocidade_min"]) ?>
                            -
                            <?= h($rota["velocidade_max"]) ?>
                            km/h
                        </td>
                        <td>
                            <?= h($rota["consumo_min"]) ?>
                            -
                            <?= h($rota["consumo_max"]) ?>
                            L/km
                        </td>
                        <td>
                            <?= $rota["ativa"]
                                ? "Ativa"
                                : "Inativa" ?>
                        </td>
                        <td>
                            <a
                                href="tela_rotas.php?id=<?= (int) $rota["id_rota"] ?>"
                                class="btn btn-primary btn-sm">
                                Ver
                            </a>
                            <?php if (eh_admin()): ?>
                                <form
                                    method="POST"
                                    class="d-inline"
                                    data-confirm="Excluir esta rota?">
                                    <?= campo_csrf() ?>
                                    <input
                                        type="hidden"
                                        name="acao"
                                        value="excluir">
                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= (int) $rota["id_rota"] ?>">
                                    <button
                                        type="submit"
                                        class="btn btn-danger btn-sm">
                                        Excluir
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                <?php if ($resultado->num_rows === 0): ?>
                    <tr>
                        <td colspan="7"
                            class="text-center">
                            Nenhuma rota encontrada.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="../script/script.js"></script>
</body>
</html>