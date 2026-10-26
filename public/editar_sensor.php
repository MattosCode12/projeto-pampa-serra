
<?php
require_once __DIR__ . "/verificar_acesso.php";
require_once __DIR__ . "/../conexao.php";

exigir_admin();

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: sensores.php");
    exit;
}

$stmt = $conexao->prepare(
    "SELECT id_sensor, nome, localizacao, tipo_dado, descricao
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

$sensor["descricao"] = (string)($sensor["descricao"] ?? "");
$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    validar_csrf();

    $campos = ["nome", "localizacao", "tipo_dado", "descricao"];

    foreach ($campos as $campo) {
        $valor = $_POST[$campo] ?? "";
        $sensor[$campo] = is_string($valor)
            ? trim($valor)
            : "";
    }

    if (
        $sensor["nome"] === "" ||
        $sensor["localizacao"] === "" ||
        $sensor["tipo_dado"] === ""
    ) {
        $erro = "Preencha os campos obrigatórios.";
    } elseif (
        mb_strlen($sensor["nome"]) > 100 ||
        mb_strlen($sensor["localizacao"]) > 100 ||
        mb_strlen($sensor["tipo_dado"]) > 150
    ) {
        $erro = "Um dos campos ultrapassa o tamanho permitido.";
    } else {
        try {
            $stmt = $conexao->prepare(
                "UPDATE sensores
                 SET nome = ?,
                     localizacao = ?,
                     tipo_dado = ?,
                     descricao = ?
                 WHERE id_sensor = ?"
            );

            $stmt->bind_param(
                "ssssi",
                $sensor["nome"],
                $sensor["localizacao"],
                $sensor["tipo_dado"],
                $sensor["descricao"],
                $id
            );

            $stmt->execute();
            $stmt->close();

            header("Location: tela_sensores.php?id=" . $id);
            exit;

        } catch (mysqli_sql_exception $e) {
            error_log($e->getMessage());
            $erro = "Não foi possível atualizar o sensor.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Sensor - Pampa Serra</title>

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

        .topo {
            padding: 25px;
            background: #174eae;
            color: white;
        }

        .painel {
            max-width: 750px;
            margin: 35px auto;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,.08);
        }

        .titulo {
            color: #174eae;
            font-weight: 800;
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
    </style>
</head>

<body>

<header class="topo">
    <div class="container d-flex justify-content-between align-items-center flex-wrap gap-3">

        <h1>Editar Sensor</h1>

        <a
            href="tela_sensores.php?id=<?= (int)$id ?>"
            class="btn btn-light">
            Voltar
        </a>

    </div>
</header>

<main class="container">

    <section class="painel">

        <h2 class="titulo mb-4">
            <?= h($sensor["nome"]) ?>
        </h2>

        <?php if ($erro !== ""): ?>
            <div class="alert alert-danger" role="alert">
                <?= h($erro) ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <?= campo_csrf() ?>

            <div class="mb-3">
                <label for="nome" class="form-label">
                    Nome do Sensor
                </label>

                <input
                    type="text"
                    id="nome"
                    name="nome"
                    class="form-control"
                    maxlength="100"
                    value="<?= h($sensor["nome"]) ?>"
                    required
                >
            </div>

            <div class="mb-3">
                <label for="localizacao" class="form-label">
                    Localização
                </label>

                <input
                    type="text"
                    id="localizacao"
                    name="localizacao"
                    class="form-control"
                    maxlength="100"
                    value="<?= h($sensor["localizacao"]) ?>"
                    required
                >
            </div>

            <div class="mb-3">
                <label for="tipo_dado" class="form-label">
                    Tipo de dado
                </label>

                <input
                    type="text"
                    id="tipo_dado"
                    name="tipo_dado"
                    class="form-control"
                    maxlength="150"
                    value="<?= h($sensor["tipo_dado"]) ?>"
                    required
                >
            </div>

            <div class="mb-4">
                <label for="descricao" class="form-label">
                    Descrição
                </label>

                <textarea
                    id="descricao"
                    name="descricao"
                    class="form-control"
                    rows="4"
                ><?= h($sensor["descricao"]) ?></textarea>
            </div>

            <div class="d-flex gap-2 flex-wrap">

                <button type="submit" class="btn btn-azul">
                    Salvar alterações
                </button>

                <a
                    href="tela_sensores.php?id=<?= (int)$id ?>"
                    class="btn btn-secondary">
                    Cancelar
                </a>

            </div>

        </form>

    </section>

</main>

</body>
</html>
