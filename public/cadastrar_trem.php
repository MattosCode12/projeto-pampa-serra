<?php
session_start();
require_once "conexao.php";

if (!isset($_SESSION["id_usuario"])) {
    header("Location: index.php");
    exit;
}

if (($_SESSION["nivel_acesso"] ?? "") !== "Administrador") {
    http_response_code(403);
    exit("Acesso negado. Somente administradores podem cadastrar trens.");
}

$erro = "";
$codigo = "";
$modelo = "";
$status = "Normal";
$id_usuario = "";

$usuarios = $conexao->query(
    "SELECT id, nome FROM usuarios ORDER BY nome"
);

if (!$usuarios) {
    die("Erro ao carregar usuários.");
}

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
        $modelo === "" ||
        !in_array($status, ["Normal", "Alerta", "Falha"], true) ||
        !$id_usuario ||
        $id_usuario < 1
    ) {
        $erro = "Preencha os campos corretamente.";
    } else {
        $stmtUsuario = $conexao->prepare(
            "SELECT id FROM usuarios WHERE id = ?"
        );
        $stmtUsuario->bind_param("i", $id_usuario);
        $stmtUsuario->execute();
        $usuarioValido = $stmtUsuario->get_result()->num_rows > 0;
        $stmtUsuario->close();

        if (!$usuarioValido) {
            $erro = "Selecione um usuário válido.";
        } else {
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

            try {
                if ($stmt->execute()) {
                    $stmt->close();
                    header("Location: trens.php?sucesso=1");
                    exit;
                }

                $erro = "Não foi possível cadastrar o trem.";
            } catch (mysqli_sql_exception $e) {
                $erro = "Erro ao cadastrar. Verifique se o código já existe.";
            }

            if ($stmt->errno === 0 || $stmt->errno !== 0) {
                $stmt->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Trem - Pampa Serra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <h1>Cadastrar trem</h1>

    <?php if ($erro !== ""): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($erro) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="codigo" class="form-label">Código do trem</label>
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
                <option value="">Selecione um usuário</option>
                <?php while ($usuario = $usuarios->fetch_assoc()): ?>
                    <option value="<?= (int)$usuario["id"] ?>"
                        <?= (string)$id_usuario === (string)$usuario["id"]
                            ? "selected" : "" ?>>
                        <?= htmlspecialchars($usuario["nome"]) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Cadastrar</button>
        <a href="trens.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

</body>
</html>