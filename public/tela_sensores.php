<?php
require_once __DIR__ . "/verificar_acesso.php";
require_once __DIR__ . "/../conexao.php";
require_once __DIR__ . "/incidentes.php";

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

$erro = "";
$mensagem = "";

try {
    garantir_tabela_incidentes($conexao);
} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());
    $erro = "Não foi possível preparar a tabela de incidentes.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && $erro === "") {
    exigir_admin();
    validar_csrf();

    $acao = $_POST["acao"] ?? "";

    try {
        if ($acao === "simular") {
            $tipo = $_POST["tipo"] ?? "";
            $gravidade = $_POST["gravidade"] ?? "";
            $descricaoInc = trim((string)($_POST["descricao_incidente"] ?? ""));

            if (
                !in_array($tipo, INCIDENTE_TIPOS, true) ||
                !in_array($gravidade, INCIDENTE_GRAVIDADES, true) ||
                mb_strlen($descricaoInc) > 255
            ) {
                $erro = "Dados do incidente inválidos.";
            } else {
                $stmt = $conexao->prepare(
                    "INSERT INTO incidentes
                        (id_sensor, tipo, gravidade, descricao)
                     VALUES (?, ?, ?, ?)"
                );

                $stmt->bind_param(
                    "isss",
                    $id,
                    $tipo,
                    $gravidade,
                    $descricaoInc
                );

                $stmt->execute();
                $stmt->close();

                header("Location: tela_sensores.php?id=" . $id . "&sucesso=simulado");
                exit;
            }
        } elseif ($acao === "resolver") {
            $idIncidente = filter_input(
                INPUT_POST,
                "id_incidente",
                FILTER_VALIDATE_INT
            );

            if (!$idIncidente || $idIncidente < 1) {
                $erro = "Incidente inválido.";
            } else {
                $stmt = $conexao->prepare(
                    "UPDATE incidentes
                     SET resolvido = 1, resolvido_em = NOW()
                     WHERE id_incidente = ? AND id_sensor = ?"
                );

                $stmt->bind_param("ii", $idIncidente, $id);
                $stmt->execute();
                $stmt->close();

                header("Location: tela_sensores.php?id=" . $id . "&sucesso=resolvido");
                exit;
            }
        } else {
            $erro = "Ação inválida.";
        }
    } catch (mysqli_sql_exception $e) {
        error_log($e->getMessage());
        $erro = "Não foi possível salvar o incidente.";
    }
}

if (isset($_GET["sucesso"])) {
    $mensagens = [
        "simulado" => "Incidente simulado e registrado no relatório!",
        "resolvido" => "Incidente marcado como resolvido."
    ];

    $mensagem = $mensagens[$_GET["sucesso"]] ?? "";
}

$incidentes = [];

try {
    $stmt = $conexao->prepare(
        "SELECT id_incidente, tipo, gravidade, descricao,
                resolvido, criado_em, resolvido_em
         FROM incidentes
         WHERE id_sensor = ?
         ORDER BY criado_em DESC, id_incidente DESC"
    );

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $incidentes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());
}

$abertos = 0;

