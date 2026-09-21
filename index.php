<?php

session_start();
require_once __DIR__ . "/conexao.php";

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $senha = $_POST["senha"] ?? "";

    if ($email === "" || $senha === "") {

        $erro = "Preencha todos os campos.";

    } else {

        $sql = "SELECT id, nome, email, senha
                FROM usuarios
                WHERE email = ?";

        $stmt = $conexao->prepare($sql);

        if (!$stmt) {
            die("Erro SQL: " . $conexao->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 1) {

            $usuario = $resultado->fetch_assoc();

            if ($senha === $usuario["senha"]) {

                $_SESSION["id_usuario"] = $usuario["id"];
                $_SESSION["nome"] = $usuario["nome"];
                $_SESSION["email"] = $usuario["email"];

                header(
                    "Location: /rafael_melchioretto/projeto-pampa-serra/public/home.php"
                );
                exit;

            } else {

                $erro = "E-mail ou senha incorretos.";
            }

        } else {

            $erro = "E-mail ou senha incorretos.";
        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Login - Pampa Serra</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="style/style.css"
    >

</head>

<body>

<div id="card-login">

    <div id="lado-esquerdo">

        <img
            src="assets/img/Logo.png"
            id="logo"
            alt="Logo Pampa Serra"
        >

    </div>

    <div id="lado-direito">

        <div id="form-area">

            <h2 id="titulo">
                LOGIN
            </h2>

            <?php if ($erro !== ""): ?>

                <div class="alert alert-danger">

                    <?= htmlspecialchars($erro) ?>

                </div>

            <?php endif; ?>

            <form method="POST" action="">

                <div class="mb-3">

                    <label
                        for="email"
                        class="form-label"
                    >
                        Email
                    </label>

                    <input
                        type="email"
                        name="email"
                        id="email"
                        class="form-control"
                        required
                    >

                </div>

                <div class="mb-3">

                    <label
                        for="senha"
                        class="form-label"
                    >
                        Senha
                    </label>

                    <input
                        type="password"
                        name="senha"
                        id="senha"
                        class="form-control"
                        required
                    >

                </div>

                <button
                    type="submit"
                    id="btn-entrar"
                >
                    Entrar
                </button>

            </form>

        </div>

    </div>

</div>

</body>

</html>