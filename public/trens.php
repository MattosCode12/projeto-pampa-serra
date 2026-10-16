<?php
session_start();
require_once "conexao.php";

if (!isset($_SESSION["id_usuario"])) {
    header("Location: index.php");
    exit;
}

$admin = isset($_SESSION["nivel_acesso"]) &&
         $_SESSION["nivel_acesso"] === "Administrador";

$sql = "SELECT t.id_trem, t.codigo, t.modelo, t.status,
               u.nome AS responsavel
        FROM trens t
        INNER JOIN usuarios u ON t.id_usuario = u.id
        ORDER BY t.id_trem DESC";

$resultado = $conexao->query($sql);

if (!$resultado) {
    die("Erro ao consultar trens.");
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trens - Pampa Serra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <h1>Trens cadastrados</h1>
    <p>Consulte os trens registrados no sistema.</p>

    <?php if (isset($_GET["sucesso"])): ?>
        <div class="alert alert-success">
            Operação realizada com sucesso!
        </div>
    <?php endif; ?>

    <?php if ($admin): ?>
        <a href="cadastrar_trem.php" class="btn btn-primary mb-3">
            Cadastrar trem
        </a>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-bordered table-striped bg-white">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Modelo</th>
                    <th>Status</th>
                    <th>Responsável</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($trem = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($trem["codigo"]) ?></td>
                    <td><?= htmlspecialchars($trem["modelo"]) ?></td>
                    <td><?= htmlspecialchars($trem["status"]) ?></td>
                    <td><?= htmlspecialchars($trem["responsavel"]) ?></td>
                    <td>
                        <a class="btn btn-info btn-sm"
                           href="visualizar_trem.php?id=<?= (int)$trem["id_trem"] ?>">
                            Ver
                        </a>

                        <?php if ($admin): ?>
                            <a class="btn btn-warning btn-sm"
                               href="editar_trem.php?id=<?= (int)$trem["id_trem"] ?>">
                                Editar
                            </a>

                            <form action="excluir_trem.php" method="POST"
                                  class="d-inline"
                                  onsubmit="return confirm('Deseja excluir este trem?')">
                                <input type="hidden" name="id_trem"
                                       value="<?= (int)$trem["id_trem"] ?>">
                                <button type="submit"
                                        class="btn btn-danger btn-sm">
                                    Excluir
                                </button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <a href="home.php" class="btn btn-secondary">Voltar</a>
</div>

</body>
</html>
