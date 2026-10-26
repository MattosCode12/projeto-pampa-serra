
<?php
require_once __DIR__ . "/verificar_acesso.php";
require_once __DIR__ . "/../conexao.php";

exigir_admin();

$busca = $_GET["busca"] ?? "";
$busca = is_string($busca) ? trim($busca) : "";

$mensagem = $_SESSION["mensagem_sucesso"] ?? "";
$erro = $_SESSION["mensagem_erro"] ?? "";

unset(
    $_SESSION["mensagem_sucesso"],
    $_SESSION["mensagem_erro"]
);

if ($busca !== "") {
    $stmt = $conexao->prepare(
        "SELECT id, nome, email, telefone, tipo_usuario
         FROM usuarios
         WHERE nome LIKE ? OR email LIKE ?
         ORDER BY id DESC"
    );

    $termo = "%" . $busca . "%";
    $stmt->bind_param("ss", $termo, $termo);
    $stmt->execute();
    $resultado = $stmt->get_result();
} else {
    $resultado = $conexao->query(
        "SELECT id, nome, email, telefone, tipo_usuario
         FROM usuarios
         ORDER BY id DESC"
    );
}

$idLogado = (int)($_SESSION["usuario_id"] ?? 0);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Usuários Cadastrados - Pampa Serra</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="../style/style.css">

    <style>
        .acoes-usuarios {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .acoes-usuarios form {
            margin: 0;
        }

        .tabela-usuarios th,
        .tabela-usuarios td {
            vertical-align: middle;
        }

        .tabela-usuarios .btn {
            white-space: nowrap;
        }
    </style>
</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="d-flex justify-content-between mb-4">

        <form method="GET" class="input-group">

            <input
                type="text"
                name="busca"
                class="form-control"
                placeholder="Buscar usuário"
                value="<?= h($busca) ?>"
            >

            <button type="submit" class="btn btn-dark">
                Buscar
            </button>

        </form>

        <a href="home.php" class="btn btn-dark ms-3">
            Voltar
        </a>

    </div>

    <div class="card p-4 shadow-sm">

        <img
            src="../assets/img/logo.png"
            class="mx-auto d-block mb-4"
            alt="Logo Pampa Serra"
            style="width:120px;"
        >

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">

            <h3>Usuários cadastrados</h3>

            <a href="cadastro_usuarios.php" class="btn btn-primary">
                Cadastrar Usuário
            </a>

        </div>

        <?php if ($mensagem !== ""): ?>
            <div class="alert alert-success">
                <?= h($mensagem) ?>
            </div>
        <?php endif; ?>

        <?php if ($erro !== ""): ?>
            <div class="alert alert-danger">
                <?= h($erro) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET["sucesso"])): ?>
            <div class="alert alert-success">
                Usuário excluído com sucesso!
            </div>
        <?php endif; ?>

        <div class="table-responsive">

            <table class="table table-hover align-middle tabela-usuarios">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Telefone</th>
                        <th>Tipo</th>
                        <th>Ações</th>
                    </tr>
                </thead>

                <tbody>

                <?php if ($resultado && $resultado->num_rows > 0): ?>

                    <?php while ($usuario = $resultado->fetch_assoc()): ?>

                        <?php
                        $idUsuario = (int)$usuario["id"];
                        $administrador =
                            (int)$usuario["tipo_usuario"] === 1;
                        $proprioUsuario = $idUsuario === $idLogado;
                        ?>

                        <tr>

                            <td>
                                <?= $idUsuario ?>
                            </td>

                            <td>
                                <?= h($usuario["nome"]) ?>
                            </td>

                            <td>
                                <?= h($usuario["email"]) ?>
                            </td>

                            <td>
                                <?= h($usuario["telefone"] ?? "") ?>
                            </td>

                            <td>
                                <?php if ($administrador): ?>
                                    <span class="badge bg-primary">
                                        Administrador
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">
                                        Usuário
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td>

                                <div class="acoes-usuarios">

                                    <a
                                        href="editar_usuario.php?id=<?= $idUsuario ?>"
                                        class="btn btn-warning btn-sm">
                                        Editar
                                    </a>

                                    <?php if ($administrador): ?>

                                        <span class="badge bg-secondary">
                                            Protegido
                                        </span>

                                    <?php elseif ($proprioUsuario): ?>

                                        <span class="badge bg-secondary">
                                            Conta atual
                                        </span>

                                    <?php else: ?>

                                        <form
                                            method="POST"
                                            action="excluir_usuario.php"
                                            onsubmit="return confirm('Deseja realmente excluir este usuário?');"
                                        >

                                            <?= campo_csrf() ?>

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?= $idUsuario ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn btn-danger btn-sm">
                                                Excluir
                                            </button>

                                        </form>

                                    <?php endif; ?>

                                </div>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="6" class="text-center">
                            Nenhum usuário encontrado.
                        </td>
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
