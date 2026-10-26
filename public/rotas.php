
<?php
require_once __DIR__ . "/verificar_acesso.php";
require_once __DIR__ . "/../conexao.php";

exigir_login();

$admin = (int)($_SESSION["tipo_usuario"] ?? 0) === 1;
$erro = "";
$sucesso = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    exigir_admin();
    validar_csrf();

    $id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

    if (($_POST["acao"] ?? "") !== "excluir" || !$id || $id < 1) {
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

if (isset($_GET["sucesso"])) {
    if ($_GET["sucesso"] === "cadastrado") {
        $sucesso = "Rota cadastrada com sucesso!";
    } elseif ($_GET["sucesso"] === "excluido") {
        $sucesso = "Rota excluída com sucesso!";
    }
}

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
    $erro = "Erro ao carregar rotas.";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rotas - Pampa Serra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/style.css">
    <style>
        body { background: #f3f3f3; }
        .topo { background: #174eae; color: white; padding: 25px; }
        .painel { background: white; padding: 25px; border-radius: 15px; }
        .acoes { display: flex; flex-wrap: wrap; gap: 6px; }
        .acoes form { margin: 0; }
    </style>
</head>
<body>
<header class="topo">
    <div class="container d-flex justify-content-between align-items-center">
        <h1>Rotas</h1>
        <a href="home.php" class="btn btn-light">Voltar à Home</a>
    </div>
</header>

<main class="container py-4">

    <?php if ($erro !== ""): ?>
        <div class="alert alert-danger"><?= h($erro) ?></div>
    <?php endif; ?>

    <?php if ($sucesso !== ""): ?>
        <div class="alert alert-success"><?= h($sucesso) ?></div>
    <?php endif; ?>

    <section class="painel">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <h2>Rotas cadastradas</h2>

            <?php if ($admin): ?>
                <a href="cadastrar_rota.php" class="btn btn-primary">
                    + Cadastrar Rota
                </a>
            <?php endif; ?>
        </div>

        <form method="GET" class="input-group mb-4">
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
                    <?php foreach ($rotas as $rota): ?>
                        <tr>
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
                            <td>
                                <div class="acoes">
                                    <a
                                        class="btn btn-primary btn-sm"
                                        href="tela_rotas.php?id=<?= (int)$rota["id_rota"] ?>">
                                        Ver
                                    </a>

                                    <?php if ($admin): ?>
                                        <a
                                            class="btn btn-warning btn-sm"
                                            href="editar_rota.php?id=<?= (int)$rota["id_rota"] ?>">
                                            Editar
                                        </a>

                                        <form
                                            method="POST"
                                            onsubmit="return confirm('Deseja excluir esta rota?');">
                                            <?= campo_csrf() ?>
                                            <input type="hidden" name="acao" value="excluir">
                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?= (int)$rota["id_rota"] ?>"
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

</main>
</body>
</html>
