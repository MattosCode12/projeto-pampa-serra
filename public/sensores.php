<?php
require_once __DIR__ . "/verificar_acesso.php";
require_once __DIR__ . "/../conexao.php";
exigir_login();
$admin = (int)($_SESSION["tipo_usuario"] ?? 0) === 1;
$erro = "";
$mensagem = "";
$busca = trim($_GET["busca"] ?? "");
if (isset($_GET["sucesso"])) {
    if ($_GET["sucesso"] === "cadastro") {
        $mensagem = "Sensor cadastrado com sucesso!";
    }
    if ($_GET["sucesso"] === "exclusao") {
        $mensagem = "Sensor excluído com sucesso!";
    }
}
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    exigir_admin();
    validar_csrf();
    $acao = $_POST["acao"] ?? "";
    if ($acao === "cadastrar") {
        $nome = trim($_POST["nome"] ?? "");
        $localizacao = trim($_POST["localizacao"] ?? "");
        $tipo_dado = trim($_POST["tipo_dado"] ?? "");
        if (
            $nome === "" ||
            $localizacao === "" ||
            $tipo_dado === ""
        ) {
            $erro = "Preencha todos os campos.";
        } else {
            try {
                $stmt = $conexao->prepare(
                    "INSERT INTO sensores
                    (nome, localizacao, tipo_dado)
                    VALUES (?, ?, ?)"
                );
                $stmt->bind_param(
                    "sss",
                    $nome,
                    $localizacao,
                    $tipo_dado
                );
                $stmt->execute();
                $stmt->close();
                header("Location: sensores.php?sucesso=cadastro");
                exit;
            } catch (mysqli_sql_exception $e) {
                error_log($e->getMessage());
                $erro = "Erro ao cadastrar sensor.";
            }
        }
    }
    if ($acao === "excluir") {
        $id = filter_input(
            INPUT_POST,
            "id_sensor",
            FILTER_VALIDATE_INT
        );
        if (!$id || $id < 1) {
            $erro = "Sensor inválido.";
        } else {
            try {
                $stmt = $conexao->prepare(
                    "DELETE FROM sensores
                     WHERE id_sensor = ?"
                );
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->close();
                header("Location: sensores.php?sucesso=exclusao");
                exit;
            } catch (mysqli_sql_exception $e) {
                error_log($e->getMessage());
                $erro = "Erro ao excluir sensor.";
            }
        }
    }
}
$sensores = [];
try {
    $termo = "%" . $busca . "%";
    $stmt = $conexao->prepare(
        "SELECT * FROM sensores
         WHERE nome LIKE ?
            OR localizacao LIKE ?
            OR tipo_dado LIKE ?
         ORDER BY id_sensor ASC"
    );
    $stmt->bind_param(
        "sss",
        $termo,
        $termo,
        $termo
    );
    $stmt->execute();
    $resultado = $stmt->get_result();
    while ($sensor = $resultado->fetch_assoc()) {
        $sensores[] = $sensor;
    }
    $stmt->close();
} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());
    $erro = "Erro ao carregar os sensores.";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
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
            padding: 20px 35px 35px;
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
            width: 245px;
            height: 38px;
        }
        .busca-sensores input {
            width: 100%;
            border: none;
            outline: none;
            border-radius: 12px 0 0 12px;
            background: #ddd;
            padding: 0 12px;
        }
        .busca-sensores button {
            border: none;
            border-radius: 0 12px 12px 0;
            background: #ddd;
            padding: 0 12px;
            font-size: 20px;
        }
        .botoes-topo {
            display: flex;
            gap: 15px;
        }
        .btn-topo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #113a82;
            color: white;
            border: none;
            border-radius: 25px;
            padding: 10px 23px;
            font-size: 17px;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
        }
        .btn-topo:hover {
            background: #092a65;
            color: white;
        }
        .grade-sensores {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            max-width: 1050px;
            margin: 0 auto;
        }
        .cartao-sensor {
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background: white;
            border-radius: 10px;
            color: black;
            text-align: center;
            font-weight: 800;
            min-width: 0;
        }
        .linha-sensor {
            padding: 12px 8px;
            border-bottom: 2px solid black;
            overflow-wrap: anywhere;
        }
        .linha-sensor:last-child {
            border-bottom: none;
        }
        .cabecalho-sensor {
            display: flex;
            align-items: stretch;
            padding: 0;
            min-height: 43px;
        }
        .nome-sensor {
            flex: 1;
            padding: 11px 6px;
            align-content: center;
        }
        .form-excluir {
            border-left: 2px solid black;
            display: flex;
            align-items: stretch;
        }
        .btn-excluir {
            background: white;
            border: none;
            padding: 8px 12px;
            cursor: pointer;
            font-size: 22px;
        }
        .btn-excluir:hover {
            background: #ffdddd;
        }
        .linha-localizacao {
            text-decoration: underline;
        }
        .mensagem-vazia {
            grid-column: 1 / -1;
            text-align: center;
            color: white;
            padding: 30px;
            font-size: 20px;
            font-weight: bold;
        }
        .area-voltar {
            margin-top: 25px;
        }
        .btn-voltar {
            background: #113a82;
            color: white;
            padding: 10px 22px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: bold;
        }
        .btn-voltar:hover {
            background: #092a65;
            color: white;
        }
        @media (max-width: 900px) {
            .grade-sensores {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 600px) {
            .pagina-sensores {
                width: 96%;
                padding: 20px 15px;
            }
            .topo-sensores {
                flex-wrap: wrap;
                justify-content: center;
            }
            .grade-sensores {
                grid-template-columns: 1fr;
            }
            .busca-sensores {
                width: 100%;
                order: 3;
            }
            .botoes-topo {
                flex-wrap: wrap;
                justify-content: center;
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
        <form
            method="GET"
            action="sensores.php"
            class="busca-sensores"
        >
            <input
                type="text"
                name="busca"
                placeholder="Buscar"
                value="<?= h($busca) ?>"
            >
            <button type="submit" aria-label="Buscar">
                &#128269;
            </button>
        </form>
        <div class="botoes-topo">
            <a href="home.php" class="btn-topo">
                Mais
            </a>
            <?php if ($admin): ?>
                <button
                    type="button"
                    class="btn-topo"
                    data-bs-toggle="modal"
                    data-bs-target="#modalCriar"
                >
                    Criar
                </button>
            <?php endif; ?>
        </div>
    </header>
    <?php if ($erro): ?>
        <div class="alert alert-danger">
            <?= h($erro) ?>
        </div>
    <?php endif; ?>
    <?php if ($mensagem): ?>
        <div class="alert alert-success">
            <?= h($mensagem) ?>
        </div>
    <?php endif; ?>
    <main class="grade-sensores">
        <?php if (count($sensores) > 0): ?>
            <?php foreach ($sensores as $sensor): ?>
                <article class="cartao-sensor">
                    <div class="linha-sensor cabecalho-sensor">
                        <div class="nome-sensor">
                            <?= h($sensor["nome"]) ?>
                        </div>
                        <?php if ($admin): ?>
                            <form
                                method="POST"
                                class="form-excluir"
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
                                    value="<?= h($sensor["id_sensor"]) ?>"
                                >
                                <button
                                    type="submit"
                                    class="btn-excluir"
                                    aria-label="Excluir sensor"
                                    title="Excluir sensor"
                                >
                                    &#128465;
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                    <div class="linha-sensor linha-localizacao">
                        <?= h($sensor["localizacao"]) ?>
                    </div>
                    <div class="linha-sensor">
                        <?= h($sensor["tipo_dado"]) ?>
                    </div>
                    <?php if (
                        isset($sensor["descricao"]) &&
                        $sensor["descricao"] !== ""
                    ): ?>
                        <div class="linha-sensor">
                            <?= h($sensor["descricao"]) ?>
                        </div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="mensagem-vazia">
                Nenhum sensor encontrado.
            </div>
        <?php endif; ?>
    </main>
    <div class="area-voltar">
        <a href="home.php" class="btn-voltar">
            Voltar
        </a>
    </div>
</div>
<?php if ($admin): ?>
<div
    class="modal fade"
    id="modalCriar"
    tabindex="-1"
    aria-labelledby="tituloModal"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tituloModal">
                    Cadastrar Sensor
                </h5>
                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Fechar"
                ></button>
            </div>
            <form method="POST" action="sensores.php">
                <div class="modal-body">
                    <?= campo_csrf() ?>
                    <input
                        type="hidden"
                        name="acao"
                        value="cadastrar"
                    >
                    <div class="mb-3">
                        <label class="form-label" for="nome">
                            Nome do Sensor
                        </label>
                        <input
                            type="text"
                            name="nome"
                            id="nome"
                            class="form-control"
                            required
                        >
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="localizacao">
                            Localização
                        </label>
                        <input
                            type="text"
                            name="localizacao"
                            id="localizacao"
                            class="form-control"
                            placeholder="Ex.: KM 22 - Aeroporto"
                            required
                        >
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="tipo_dado">
                            Tipo de Dado
                        </label>
                        <input
                            type="text"
                            name="tipo_dado"
                            id="tipo_dado"
                            class="form-control"
                            placeholder="Ex.: Sensor de temperatura"
                            required
                        >
                    </div>
                </div>
                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Cadastrar Sensor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>