<?php
require_once __DIR__ . "/verificar_acesso.php";
require_once __DIR__ . "/../conexao.php";
require_once __DIR__ . "/incidentes.php";

exigir_login();

$admin = (int)($_SESSION["tipo_usuario"] ?? 0) === 1;
$erro = "";
$mensagem = "";

$busca = $_GET["busca"] ?? "";
$busca = is_string($busca) ? trim($busca) : "";

if (isset($_GET["sucesso"])) {
    $mensagens = [
        "cadastro" => "Sensor cadastrado com sucesso!",
        "edicao" => "Sensor atualizado com sucesso!",
        "exclusao" => "Sensor excluído com sucesso!"
    ];

    $mensagem = $mensagens[$_GET["sucesso"]] ?? "";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    exigir_admin();
    validar_csrf();

    $id = filter_input(
        INPUT_POST,
        "id_sensor",
        FILTER_VALIDATE_INT
    );

    if (($_POST["acao"] ?? "") !== "excluir" || !$id || $id < 1) {
        $erro = "Sensor inválido.";
    } else {
        try {
            garantir_tabela_incidentes($conexao);

            $stmt = $conexao->prepare(
                "DELETE FROM incidentes WHERE id_sensor = ?"
            );

            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            $stmt = $conexao->prepare(
                "DELETE FROM sensores WHERE id_sensor = ?"
            );

            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            header("Location: sensores.php?sucesso=exclusao");
            exit;
        } catch (mysqli_sql_exception $e) {
            error_log($e->getMessage());
            $erro = "Não foi possível excluir o sensor.";
        }
    }
}

$sensores = [];

