<?php
require_once __DIR__ . "/verificar_acesso.php";
require_once __DIR__ . "/../conexao.php";
exigir_login();
$erro = "";
$sucesso = "";
$admin = (int)($_SESSION["tipo_usuario"] ?? 0) === 1;
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
        if (!$id) {
            $erro = "Rota inválida.";
        } else {
            try {
                $stmt = $conexao->prepare(
                    "DELETE FROM rota WHERE id_rota = ?"
                );
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->close();
                header("Location: rotas.php?sucesso=excluido");
                exit;
            } catch (mysqli_sql_exception $e) {
                error_log($e->getMessage());
                $erro = "Não foi possível excluir a rota.";
            }
        }
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
                $stmt->close();
                header("Location: rotas.php?sucesso=cadastrado");
                exit;
            } catch (mysqli_sql_exception $e) {
                error_log($e->getMessage());
                $erro = "Não foi possível cadastrar a rota.";
            }
        }
    }
}
if (isset($_GET["sucesso"])) {
    if ($_GET["sucesso"] === "cadastrado" || $_GET["sucesso"] === "1") {
        $sucesso = "Rota cadastrada com sucesso!";
    } elseif ($_GET["sucesso"] === "excluido") {
        $sucesso = "Rota excluída com sucesso!";
    }
}
$busca = trim($_GET["busca"] ?? "");
$termo = "%" . $busca . "%";
$resultado = null;
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
    $resultado = $stmt->get_result();
} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());
    $erro = "Não foi possível carregar as rotas. Verifique o banco de dados.";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rotas - Pampa Serra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/style.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="d-flex justify-content-between mb-4">
        <h2>Rotas</h2>
        <a href="home.php" class="btn btn-dark">Voltar</a>
    </div>
    <div class="card p-4 shadow-sm mb-4">
        <img
            src="../assets/img/logo.png"
            class="mx-auto d-block mb-4"
            alt="Pampa Serra"
            style="width:120px;"
        >
        <?php if ($erro): ?>
            <div class="alert alert-danger">
                <?= h($erro) ?>
            </div>
        <?php endif; ?>
        <?php if ($sucesso): ?>
            <div class="alert alert-success">
                <?= h($sucesso) ?>
            </div>
        <?php endif; ?>
        <form method="GET" class="input-group mb-4">
            <input
                type="text"
                name="busca"
                class="form-control"
                placeholder="Buscar rota"
                value="<?= h($busca) ?>"
            >
            <button type="submit" class="btn btn-dark">
                Buscar
            </button>
        </form>
        <?php if ($admin): ?>
            <h4 class="mb-3">Cadastrar Rota</h4>
            <form method="POST" class="row g-3 mb-4">
                <?= campo_csrf() ?>
                <input type="hidden" name="acao" value="cadastrar">
                <div class="col-md-6">
                    <label class="form-label">Código</label>
                    <input
                        type="text"
                        name="codigo"
                        class="form-control"
                        required
                    >
                </div>
                <div class="col-md-6">
                    <label class="form-label">Parada</label>
                    <input
                        type="text"
                        name="parada"
                        class="form-control"
                    >
                </div>
                <div class="col-md-6">
                    <label class="form-label">Origem</label>
                    <input
                        type="text"
                        name="origem"
                        class="form-control"
                        required
                    >
                </div>
                <div class="col-md-6">
                    <label class="form-label">Destino</label>
                    <input
                        type="text"
                        name="destino"
                        class="form-control"
                        required
                    >
                </div>
                <div class="col-md-6">
                    <label class="form-label">Velocidade mínima</label>
                    <input
                        type="number"
                        name="velocidade_min"
                        step="any"
                        min="0"
                        class="form-control"
                        required
                    >
                </div>
                <div class="col-md-6">
                    <label class="form-label">Velocidade máxima</label>
                    <input
                        type="number"
                        name="velocidade_max"
                        step="any"
                        min="0"
                        class="form-control"
                        required
                    >
                </div>
                <div class="col-md-6">
                    <label class="form-label">Consumo mínimo</label>
                    <input
                        type="number"
                        name="consumo_min"
                        step="any"
                        min="0"
                        class="form-control"
                        required
                    >
                </div>
                <div class="col-md-6">
                    <label class="form-label">Consumo máximo</label>
                    <input
                        type="number"
                        name="consumo_max"
                        step="any"
                        min="0"
                        class="form-control"
                        required
                    >
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        Cadastrar Rota
                    </button>
                </div>
            </form>
        <?php endif; ?>
        <h4 class="mb-3">Rotas Cadastradas</h4>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Código</th>
                        <th>Origem</th>
                        <th>Destino</th>
                        <th>Parada</th>
                        <th>Vel. Mín.</th>
                        <th>Vel. Máx.</th>
                        <th>Cons. Mín.</th>
                        <th>Cons. Máx.</th>
                        <th>Status</th>
                        <?php if ($admin): ?>
                            <th>Ações</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                <?php if ($resultado && $resultado->num_rows > 0): ?>
                    <?php while ($rota = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td><?= h($rota["id_rota"]) ?></td>
                            <td><?= h($rota["codigo"]) ?></td>
                            <td><?= h($rota["origem"]) ?></td>
                            <td><?= h($rota["destino"]) ?></td>
                            <td><?= h($rota["parada"] ?? "") ?></td>
                            <td><?= h($rota["velocidade_min"]) ?></td>
                            <td><?= h($rota["velocidade_max"]) ?></td>
                            <td><?= h($rota["consumo_min"]) ?></td>
                            <td><?= h($rota["consumo_max"]) ?></td>
                            <td>
                                <?= (int)$rota["ativa"] === 1 ? "Ativa" : "Inativa" ?>
                            </td>
                            <?php if ($admin): ?>
                                <td>
                                    <form
                                        method="POST"
                                        onsubmit="return confirm('Deseja excluir esta rota?');"
                                    >
                                        <?= campo_csrf() ?>
                                        <input
                                            type="hidden"
                                            name="acao"
                                            value="excluir"
                                        >
                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= h($rota["id_rota"]) ?>"
                                        >
                                        <button
                                            type="submit"
                                            class="btn btn-danger btn-sm"
                                        >
                                            Excluir
                                        </button>
                                    </form>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td
                            colspan="<?= $admin ? 11 : 10 ?>"
                            class="text-center"
                        >
                            Nenhuma rota encontrada.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>