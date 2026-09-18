<?php

require_once "../conexao.php";

$sql = "SELECT id_usuario, nome, email, telefone, tipo_usuario
        FROM usuario
        ORDER BY nome ASC";

$resultado = $conexao->query($sql);

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Listar Cadastro</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="../style/style.css">

</head>

<body class="bg-light">

    <div class="container mt-4">

        <div class="input-group mb-3">

            <input
                type="text"
                id="buscar"
                class="form-control"
                placeholder="Buscar usuário"
            >

        </div>

        <a href="cadastro_usuarios.php" class="btn-voltar mb-4">
            Voltar
        </a>

        <div class="card shadow-sm p-4">

            <img
                src="../assets/img/logo.png"
                class="mx-auto d-block mb-4"
                alt="Logo Pampa Serra"
                style="width: 120px;"
            >

            <h2 class="text-center mb-4">
                Usuários cadastrados
            </h2>

            <div class="table-responsive">

                <table class="table table-bordered table-striped">

                    <thead>

                        <tr>

                            <th>ID</th>

                            <th>Nome</th>

                            <th>Email</th>

                            <th>Telefone</th>

                            <th>Tipo de Usuário</th>

                        </tr>

                    </thead>

                    <tbody id="tabelaUsuarios">

                        <?php

                        if ($resultado && $resultado->num_rows > 0) {

                            while ($usuario = $resultado->fetch_assoc()) {

                        ?>

                                <tr>

                                    <td>
                                        <?= htmlspecialchars($usuario["id_usuario"]) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($usuario["nome"]) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($usuario["email"]) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($usuario["telefone"]) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($usuario["tipo_usuario"]) ?>
                                    </td>

                                </tr>

                        <?php

                            }

                        } else {

                        ?>

                            <tr>

                                <td colspan="5" class="text-center">

                                    Nenhum usuário cadastrado.

                                </td>

                            </tr>

                        <?php

                        }

                        ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    <script src="../script/script.js"></script>

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js">
    </script>

</body>

</html>