try {
    garantir_tabela_incidentes($conexao);

    $termo = "%" . $busca . "%";

    $stmt = $conexao->prepare(
        "SELECT s.id_sensor, s.nome, s.localizacao,
                s.tipo_dado, s.descricao, s.criado_em,
                (SELECT COUNT(*)
                 FROM incidentes i
                 WHERE i.id_sensor = s.id_sensor
                   AND i.resolvido = 0) AS incidentes_abertos
         FROM sensores s
         WHERE s.nome LIKE ?
            OR s.localizacao LIKE ?
            OR s.tipo_dado LIKE ?
         ORDER BY s.id_sensor ASC"
    );

    $stmt->bind_param("sss", $termo, $termo, $termo);
    $stmt->execute();

    $sensores = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());
    $erro = "Não foi possível carregar os sensores.";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sensores - Pampa Serra</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <link rel="stylesheet" href="../style/style.css">
    <link rel="stylesheet" href="../style/padrao-paginas.css">

    <style>
        .pagina-sensores {
            width: 94%;
            max-width: 1800px;
            min-height: 600px;
            margin: 32px auto;
            padding: 25px 35px 35px;
            background: #0c53b8;
            border-radius: 21px;
        }

        .topo-sensores {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 28px;
        }



        .busca-sensores {
            display: flex;
            width: 300px;
            max-width: 100%;
        }

        .busca-sensores input {
            border-radius: 12px 0 0 12px;
            background: #e5e7eb;
            border: none;
            padding: 10px;
            min-width: 0;
            flex: 1;
        }

        .busca-sensores button {
            border: none;
            border-radius: 0 12px 12px 0;
            padding: 10px 15px;
            background: white;
            font-weight: bold;
        }

        .criar-sensor {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 46px;
            padding: 11px 22px;
            border-radius: 15px;
            background: #fff;
            color: #174eae;
            font-weight: 700;
            text-decoration: none;
        }

        .criar-sensor:hover {
            background: #e2e5ea;
            color: #123a85;
        }

        .criar-sensor:focus-visible {
            outline: 3px solid #fff;
            outline-offset: 3px;
        }

        .topo-sensores {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 300px minmax(0, 1fr);
        }
        .topo-sensores .busca-sensores { grid-column: 2; }
        .acoes-topo-sensores { grid-column: 3; justify-self: end; }
        @media (max-width: 900px) {
            .topo-sensores { grid-template-columns: minmax(0, 1fr); }
            .topo-sensores .busca-sensores {
                grid-column: 1;
                grid-row: 1;
                justify-self: center;
            }
            .acoes-topo-sensores { grid-column: 1; grid-row: 2; }
        }
        .acoes-topo-sensores { margin-left: auto; }

        .grade-sensores {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 24px;
        }

        .cartao-sensor {
            display: flex;
            flex-direction: column;
            background: white;
            color: black;
            border-radius: 15px;
            overflow: hidden;
            text-align: center;
            min-width: 0;
        }

        .cartao-alerta {
            box-shadow: 0 0 0 4px #dc3545;
        }

        .alerta-incidente {
            background: #dc3545;
            color: white;
            font-weight: 700;
            padding: 8px;
        }

        .linha-sensor {
            padding: 13px;
            border-bottom: 1px solid #ddd;
            overflow-wrap: anywhere;
        }

        .nome-sensor {
            background: #eaf1fc;
            color: #174eae;
            font-size: 20px;
            font-weight: 800;
        }

        .descricao-sensor {
            color: #555;
            flex: 1;
        }

        .acoes-sensor {
            display: flex;
            gap: 8px;
            padding: 12px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .acoes-sensor form {
            margin: 0;
        }

        .mensagem-vazia {
            grid-column: 1 / -1;
            color: white;
            text-align: center;
            padding: 35px;
            font-size: 20px;
            font-weight: bold;
        }

        @media (max-width: 900px) {
            .grade-sensores {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 600px) {
            .pagina-sensores {
                padding: 20px 15px;
            }

            .topo-sensores {
                flex-wrap: wrap;
                justify-content: center;
            }

            .busca-sensores {
                order: 3;
                width: 100%;
            }

            .grade-sensores {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body class="ps-pagina">

<header class="ps-cabecalho">
    <div class="ps-cabecalho-conteudo">
        <a href="home.php" aria-label="Pampa Serra — início">
            <img src="../assets/img/logo.png" alt="Pampa Serra" class="ps-logo">
        </a>
        <h1 class="ps-titulo">Sensores</h1>
        <a href="home.php" class="ps-voltar" aria-label="Voltar à Home">
            <span aria-hidden="true">←</span> Voltar
        </a>
    </div>
</header>

<div class="pagina-sensores">

    <div class="topo-sensores">



        <form method="GET" action="sensores.php" class="busca-sensores">
            <input
                type="search"
                name="busca"
                placeholder="Buscar sensor"
                aria-label="Buscar sensor"
                value="<?= h($busca) ?>"
            >

            <button type="submit">Buscar</button>
        </form>

        <div class="acoes-topo-sensores">



            <?php if ($admin): ?>
                <a href="criar_sensor.php" class="criar-sensor">
                    <span aria-hidden="true">+</span> Criar Sensor
                </a>
            <?php endif; ?>

        </div>

    </div>

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

    <main class="grade-sensores">

        <?php foreach ($sensores as $sensor): ?>

            <?php $abertos = (int)$sensor["incidentes_abertos"]; ?>

            <article class="cartao-sensor <?= $abertos > 0 ? "cartao-alerta" : "" ?>">

                <?php if ($abertos > 0): ?>
                    <div class="alerta-incidente">
                        ⚠ <?= $abertos ?> incidente(s) em aberto
                    </div>
                <?php endif; ?>

                <div class="linha-sensor nome-sensor">
                    <?= h($sensor["nome"]) ?>
                </div>

                <div class="linha-sensor">
                    <strong>Localização</strong>
                    <p class="mb-0">
                        <?= h($sensor["localizacao"]) ?>
                    </p>
                </div>

                <div class="linha-sensor">
                    <strong>Tipo de dado</strong>
                    <p class="mb-0">
                        <?= h($sensor["tipo_dado"]) ?>
                    </p>
                </div>

                <div class="linha-sensor descricao-sensor">
                    <?= h($sensor["descricao"] ?: "Sem descrição") ?>
                </div>

                <div class="acoes-sensor">

                    <a
                        href="tela_sensores.php?id=<?= (int)$sensor["id_sensor"] ?>"
                        class="btn btn-primary btn-sm">
                        Ver
                    </a>

                    <?php if ($admin): ?>

                        <a
                            href="editar_sensor.php?id=<?= (int)$sensor["id_sensor"] ?>"
                            class="btn btn-warning btn-sm">
                            Editar
                        </a>

                        <a
                            href="tela_sensores.php?id=<?= (int)$sensor["id_sensor"] ?>#simular"
                            class="btn btn-outline-danger btn-sm">
                            ⚠ Incidente
                        </a>

                        <form
                            method="POST"
                            onsubmit="return confirm('Deseja excluir este sensor?');"
                        >
                            <?= campo_csrf() ?>

                            <input
                                type="hidden"
                                name="acao"
                                value="excluir"
                            >

                            <input
                                type="hidden"
                                name="id_sensor"
                                value="<?= (int)$sensor["id_sensor"] ?>"
                            >

                            <button type="submit" class="btn btn-danger btn-sm">
                                Excluir
                            </button>
                        </form>

                    <?php endif; ?>

                </div>

            </article>

        <?php endforeach; ?>

        <?php if (!$sensores): ?>
            <div class="mensagem-vazia">
                Nenhum sensor encontrado.
            </div>
        <?php endif; ?>

    </main>

</div>

</body>
</html>