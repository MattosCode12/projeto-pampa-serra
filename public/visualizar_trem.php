
<?php
require_once __DIR__ . "/verificar_acesso.php";
require_once __DIR__ . "/../conexao.php";

exigir_login();

$admin = (int)($_SESSION["tipo_usuario"] ?? 0) === 1;

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: trens.php");
    exit;
}

$stmt = $conexao->prepare(
    "SELECT t.id_trem, t.codigo, t.modelo, t.status,
            u.nome AS responsavel
     FROM trens t
     INNER JOIN usuarios u ON u.id = t.id_usuario
     WHERE t.id_trem = ?"
);

$stmt->bind_param("i", $id);
$stmt->execute();
$trem = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$trem) {
    http_response_code(404);
    exit("Trem não encontrado.");
}

function trem_h($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Visualizar Trem - Pampa Serra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/style.css">
    <style>
        body { background: #f3f3f3; }
        .topo { background: #174eae; color: white; padding: 25px; }
        .painel {
            background: white;
            border-radius: 15px;
            padding: 30px;
        }
    </style>
</head>
<body>

<header class="topo">
    <div class="container d-flex justify-content-between align-items-center">
        <h1>Detalhes do Trem</h1>
        <a href="trens.php" class="btn btn-light">Voltar</a>
    </div>
</header>

<main class="container py-4">

    <section class="painel mx-auto" style="max-width: 750px;">

        <h2><?= trem_h($trem["codigo"]) ?></h2>
        <p class="text-muted">Informações do trem cadastrado</p>

        <hr>

        <dl class="row">
            <dt class="col-sm-4">Código</dt>
            <dd class="col-sm-8"><?= trem_h($trem["codigo"]) ?></dd>

            <dt class="col-sm-4">Modelo</dt>
            <dd class="col-sm-8"><?= trem_h($trem["modelo"]) ?></dd>

            <dt class="col-sm-4">Status</dt>
            <dd class="col-sm-8"><?= trem_h($trem["status"]) ?></dd>

            <dt class="col-sm-4">Responsável</dt>
            <dd class="col-sm-8"><?= trem_h($trem["responsavel"]) ?></dd>
        </dl>

        <div class="d-flex gap-2 mt-4">

            <?php if ($admin): ?>
                <a href="editar_trem.php?id=<?= (int)$trem["id_trem"] ?>"
                   class="btn btn-primary">
                    Editar Trem
                </a>
            <?php endif; ?>

            <a href="trens.php" class="btn btn-secondary">
                Voltar à lista
            </a>

        </div>

    </section>

</main>
</body>
</html>
