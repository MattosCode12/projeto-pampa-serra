
<?php
require_once __DIR__ . "/verificar_acesso.php";
require_once __DIR__ . "/../conexao.php";

exigir_login();

$admin = (int)($_SESSION["tipo_usuario"] ?? 0) === 1;

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: sensores.php");
    exit;
}

$stmt = $conexao->prepare(
    "SELECT id_sensor, nome, localizacao,
            tipo_dado, descricao, criado_em
     FROM sensores
     WHERE id_sensor = ?"
);

$stmt->bind_param("i", $id);
$stmt->execute();
$sensor = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$sensor) {
    http_response_code(404);
    exit("Sensor não encontrado.");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Sensor - Pampa Serra</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <link rel="stylesheet" href="../style/style.css">

    <style>
        body {
            margin: 0;
            min-height: 100vh;
            background: #f3f3f3;
        }

        .topo-sensor {
            background: #174eae;
            color: white;
            padding: 25px;
        }

        .painel-sensor {
            max-width: 950px;
            background: white;
            border-radius: 18px;
            margin: 35px auto;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,.08);
        }

        .titulo-sensor {
            color: #174eae;
            font-weight: 800;
            margin-bottom: 25px;
        }

        .grade-dados {
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
            color: #555;
            margin-bottom: 8px;
        }

        .dado strong {
            display: block;
            color: #174eae;
            font-size: 20px;
            overflow-wrap: anywhere;
        }

        .descricao {
            background: #eaf1fc;
            border-radius: 12px;
            padding: 25px;
            margin-top: 25px;
        }

        .btn-azul {
            background: #0755bc;
            color: white;
        }

        .btn-azul:hover {
            background: #08469a;
            color: white;
        }

        @media (max-width: 768px) {
            .grade-dados {
                grid-template-columns: 1fr;
            }

            .painel-sensor {
                margin: 20px 10px;
                padding: 20px;
            }
        }
    </style>
</head>

<body>

<header class="topo-sensor">
    <div class="container d-flex justify-content-between align-items-center flex-wrap gap-3">

        <h1>Detalhes do Sensor</h1>

        <a href="sensores.php" class="btn btn-light">
            Voltar
        </a>

    </div>
</header>

<main class="container">

    <section class="painel-sensor">

        <h2 class="titulo-sensor">
            <?= h($sensor["nome"]) ?>
        </h2>

        <div class="grade-dados">

            <div class="dado">
                <span>ID do Sensor</span>
                <strong>
                    #<?= (int)$sensor["id_sensor"] ?>
                </strong>
            </div>

            <div class="dado">
                <span>Nome</span>
                <strong>
                    <?= h($sensor["nome"]) ?>
                </strong>
            </div>

            <div class="dado">
                <span>Localização</span>
                <strong>
                    <?= h($sensor["localizacao"]) ?>
                </strong>
            </div>

            <div class="dado">
                <span>Tipo de dado</span>
                <strong>
                    <?= h($sensor["tipo_dado"]) ?>
                </strong>
            </div>

            <div class="dado">
                <span>Data de cadastro</span>
                <strong>
                    <?= h($sensor["criado_em"]) ?>
                </strong>
            </div>

        </div>

        <div class="descricao">

            <h3>Descrição</h3>

            <p class="mb-0">
                <?= nl2br(h($sensor["descricao"] ?: "Sem descrição cadastrada.")) ?>
            </p>

        </div>

        <div class="d-flex gap-2 flex-wrap mt-4">

            <a href="sensores.php" class="btn btn-secondary">
                Voltar à Lista
            </a>

            <?php if ($admin): ?>
                <a
                    href="editar_sensor.php?id=<?= (int)$sensor["id_sensor"] ?>"
                    class="btn btn-warning">
                    Editar Sensor
                </a>
            <?php endif; ?>

        </div>

    </section>

</main>

</body>
</html>
