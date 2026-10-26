
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

    $acao = $_POST["acao"] ?? "";

    if ($acao === "excluir") {
        $id = filter_input(
            INPUT_POST,
            "id",
            FILTER_VALIDATE_INT
        );

        if (!$id || $id < 1) {
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
}

if (isset($_GET["sucesso"])) {
    if ($_GET["sucesso"] === "cadastrado") {
        $sucesso = "Rota cadastrada com sucesso!";
    } elseif ($_GET["sucesso"] === "excluido") {
        $sucesso = "Rota excluída com sucesso!";
    } elseif ($_GET["sucesso"] === "editado") {
        $sucesso = "Rota atualizada com sucesso!";
    }
}

$busca = trim((string)($_GET["busca"] ?? ""));
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
    $erro = "Não foi possível carregar as rotas.";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Rotas - Pampa Serra</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="../style/style.css">

    <style>
        body {
            margin: 0;
            background: #f3f3f3;
            min-height: 100vh;
        }

        .cabecalho-rotas {
            background: #174eae;
            color: white;
            padding: 25px;
        }

        .painel-rotas {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        .btn-azul {
            background: #0755bc;
            color: white;
            border: none;
        }

        .btn-azul:hover {
            background: #08469a;
            color: white;
        }

        .tabela-rotas {
            width: 100%;
        }

        .acoes-rota {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .acoes-rota form {
            margin: 0;
        }

        .titulo-rotas {
            color: #174eae;
            font-weight: 800;
        }
    </style>
</head>

<body>

<header class="cabecalho-rotas">
    <div class="container d-flex justify-content-between align-items-center flex-wrap gap-3">

        <h1 class="m-0">Rotas</h1>

        <a href="home.php" class="btn btn-light">
            Voltar à Home
        </a>

    </div>
</header>

<main class="container py-4">

    <?php if ($erro !== ""): ?>
        <div class="alert alert-danger" role="alert">
            <?= h($erro) ?>
        </div>
    <?php endif; ?>

    <?php if ($sucesso !== ""): ?>
        <div class="alert alert-success" role="status">
            <?= h($sucesso) ?>
        </div>
    <?php endif; ?>

    <section class="painel-rotas">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

            <div>
                <h2 class="titulo-rotas">Rotas cadastradas</h2>
                <p class="text-muted mb-0">
                    <?= count($rotas) ?> rota(s) encontrada(s)
                </p>
            </div>

            <?php if ($admin): ?>
                <a
                    href="cadastrar_rota.php"
                    class="btn btn-azul">
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

            <button type="submit" class="btn btn-azul">
                Buscar
            </button>

        </form>

        <div class="table-responsive">

            <table class="table table-hover align-middle tabela-rotas">

                <thead class="table-light">
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
                        <th>Ações</th>
                    </tr>
                </thead>

                <tbody>

                <?php foreach ($rotas as $rota): ?>

                    <tr>

                        <td>
                            <?= (int)$rota["id_rota"] ?>
                        </td>

                        <td>
                            <strong>
                                <?= h($rota["codigo"]) ?>
                            </strong>
                        </td>

                        <td>
                            <?= h($rota["origem"]) ?>
                        </td>

                        <td>
                            <?= h($rota["destino"]) ?>
                        </td>

                        <td>
                            <?= h($rota["parada"] ?? "") ?>
                        </td>

                        <td>
                            <?= h($rota["velocidade_min"]) ?>
                        </td>

                        <td>
                            <?= h($rota["velocidade_max"]) ?>
                        </td>

                        <td>
                            <?= h($rota["consumo_min"]) ?>
                        </td>

                        <td>
                            <?= h($rota["consumo_max"]) ?>
                        </td>

                        <td>
                            <?php if ((int)$rota["ativa"] === 1): ?>
                                <span class="badge bg-success">
                                    Ativa
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary">
                                    Inativa
                                </span>
                            <?php endif; ?>
                        </td>

                        <td>

                            <div class="acoes-rota">

                                <a
                                    href="tela_rotas.php?id=<?= (int)$rota["id_rota"] ?>"
                                    class="btn btn-primary btn-sm">
                                    Ver
                                </a>

                                <?php if ($admin): ?>

                                    <a
                                        href="editar_rota.php?id=<?= (int)$rota["id_rota"] ?>"
                                        class="btn btn-warning btn-sm">
                                        Editar
                                    </a>

                                    <form
                                        method="POST"
                                        onsubmit="return confirm('Deseja realmente excluir esta rota?');">

                                        <?= campo_csrf() ?>

                                        <input
                                            type="hidden"
                                            name="acao"
                                            value="excluir"
                                        >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int)$rota["id_rota"] ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="btn btn-danger btn-sm">
                                            Excluir
                                        </button>

                                    </form>

                                <?php endif; ?>

                            </div>

                        </td>

                    </tr>

                <?php endforeach; ?>

                <?php if (empty($rotas)): ?>

                    <tr>
                        <td colspan="11" class="text-center py-4">
                            Nenhuma rota encontrada.
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </section>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