foreach ($incidentes as $inc) {
    if (!(int)$inc["resolvido"]) {
        $abertos++;
    }
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

        .bloco-incidente {
            border: 2px solid #dc3545;
            background: #fff5f5;
            border-radius: 12px;
            padding: 25px;
            margin-top: 25px;
        }

        .bloco-incidente h3 {
            color: #b02a37;
        }

        .item-incidente {
            background: white;
            border: 1px solid #eee;
            border-left: 5px solid #dc3545;
            border-radius: 10px;
            padding: 14px 16px;
            margin-top: 12px;
        }

        .item-incidente.resolvido {
            border-left-color: #198754;
            opacity: .8;
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

        <?php if ($erro !== ""): ?>
            <div class="alert alert-danger">
                <?= h($erro) ?>
            </div>
        <?php endif; ?>

        <?php if ($mensagem !== ""): ?>
            <div class="alert alert-success">
                <?= h($mensagem) ?>
            </div>
        <?php endif; ?>

        <h2 class="titulo-sensor">
            <?= h($sensor["nome"]) ?>

            <?php if ($abertos > 0): ?>
                <span class="badge text-bg-danger fs-6 align-middle">
                    ⚠ <?= $abertos ?> incidente(s) em aberto
                </span>
            <?php endif; ?>
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

        <?php if ($admin): ?>
            <div class="bloco-incidente" id="simular">

                <h3>Simular incidente</h3>
                <p class="text-muted">
                    Registra uma pane ou acidente neste sensor. O incidente
                    aparece automaticamente em Relatórios.
                </p>

                <form method="POST">

                    <?= campo_csrf() ?>

                    <input type="hidden" name="acao" value="simular">

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label for="tipo" class="form-label">
                                Tipo de incidente
                            </label>
                            <select name="tipo" id="tipo" class="form-select" required>
                                <?php foreach (INCIDENTE_TIPOS as $tipoOpcao): ?>
                                    <option value="<?= h($tipoOpcao) ?>">
                                        <?= h($tipoOpcao) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="gravidade" class="form-label">
                                Gravidade
                            </label>
                            <select name="gravidade" id="gravidade" class="form-select" required>
                                <?php foreach (INCIDENTE_GRAVIDADES as $grav): ?>
                                    <option value="<?= h($grav) ?>">
                                        <?= h($grav) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="descricao_incidente" class="form-label">
                                Observação (opcional)
                            </label>
                            <input
                                type="text"
                                name="descricao_incidente"
                                id="descricao_incidente"
                                class="form-control"
                                maxlength="255"
                            >
                        </div>

                    </div>

                    <button type="submit" class="btn btn-danger mt-3">
                        ⚠ Simular incidente
                    </button>

                </form>

            </div>
        <?php endif; ?>

        <div class="mt-4">

            <h3>Histórico de incidentes</h3>

            <?php if (!$incidentes): ?>
                <p class="text-muted mb-0">
                    Nenhum incidente registrado para este sensor.
                </p>
            <?php endif; ?>

            <?php foreach ($incidentes as $inc): ?>
                <?php $resolvido = (bool)(int)$inc["resolvido"]; ?>

                <div class="item-incidente <?= $resolvido ? "resolvido" : "" ?>">

                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">

                        <div>
                            <strong><?= h($inc["tipo"]) ?></strong>
                            <span class="badge <?= incidente_classe($inc["gravidade"]) ?>">
                                <?= h($inc["gravidade"]) ?>
                            </span>
                            <span class="badge <?= $resolvido ? "text-bg-success" : "text-bg-danger" ?>">
                                <?= $resolvido ? "Resolvido" : "Em aberto" ?>
                            </span>
                        </div>

                        <small class="text-muted">
                            <?= h(incidente_data($inc["criado_em"])) ?>
                        </small>

                    </div>

                    <?php if ($inc["descricao"]): ?>
                        <p class="mb-0 mt-2">
                            <?= h($inc["descricao"]) ?>
                        </p>
                    <?php endif; ?>

                    <?php if ($resolvido): ?>
                        <small class="text-muted">
                            Resolvido em <?= h(incidente_data($inc["resolvido_em"])) ?>
                        </small>
                    <?php elseif ($admin): ?>
                        <form method="POST" class="mt-2">
                            <?= campo_csrf() ?>
                            <input type="hidden" name="acao" value="resolver">
                            <input
                                type="hidden"
                                name="id_incidente"
                                value="<?= (int)$inc["id_incidente"] ?>"
                            >
                            <button type="submit" class="btn btn-success btn-sm">
                                Marcar como resolvido
                            </button>
                        </form>
                    <?php endif; ?>

                </div>
            <?php endforeach; ?>

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

            <a href="relatorios.php" class="btn btn-azul">
                Ver relatórios
            </a>

        </div>

    </section>

</main>

</body>
</html>