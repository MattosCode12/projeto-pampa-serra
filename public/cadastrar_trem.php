
<?php
require_once __DIR__ . "/verificar_acesso.php";
require_once __DIR__ . "/../conexao.php";

exigir_login();

if ((int)($_SESSION["tipo_usuario"] ?? 0) !== 1) {
    http_response_code(403);
    exit("Acesso negado. Somente administradores.");
}

if (empty($_SESSION["csrf_trens"])) {
    $_SESSION["csrf_trens"] = bin2hex(random_bytes(32));
}

$erro = "";
$codigo = "";
$modelo = "";
$status = "Normal";
$id_usuario = 0;

$usuarios = $conexao->query(
    "SELECT id, nome FROM usuarios ORDER BY nome"
)->fetch_all(MYSQLI_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $token = $_POST["csrf_trens"] ?? "";

    if (!is_string($token) ||
        !hash_equals($_SESSION["csrf_trens"], $token)) {
        http_response_code(403);
        exit("Solicitação inválida.");
    }

    $codigo = trim((string)($_POST["codigo"] ?? ""));
    $modelo = trim((string)($_POST["modelo"] ?? ""));
    $status = $_POST["status"] ?? "";
    $id_usuario = filter_input(
        INPUT_POST, "id_usuario", FILTER_VALIDATE_INT
    );

    $idsValidos = array_map(
        "intval",
        array_column($usuarios, "id")
    );

    if (
        $codigo === "" ||
        mb_strlen($codigo) > 50 ||
        $modelo === "" ||
        mb_strlen($modelo) > 100 ||
        !in_array($status, ["Normal", "Alerta", "Falha"], true) ||
        !in_array($id_usuario, $idsValidos, true)
    ) {
        $erro = "Preencha todos os campos corretamente.";
    } else {
        try {
            $stmt = $conexao->prepare(
                "INSERT INTO trens (codigo, modelo, status, id_usuario)
                 VALUES (?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "sssi",
                $codigo,
                $modelo,
                $status,
                $id_usuario
            );

            $stmt->execute();
            $stmt->close();

            header("Location: trens.php?sucesso=cadastrado");
            exit;

        } catch (mysqli_sql_exception $e) {
            error_log($e->getMessage());
            $erro = "Não foi possível cadastrar. Verifique se o código já existe.";
        }
    }
}

function trem_h($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cadastrar Trem - Pampa Serra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/style.css">
    <style>
        body { background: #f3f3f3; }
        .topo { background: #174eae; color: white; padding: 25px; }
        .painel { background: white; border-radius: 15px; padding: 30px; }
    </style>
</head>
<body>

<header class="topo">
    <div class="container">
        <h1>Cadastrar Trem</h1>
    </div>
</header>

<main class="container py-4">
    <div class="painel mx-auto" style="max-width: 700px;">

        <?php if ($erro !== ""): ?>
            <div class="alert alert-danger">
                <?= trem_h($erro) ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <input type="hidden"
                   name="csrf_trens"
                   value="<?= trem_h($_SESSION["csrf_trens"]) ?>">

            <div class="mb-3">
                <label for="codigo" class="form-label">Código do trem</label>
                <input type="text" name="codigo" id="codigo"
                       class="form-control" maxlength="50" required
                       value="<?= trem_h($codigo) ?>">
            </div>

            <div class="mb-3">
                <label for="modelo" class="form-label">Modelo</label>
                <input type="text" name="modelo" id="modelo"
                       class="form-control" maxlength="100" required
                       value="<?= trem_h($modelo) ?>">
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <?php foreach (["Normal", "Alerta", "Falha"] as $opcao): ?>
                        <option value="<?= $opcao ?>"
                            <?= $status === $opcao ? "selected" : "" ?>>
                            <?= $opcao ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="id_usuario" class="form-label">
                    Responsável
                </label>
                <select name="id_usuario" id="id_usuario"
                        class="form-select" required>
                    <option value="">Selecione um usuário</option>
                    <?php foreach ($usuarios as $usuario): ?>
                        <option value="<?= (int)$usuario["id"] ?>"
                            <?= $id_usuario == $usuario["id"]
                                ? "selected" : "" ?>>
                            <?= trem_h($usuario["nome"]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">
                Cadastrar Trem
            </button>

            <a href="trens.php" class="btn btn-secondary">
                Cancelar
            </a>

        </form>
    </div>
</main>

</body>
</html>
