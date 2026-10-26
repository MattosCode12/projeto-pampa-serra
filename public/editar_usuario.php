
<?php
require_once __DIR__ . "/verificar_acesso.php";
require_once __DIR__ . "/../conexao.php";

exigir_admin();

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: listar_cadastro.php");
    exit;
}

$stmt = $conexao->prepare(
    "SELECT id, nome, email, telefone, tipo_usuario
     FROM usuarios
     WHERE id = ?"
);

$stmt->bind_param("i", $id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$usuario) {
    http_response_code(404);
    exit("Usuário não encontrado.");
}

$erro = "";
$proprioUsuario = $id === (int)($_SESSION["usuario_id"] ?? 0);

$usuario["telefone"] = (string)($usuario["telefone"] ?? "");
$usuario["tipo_usuario"] = (string)$usuario["tipo_usuario"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    validar_csrf();

    $usuario["nome"] = trim((string)($_POST["nome"] ?? ""));
    $usuario["email"] = mb_strtolower(
        trim((string)($_POST["email"] ?? "")),
        "UTF-8"
    );
    $usuario["telefone"] = trim((string)($_POST["telefone"] ?? ""));
    $usuario["tipo_usuario"] = $_POST["tipo_usuario"] ?? "";
    $senha = $_POST["senha"] ?? "";
    $confirmar = $_POST["confirmar_senha"] ?? "";

    if (
        $usuario["nome"] === "" ||
        $usuario["email"] === ""
    ) {
        $erro = "Preencha os campos obrigatórios.";
    } elseif (
        mb_strlen($usuario["nome"]) > 100 ||
        strlen($usuario["email"]) > 100 ||
        strlen($usuario["telefone"]) > 20
    ) {
        $erro = "Um dos campos ultrapassa o tamanho permitido.";
    } elseif (!filter_var($usuario["email"], FILTER_VALIDATE_EMAIL)) {
        $erro = "Informe um e-mail válido.";
    } elseif (
        !in_array($usuario["tipo_usuario"], ["0", "1"], true)
    ) {
        $erro = "Tipo de usuário inválido.";
    } elseif (!is_string($senha) || !is_string($confirmar)) {
        $erro = "Senha inválida.";
    } elseif ($senha !== "" && strlen($senha) < 8) {
        $erro = "A nova senha deve ter pelo menos 8 caracteres.";
    } elseif ($senha !== $confirmar) {
        $erro = "As senhas não coincidem.";
    } elseif (
        $proprioUsuario &&
        $usuario["tipo_usuario"] !== "1"
    ) {
        $erro = "Você não pode remover seu próprio acesso de administrador.";
    }

    if ($erro === "") {
        try {
            $tipo = (int)$usuario["tipo_usuario"];
            $telefone = $usuario["telefone"] !== ""
                ? $usuario["telefone"]
                : null;

            if ($senha === "") {
                $stmt = $conexao->prepare(
                    "UPDATE usuarios
                     SET nome = ?, email = ?, telefone = ?,
                         tipo_usuario = ?
                     WHERE id = ?"
                );

                $stmt->bind_param(
                    "sssii",
                    $usuario["nome"],
                    $usuario["email"],
                    $telefone,
                    $tipo,
                    $id
                );
            } else {
                $hash = password_hash($senha, PASSWORD_DEFAULT);

                $stmt = $conexao->prepare(
                    "UPDATE usuarios
                     SET nome = ?, email = ?, telefone = ?,
                         tipo_usuario = ?, senha = ?
                     WHERE id = ?"
                );

                $stmt->bind_param(
                    "sssisi",
                    $usuario["nome"],
                    $usuario["email"],
                    $telefone,
                    $tipo,
                    $hash,
                    $id
                );
            }

            $stmt->execute();
            $stmt->close();

            if ($proprioUsuario) {
                $_SESSION["nome"] = $usuario["nome"];
                $_SESSION["email"] = $usuario["email"];
                $_SESSION["tipo_usuario"] = $tipo;
            }

            $_SESSION["mensagem_sucesso"] =
                "Usuário atualizado com sucesso!";

            header("Location: listar_cadastro.php");
            exit;

        } catch (mysqli_sql_exception $e) {
            error_log($e->getMessage());

            $erro = (int)$e->getCode() === 1062
                ? "Já existe um usuário com este e-mail."
                : "Não foi possível salvar as alterações.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuário - Pampa Serra</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/style.css">

    <style>
        body {
            background: #f3f3f3;
            min-height: 100vh;
        }

        .topo {
            background: #174eae;
            color: white;
            padding: 25px;
        }

        .painel {
            background: white;
            max-width: 750px;
            margin: 35px auto;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,.08);
        }

        .titulo {
            color: #174eae;
            font-weight: 800;
        }
    </style>
</head>

<body>

<header class="topo">
    <div class="container d-flex justify-content-between align-items-center">
        <h1>Editar Usuário</h1>
        <a href="listar_cadastro.php" class="btn btn-light">
            Voltar
        </a>
    </div>
</header>

<main class="container">

    <section class="painel">

        <h2 class="titulo mb-4">
            <?= h($usuario["nome"]) ?>
        </h2>

        <?php if ($proprioUsuario): ?>
            <div class="alert alert-info">
                Você está editando sua própria conta.
            </div>
        <?php endif; ?>

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
                    value="<?= h($usuario["nome"]) ?>"
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
                    value="<?= h($usuario["email"]) ?>"
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
                    value="<?= h($usuario["telefone"]) ?>"
                >
            </div>

            <div class="mb-3">
                <label for="tipo_usuario" class="form-label">
                    Tipo de Usuário
                </label>

                <select
                    name="tipo_usuario"
                    id="tipo_usuario"
                    class="form-select"
                    <?= $proprioUsuario ? "disabled" : "" ?>
                >
                    <option value="0" <?= $usuario["tipo_usuario"] === "0" ? "selected" : "" ?>>
                        Usuário
                    </option>
                    <option value="1" <?= $usuario["tipo_usuario"] === "1" ? "selected" : "" ?>>
                        Administrador
                    </option>
                </select>

                <?php if ($proprioUsuario): ?>
                    <input type="hidden" name="tipo_usuario" value="1">
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="senha" class="form-label">
                    Nova senha
                </label>
                <input
                    type="password"
                    name="senha"
                    id="senha"
                    class="form-control"
                    minlength="8"
                    autocomplete="new-password"
                >
                <small class="text-muted">
                    Deixe vazio para manter a senha atual.
                </small>
            </div>

            <div class="mb-4">
                <label for="confirmar_senha" class="form-label">
                    Confirmar nova senha
                </label>
                <input
                    type="password"
                    name="confirmar_senha"
                    id="confirmar_senha"
                    class="form-control"
                    autocomplete="new-password"
                >
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary">
                    Salvar Alterações
                </button>

                <a href="listar_cadastro.php" class="btn btn-secondary">
                    Cancelar
                </a>
            </div>

        </form>

    </section>

</main>

</body>
</html>
