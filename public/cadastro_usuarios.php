
<?php
require_once __DIR__ . "/verificar_acesso.php";
require_once __DIR__ . "/../conexao.php";

exigir_admin();

$erro = "";
$nome = "";
$email = "";
$telefone = "";
$tipo_usuario = "0";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    validar_csrf();

    $nome = trim((string)($_POST["nome"] ?? ""));
    $email = mb_strtolower(
        trim((string)($_POST["email"] ?? "")),
        "UTF-8"
    );
    $telefone = trim((string)($_POST["telefone"] ?? ""));
    $senha = $_POST["senha"] ?? "";
    $tipo_usuario = $_POST["tipo_usuario"] ?? "0";

    if (
        $nome === "" ||
        $email === "" ||
        !is_string($senha) ||
        $senha === ""
    ) {
        $erro = "Preencha os campos obrigatórios.";
    } elseif (
        mb_strlen($nome) > 100 ||
        strlen($email) > 100 ||
        strlen($telefone) > 20
    ) {
        $erro = "Um dos campos ultrapassa o tamanho permitido.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Informe um e-mail válido.";
    } elseif (strlen($senha) < 8) {
        $erro = "A senha deve ter pelo menos 8 caracteres.";
    } elseif (!in_array($tipo_usuario, ["0", "1"], true)) {
        $erro = "Tipo de usuário inválido.";
    } else {
        try {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $tipo = (int)$tipo_usuario;

            $stmt = $conexao->prepare(
                "INSERT INTO usuarios
                 (nome, email, senha, telefone, tipo_usuario)
                 VALUES (?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "ssssi",
                $nome,
                $email,
                $hash,
                $telefone,
                $tipo
            );

            $stmt->execute();
            $stmt->close();

            $_SESSION["mensagem_sucesso"] =
                "Usuário cadastrado com sucesso!";

            header("Location: listar_cadastro.php");
            exit;

        } catch (mysqli_sql_exception $e) {
            error_log($e->getMessage());

            $erro = (int)$e->getCode() === 1062
                ? "Já existe um usuário com este e-mail."
                : "Não foi possível cadastrar o usuário.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Usuário - Pampa Serra</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/style.css">

    <style>
        body {
            background: #f3f3f3;
            min-height: 100vh;
        }

        .topo {
            background: #174eae;
            padding: 25px;
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
    <div class="container d-flex justify-content-between align-items-center">
        <h1>Cadastrar Usuário</h1>
        <a href="home.php" class="btn btn-light">
            Voltar à Home
        </a>
    </div>
</header>

<main class="container">

    <section class="painel">

        <img
            src="../assets/img/logo.png"
            alt="Pampa Serra"
            class="d-block mx-auto mb-4"
            style="width:100px"
        >

        <h2 class="titulo text-center mb-4">
            Novo Usuário
        </h2>

        <?php if ($erro !== ""): ?>
            <div class="alert alert-danger" role="alert">
                <?= h($erro) ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <?= campo_csrf() ?>

            <div class="mb-3">
                <label for="nome" class="form-label">Nome</label>
                <input
                    type="text"
                    name="nome"
                    id="nome"
                    class="form-control"
                    maxlength="100"
                    value="<?= h($nome) ?>"
                    required
                >
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    class="form-control"
                    maxlength="100"
                    value="<?= h($email) ?>"
                    required
                >
            </div>

            <div class="mb-3">
                <label for="telefone" class="form-label">
                    Telefone
                </label>
                <input
                    type="tel"
                    name="telefone"
                    id="telefone"
                    class="form-control"
                    maxlength="20"
                    value="<?= h($telefone) ?>"
                >
            </div>

            <div class="mb-3">
                <label for="senha" class="form-label">
                    Senha
                </label>
                <input
                    type="password"
                    name="senha"
                    id="senha"
                    class="form-control"
                    minlength="8"
                    autocomplete="new-password"
                    required
                >
            </div>

            <div class="mb-4">
                <label for="tipo_usuario" class="form-label">
                    Tipo de Usuário
                </label>

                <select
                    name="tipo_usuario"
                    id="tipo_usuario"
                    class="form-select"
                    required
                >
                    <option value="0" <?= $tipo_usuario === "0" ? "selected" : "" ?>>
                        Usuário
                    </option>
                    <option value="1" <?= $tipo_usuario === "1" ? "selected" : "" ?>>
                        Administrador
                    </option>
                </select>
            </div>

            <div class="d-flex flex-wrap gap-2">

                <button type="submit" class="btn btn-azul">
                    Cadastrar Usuário
                </button>

                <a href="listar_cadastro.php" class="btn btn-secondary">
                    Ver Usuários
                </a>

                <a href="home.php" class="btn btn-outline-secondary">
                    Cancelar
                </a>

            </div>

        </form>

    </section>

</main>

</body>
</html>
