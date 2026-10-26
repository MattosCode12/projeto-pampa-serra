
<?php
require_once __DIR__ . "/verificar_acesso.php";
require_once __DIR__ . "/../conexao.php";

exigir_admin();

$erro = "";
$nome = "";
$localizacao = "";
$tipo_dado = "";
$descricao = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    validar_csrf();

    $campos = ["nome", "localizacao", "tipo_dado", "descricao"];

    foreach ($campos as $campo) {
        $valor = $_POST[$campo] ?? "";
        $$campo = is_string($valor) ? trim($valor) : "";
    }

    if (
        $nome === "" ||
        $localizacao === "" ||
        $tipo_dado === ""
    ) {
        $erro = "Preencha os campos obrigatórios.";
    } elseif (
        mb_strlen($nome) > 100 ||
        mb_strlen($localizacao) > 100 ||
        mb_strlen($tipo_dado) > 150
    ) {
        $erro = "Um dos campos ultrapassa o tamanho permitido.";
    } else {
        try {
            $stmt = $conexao->prepare(
                "INSERT INTO sensores
                 (nome, localizacao, tipo_dado, descricao)
                 VALUES (?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "ssss",
                $nome,
                $localizacao,
                $tipo_dado,
                $descricao
            );

            $stmt->execute();
            $stmt->close();

            header("Location: sensores.php?sucesso=cadastro");
            exit;

        } catch (mysqli_sql_exception $e) {
            error_log($e->getMessage());
            $erro = "Não foi possível cadastrar o sensor.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Sensor - Pampa Serra</title>

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

        .tela-criar-sensor {
            max-width: 850px;
            margin: 35px auto;
            padding: 30px;
            background: #0755bc;
            border-radius: 22px;
        }

        .topo-criar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        .topo-criar h1 {
            color: white;
            font-weight: 800;
            font-size: 28px;
        }

        .btn-voltar-criar {
            background: #113a82;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 12px;
        }

        .btn-voltar-criar:hover {
            color: white;
            background: #092a65;
        }

        .card-criar-sensor {
            max-width: 650px;
            margin: auto;
            padding: 30px;
            background: white;
            border-radius: 16px;
        }

        .logo-criar-sensor {
            display: block;
            width: 120px;
            margin: 0 auto 25px;
        }

        .card-criar-sensor label {
            font-weight: bold;
            margin-bottom: 6px;
        }

        .input-criar-sensor {
            margin-bottom: 18px;
        }

        #btn-criar-sensor {
            background: #0755bc;
            color: white;
            border: none;
            border-radius: 10px;
            padding: 12px 25px;
            font-weight: bold;
        }

        #btn-criar-sensor:hover {
            background: #08469a;
        }

        @media (max-width: 600px) {
            .tela-criar-sensor {
                margin: 15px;
                padding: 18px;
            }

            .card-criar-sensor {
                padding: 20px;
            }
        }
    </style>
</head>

<body>

<main class="tela-criar-sensor">

    <div class="topo-criar">
        <h1>Cadastrar Sensor</h1>

        <a href="sensores.php" class="btn-voltar-criar">
            Voltar
        </a>
    </div>

    <div class="card-criar-sensor">

        <img
            src="../assets/img/logo.png"
            class="logo-criar-sensor"
            alt="Logo Pampa Serra"
        >

        <?php if ($erro !== ""): ?>
            <div class="alert alert-danger">
                <?= h($erro) ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <?= campo_csrf() ?>

            <label for="nome">Sensor:</label>
            <input
                type="text"
                id="nome"
                name="nome"
                maxlength="100"
                class="form-control input-criar-sensor"
                value="<?= h($nome) ?>"
                required
            >

            <label for="localizacao">Localização:</label>
            <input
                type="text"
                id="localizacao"
                name="localizacao"
                maxlength="100"
                class="form-control input-criar-sensor"
                value="<?= h($localizacao) ?>"
                required
            >

            <label for="tipo_dado">Tipo de dado:</label>
            <input
                type="text"
                id="tipo_dado"
                name="tipo_dado"
                maxlength="150"
                class="form-control input-criar-sensor"
                value="<?= h($tipo_dado) ?>"
                required
            >

            <label for="descricao">Descrição:</label>
            <textarea
                id="descricao"
                name="descricao"
                class="form-control input-criar-sensor"
                rows="4"
            ><?= h($descricao) ?></textarea>

            <div class="d-flex gap-2 flex-wrap mt-3">

                <button type="submit" id="btn-criar-sensor">
                    Criar Sensor
                </button>

                <a href="sensores.php" class="btn btn-secondary">
                    Cancelar
                </a>

            </div>

        </form>

    </div>

</main>

</body>
</html>
