<?php

require_once "../conexao.php";

$sql = "SELECT id_usuario, nome, email
        FROM usuario
        ORDER BY nome ASC";

$resultado = $conexao->query($sql);

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Listar Cadastro</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light">

    <div class="container mt-5">

        <div class="d-flex justify-content-between mb-4">

            <div class="input-group">

                <input
                    type="text"
                    class="form-control"
                    placeholder="Buscar"
                >

            </div>

            <a
                href="cadastro_usuarios.php"
                class="btn btn-dark"
            >
                Voltar
            </a>

        </div>

        <div class="card p-4 shadow-sm">

            <img
                src="../assets/img/logo.png"
                class="mx-auto d-block mb-4"
                alt="Logo Pampa Serra"
                style="width: 120px;"
            >

            <h2 class="text-center mb-4">
                Usuários cadastrados
            </h2>

            <?php

            if ($resultado && $resultado->num_rows > 0) {

                while ($usuario = $resultado->fetch_assoc()) {

            ?>

                    <div class="mb-3">

                        <label class="form-label">
                            Nome
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="<?= htmlspecialchars($usuario["nome"]) ?>"
                            readonly
                        >

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Email
                        </label>

                        <input
                            type="email"
                            class="form-control"
                            value="<?= htmlspecialchars($usuario["email"]) ?>"
                            readonly
                        >

                    </div>

                    <hr>

            <?php

                }

            } else {

            ?>

                <p class="text-center">
                    Nenhum usuário cadastrado.
                </p>

            <?php

            }

            ?>

        </div>

    </div>

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js">
    </script>

</body>

</html>