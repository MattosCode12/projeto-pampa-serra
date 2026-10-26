<?php
require_once __DIR__ . "/verificar_acesso.php";
require_once __DIR__ . "/../conexao.php";

exigir_login();

$admin = (int)($_SESSION["tipo_usuario"] ?? 0) === 1;
$erro = "";

if (empty($_SESSION["csrf_trens"])) {
    $_SESSION["csrf_trens"] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!$admin) {
        http_response_code(403);
        exit("Acesso negado.");
    }

    $token = $_POST["csrf_trens"] ?? "";

    if (!is_string($token) ||
        !hash_equals($_SESSION["csrf_trens"], $token)) {
        http_response_code(403);
        exit("Solicitação inválida.");
    }

    $id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

    if (($_POST["acao"] ?? "") === "excluir" && $id > 0) {
        try {
            $stmt = $conexao->prepare(
                "DELETE FROM trens WHERE id_trem = ?"
            );
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            header("Location: trens.php?sucesso=excluido");
            exit;
        } catch (mysqli_sql_exception $e) {
            error_log($e->getMessage());
            $erro = "Não foi possível excluir o trem.";
        }
    } else {
        $erro = "Trem inválido.";
    }
}

$busca = trim((string)($_GET["busca"] ?? ""));
$termo = "%" . $busca . "%";

$stmt = $conexao->prepare(
    "SELECT t.id_trem, t.codigo, t.modelo, t.status,
            u.nome AS responsavel
     FROM trens t
     INNER JOIN usuarios u ON u.id = t.id_usuario
     WHERE t.codigo LIKE ?
        OR t.modelo LIKE ?
        OR u.nome LIKE ?
     ORDER BY t.codigo"
);

$stmt->bind_param("sss", $termo, $termo, $termo);
$stmt->execute();
$trens = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

function trem_h($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Trens - Pampa Serra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/style.css">
    <style>
        body { background: #f3f3f3; }
        .topo { background: #174eae; color: white; padding: 25px; }
        .painel { background: white; padding: 25px; border-radius: 15px; }
        .btn-azul { background: #0755bc; color: white; }
        .btn-azul:hover { background: #08469a; color: white; }
    </style>
</head>
<body>

<header class="topo">
    <div class="container d-flex justify-content-between align-items-center">
        <h1>Trens</h1>
        <a href="home.php" class="btn btn-light">Voltar à Home</a>
    </div>
</header>

<main class="container py-4">

    <?php if ($erro !== ""): ?>
        <div class="alert alert-danger">
            <?= trem_h($erro) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET["sucesso"])): ?>
        <div class="alert alert-success">
            Operação realizada com sucesso.
        </div>
    <?php endif; ?>

    <section class="painel">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <h2>Trens cadastrados</h2>

            <?php if ($admin): ?>
                <a href="cadastrar_trem.php" class="btn btn-azul">
                    + Cadastrar trem
                </a>
            <?php endif; ?>
        </div>

        <form method="GET" class="d-flex gap-2 mb-4">
            <input
                type="search"
                name="busca"
                class="form-control"
                placeholder="Buscar código, modelo ou responsável"
                value="<?= trem_h($busca) ?>"
            >
            <button class="btn btn-azul">Buscar</button>
        </form>

        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Modelo</th>
                        <th>Status</th>
                        <th>Responsável</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($trens as $trem): ?>
                    <tr>
                        <td><?= trem_h($trem["codigo"]) ?></td>
                        <td><?= trem_h($trem["modelo"]) ?></td>
                        <td><?= trem_h($trem["status"]) ?></td>
                        <td><?= trem_h($trem["responsavel"]) ?></td>
                        <td>
                            <div class="d-flex gap-2 flex-wrap">

                                <a class="btn btn-sm btn-primary"
                                   href="visualizar_trem.php?id=<?= (int)$trem["id_trem"] ?>">
                                    Ver
                                </a>

                                <?php if ($admin): ?>
                                    <a class="btn btn-sm btn-warning"
                                       href="editar_trem.php?id=<?= (int)$trem["id_trem"] ?>">
                                        Editar
                                    </a>

                                    <form method="POST"
                                          onsubmit="return confirm('Excluir este trem?');">

                                        <input type="hidden"
                                               name="csrf_trens"
                                               value="<?= trem_h($_SESSION["csrf_trens"]) ?>">

                                        <input type="hidden" name="acao" value="excluir">

                                        <input type="hidden"
                                               name="id"
                                               value="<?= (int)$trem["id_trem"] ?>">

                                        <button type="submit"
                                                class="btn btn-sm btn-danger">
                                            Excluir
                                        </button>
                                    </form>
                                <?php endif; ?>

                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$trens): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            Nenhum trem encontrado.
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