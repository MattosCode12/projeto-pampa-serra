
<?php
require_once "o_arquivo_com_exigir_admin.php";

var_dump($_SESSION);
exit;

exigir_admin();

<?php
require_once __DIR__ . "/verificar_acesso.php";
require_once __DIR__ . "/../conexao.php";

exigir_admin();

$erro = "";
$mensagem = "";
$nome = "";
$email = "";
$telefone = "";
$tipo_usuario = "0";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    validar_csrf();

    $nome = trim($_POST["nome"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $telefone = trim($_POST["telefone"] ?? "");
    $senha = $_POST["senha"] ?? "";
    $tipo_usuario = $_POST["tipo_usuario"] ?? "0";

    if ($nome === "" || $email === "" || $senha === "") {
        $erro = "Preencha os campos obrigatórios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Informe um email válido.";
    } elseif (strlen($senha) < 8) {
        $erro = "A senha deve ter pelo menos 8 caracteres.";
    } elseif (!in_array($tipo_usuario, ["0", "1"], true)) {
        $erro = "Tipo de usuário inválido.";
    } else {
        $stmt = $conexao->prepare(
            "SELECT id FROM usuarios WHERE email = ? LIMIT 1"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $existe = $resultado->num_rows > 0;
        $stmt->close();

        if ($existe) {
            $erro = "Já existe um usuário com este email.";
        } else {
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
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
                $senhaHash,
                $telefone,
                $tipo
            );

            try {
                if ($stmt->execute()) {
                    $_SESSION["mensagem_sucesso"] =
                        "Usuário cadastrado com sucesso!";

                    header("Location: listar_cadastro.php");
                    exit;
                }

                $erro = "Erro ao cadastrar usuário.";
            } catch (mysqli_sql_exception $e) {
                error_log($e->getMessage());
                $erro = "Erro ao cadastrar usuário.";
            } finally {
                $stmt->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Usuário - Pampa Serra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/style.css">
</head>
<body class="bg-light">

<div class="container mt-5">

    <div class="d-flex justify-content-between mb-4">
        <div class="input-group">
            <input
                type="text"
                class="form-control"
                value="Cadastrar usuário"
                disabled
            >
        </div>

        <a href="home.php" class="btn btn-dark ms-3">Voltar</a>
    </div>

    <div class="card p-4 shadow-sm">

        <img
            src="../assets/img/logo.png"
            class="mx-auto d-block mb-4"
            alt="Logo Pampa Serra"
            style="width: 120px;"
        >

        <h3 class="text-center mb-4">Cadastrar Usuário</h3>

        <?php if ($mensagem): ?>
            <div class="alert alert-success">
                <?= h($mensagem) ?>
            </div>
        <?php endif; ?>

        <?php if ($erro): ?>
            <div class="alert alert-danger">
                <?= h($erro) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="cadastro_usuarios.php">

            <?= campo_csrf() ?>

            <div class="mb-3">
                <label for="nome" class="form-label">Nome</label>
                <input
                    type="text"
                    name="nome"
                    id="nome"
                    class="form-control"
                    value="<?= h($nome) ?>"
                    required
                >
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    class="form-control"
                    value="<?= h($email) ?>"
                    required
                >
            </div>

            <div class="mb-3">
                <label for="telefone" class="form-label">Telefone</label>
                <input
                    type="tel"
                    name="telefone"
                    id="telefone"
                    class="form-control"
                    value="<?= h($telefone) ?>"
                >
            </div>

            <div class="mb-3">
                <label for="senha" class="form-label">Senha</label>
                <input
                    type="password"
                    name="senha"
                    id="senha"
                    class="form-control"
                    minlength="8"
                    required
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

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    Cadastrar
                </button>

                <a href="listar_cadastro.php" class="btn btn-dark">
                    Ver Usuários
                </a>
            </div>

        </form>
    </div>
</div>

<script src="../script/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>