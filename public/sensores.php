
<?php
require_once __DIR__ . "/verificar_acesso.php";
require_once __DIR__ . "/../conexao.php";

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
    $termo = "%" . $busca . "%";

    $stmt = $conexao->prepare(
        "SELECT id_sensor, nome, localizacao,
                tipo_dado, descricao, criado_em
         FROM sensores
         WHERE nome LIKE ?
            OR localizacao LIKE ?
            OR tipo_dado LIKE ?
         ORDER BY id_sensor ASC"
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

    <style>
        body {
            margin: 0;
            min-height: 100vh;
            background: #f5f5f5;
            font-family: Arial, sans-serif;
        }

        .pagina-sensores {
            width: 95%;
            max-width: 1200px;
            min-height: 600px;
            margin: 32px auto;
            padding: 25px 35px 35px;
            background: #064eb1;
            border-radius: 25px;
        }

        .topo-sensores {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 28px;
        }

        .logo-sensores {
            width: 100px;
            height: auto;
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

        .botoes-topo {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-topo {
            background: #113a82;
            color: white;
            text-decoration: none;
            padding: 11px 20px;
            border-radius: 20px;
            font-weight: bold;
        }

        .btn-topo:hover {
            background: #092a65;
            color: white;
        }

        .grade-sensores {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 24px;
        }

        .cartao-sensor {
            display: flex;
            flex-direction: column;
            background: white;
            color: black;
            border-radius: 12px;
            overflow: hidden;
            text-align: center;
            min-width: 0;
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

<body>

<div class="pagina-sensores">

    <header class="topo-sensores">

        <a href="home.php">
            <img
                src="../assets/img/logo.png"
                class="logo-sensores"
                alt="Pampa Serra"
            >
        </a>

        <form method="GET" action="sensores.php" class="busca-sensores">
            <input
                type="search"
                name="busca"
                placeholder="Buscar sensor"
                value="<?= h($busca) ?>"
            >

            <button type="submit">Buscar</button>
        </form>

        <div class="botoes-topo">

            <a href="home.php" class="btn-topo">
                Voltar
            </a>

            <?php if ($admin): ?>
                <a href="criar_sensor.php" class="btn-topo">
                    + Criar Sensor
                </a>
            <?php endif; ?>

        </div>

    </header>

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

            <article class="cartao-sensor">

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
