
<?php
session_start();
require_once "conexao.php";

if (!isset($_SESSION["id_usuario"])) {
    header("Location: index.php");
    exit;
}

if (($_SESSION["nivel_acesso"] ?? "") !== "Administrador") {
    http_response_code(403);
    exit("Acesso negado.");
}

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    http_response_code(400);
    exit("Identificador inválido.");
}

$stmt = $conexao->prepare(
    "SELECT id_trem, codigo, modelo, status, id_usuario
     FROM trens WHERE id_trem = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$trem = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$trem) {
    http_response_code(404);
    exit("Trem não encontrado.");
}

$codigo = $trem["codigo"];
$modelo = $trem["modelo"];
$status = $trem["status"];
$id_usuario = (int)$trem["id_usuario"];
$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $codigo = trim($_POST["codigo"] ?? "");
    $modelo = trim($_POST["modelo"] ?? "");
    $status = $_POST["status"] ?? "";
    $id_usuario = filter_var(
        $_POST["id_usuario"] ?? "",
        FILTER_VALIDATE_INT
    );

    if (
        $codigo === "" ||
        strlen($codigo) > 50 ||
        $modelo === "" ||
        strlen($modelo) > 100 ||
        !in_array($status, ["Normal", "Alerta", "Falha"], true) ||
        !$id_usuario ||
        $id_usuario < 1
    ) {
        $erro = "Preencha todos os campos corretamente.";
    } else {
        $check = $conexao->prepare(
            "SELECT id FROM usuarios WHERE id = ?"
        );
        $check->bind_param("i", $id_usuario);
        $check->execute();
        $usuarioValido = $check->get_result()->num_rows > 0;
        $check->close();

        if (!$usuarioValido) {
            $erro = "O usuário responsável não é válido.";
        } else {
            $update = $conexao->prepare(
                "UPDATE trens
                 SET codigo = ?, modelo = ?, status = ?, id_usuario = ?
                 WHERE id_trem = ?"
            );

            $update->bind_param(
                "sssii",
                $codigo,
                $modelo,
                $status,
                $id_usuario,
                $id
            );

            try {
                $update->execute();
                $update->close();
                header("Location: trens.php?sucesso=1");
                exit;
            } catch (mysqli_sql_exception $e) {
                $update->close();
                $erro = "Erro ao atualizar. Verifique se o código já existe.";
            }
        }
    }
}

$usuarios = $conexao->query(
    "SELECT id, nome FROM usuarios ORDER BY nome"
);

if (!$usuarios) {
    die("Erro ao carregar usuários.");
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Trem - Pampa Serra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <h1>Editar trem</h1>

    <?php if ($erro !== ""): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($erro) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="codigo" class="form-label">Código</label>
            <input type="text" id="codigo" name="codigo"
                   class="form-control" maxlength="50" required
                   value="<?= htmlspecialchars($codigo) ?>">
        </div>

        <div class="mb-3">
            <label for="modelo" class="form-label">Modelo</label>
            <input type="text" id="modelo" name="modelo"
                   class="form-control" maxlength="100" required
                   value="<?= htmlspecialchars($modelo) ?>">
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select id="status" name="status" class="form-select" required>
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
                Usuário responsável
            </label>
            <select id="id_usuario" name="id_usuario"
                    class="form-select" required>
                <?php while ($usuario = $usuarios->fetch_assoc()): ?>
                    <option value="<?= (int)$usuario["id"] ?>"
                        <?= $id_usuario == $usuario["id"]
                            ? "selected" : "" ?>>
                        <?= htmlspecialchars($usuario["nome"]) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Salvar alterações</button>
        <a href="trens.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

</body>
</html>
