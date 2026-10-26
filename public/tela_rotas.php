
<?php
require_once __DIR__ . "/verificar_acesso.php";
require_once __DIR__ . "/../conexao.php";

exigir_login();

$admin = (int)($_SESSION["tipo_usuario"] ?? 0) === 1;

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
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes da Rota - Pampa Serra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/style.css">
    <style>
        body {
            margin: 0;
            background: #f3f3f3;
        }

        .topo {
            background: #174eae;
            padding: 25px;
            color: white;
        }

        .painel {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin-top: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,.08);
        }

        .dados {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }

        .dado {
            background: #f1f4f8;
            padding: 20px;
            border-radius: 12px;
        }

        .dado span {
            display: block;
            color: #666;
            margin-bottom: 8px;
        }

        .dado strong {
            color: #174eae;
            font-size: 22px;
            overflow-wrap: anywhere;
        }

        .indicadores {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
            margin-top: 25px;
        }

        .indicador {
            background: #0755bc;
            color: white;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
        }

        .indicador strong {
            display: block;
            font-size: 26px;
            margin-top: 10px;
        }

        @media (max-width: 768px) {
            .dados, .indicadores {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<header class="topo">
    <div class="container d-flex justify-content-between align-items-center flex-wrap gap-3">
        <h1>Detalhes da Rota</h1>
        <a href="rotas.php" class="btn btn-light">
            Voltar
        </a>
    </div>
</header>

<main class="container">

    <section class="painel">

        <h2 class="mb-4">
            Rota <?= h($rota["codigo"]) ?>
        </h2>

        <div class="dados">

            <div class="dado">
                <span>Código</span>
                <strong><?= h($rota["codigo"]) ?></strong>
            </div>

            <div class="dado">
                <span>Status</span>
                <strong>
                    <?= (int)$rota["ativa"] === 1 ? "Ativa" : "Inativa" ?>
                </strong>
            </div>

            <div class="dado">
                <span>Origem</span>
                <strong><?= h($rota["origem"]) ?></strong>
            </div>

            <div class="dado">
                <span>Destino</span>
                <strong><?= h($rota["destino"]) ?></strong>
            </div>

            <div class="dado">
                <span>Parada obrigatória</span>
                <strong><?= h($rota["parada"] ?: "Não informada") ?></strong>
            </div>

            <div class="dado">
                <span>ID da rota</span>
                <strong><?= (int)$rota["id_rota"] ?></strong>
            </div>

        </div>

        <div class="indicadores">

            <div class="indicador">
                <h3>Velocidade cadastrada</h3>
                <strong>
                    <?= h($rota["velocidade_min"]) ?> -
                    <?= h($rota["velocidade_max"]) ?> km/h
                </strong>
            </div>

            <div class="indicador">
                <h3>Consumo cadastrado</h3>
                <strong>
                    <?= h($rota["consumo_min"]) ?> -
                    <?= h($rota["consumo_max"]) ?> L/km
                </strong>
            </div>

        </div>

        <div class="d-flex gap-2 flex-wrap mt-4">

            <a href="rotas.php" class="btn btn-secondary">
                Voltar à lista
            </a>

            <?php if ($admin): ?>
                <a
                    href="editar_rota.php?id=<?= (int)$rota["id_rota"] ?>"
                    class="btn btn-warning">
                    Editar Rota
                </a>
            <?php endif; ?>

        </div>

    </section>

</main>
</body>
</html>
