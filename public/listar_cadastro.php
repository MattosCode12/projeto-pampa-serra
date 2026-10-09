<?php
require_once 'verificar_acesso.php';
exigir_admin();

session_start();
require_once "../conexao.php";

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../index.php");
    exit;
}

$busca = trim($_GET["busca"] ?? "");

if ($busca !== "") {
    $sql = "SELECT id, nome, email, telefone, tipo_usuario FROM usuarios WHERE nome LIKE ? OR email LIKE ? ORDER BY id DESC";
    $stmt = $conexao->prepare($sql);
    $termo = "%".$busca."%";
    $stmt->bind_param("ss", $termo, $termo);
    $stmt->execute();
    $resultado = $stmt->get_result();
} else {
    $resultado = $conexao->query("SELECT id, nome, email, telefone, tipo_usuario FROM usuarios ORDER BY id DESC");
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários Cadastrados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/style.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="d-flex justify-content-between mb-4">
        <form method="GET" class="input-group">
            <input type="text" name="busca" class="form-control" placeholder="Buscar usuário" value="<?= htmlspecialchars($busca) ?>">
            <button type="submit" class="btn btn-dark">Buscar</button>
        </form>

        <a href="cadastro_usuarios.php" class="btn btn-dark ms-3">Voltar</a>
    </div>

    <div class="card p-4 shadow-sm">
        <img src="../assets/img/logo.png" class="mx-auto d-block mb-4" alt="Logo Pampa Serra" style="width: 120px;">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Usuários cadastrados</h3>
            <a href="cadastro_usuarios.php" class="btn btn-primary">Cadastrar Usuário</a>
        </div>

        <?php if (isset($_GET["sucesso"])): ?>
            <div class="alert alert-success">Usuário excluído com sucesso.</div>
        <?php endif; ?>

        <?php if (isset($_GET["erro"])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_GET["erro"]) ?></div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Telefone</th>
                        <th>Tipo</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>

                <?php if ($resultado && $resultado->num_rows > 0): ?>
                    <?php while ($usuario = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($usuario["id"]) ?></td>
                            <td><?= htmlspecialchars($usuario["nome"]) ?></td>
                            <td><?= htmlspecialchars($usuario["email"]) ?></td>
                            <td><?= htmlspecialchars($usuario["telefone"] ?? "") ?></td>
                            <td><?= htmlspecialchars($usuario["tipo_usuario"]) ?></td>
                            <td>
                                <?php if ($usuario["tipo_usuario"] !== "admin"): ?>
                                    <form action="excluir_usuario.php" method="POST" class="d-inline" onsubmit="return confirm('Deseja realmente excluir este usuário?');">
                                        <input type="hidden" name="id" value="<?= $usuario["id"] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
                                    </form>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Protegido</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">Nenhum usuário cadastrado.</td>
                    </tr>
                <?php endif; ?>

                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="../script/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>