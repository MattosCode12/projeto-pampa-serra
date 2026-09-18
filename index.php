<?php

session_start();

require_once "conexao.php";

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $senha = $_POST["senha"] ?? "";

    if (empty($email) || empty($senha)) {

        $erro = "Preencha todos os campos.";

    } else {

        $sql = "SELECT id_usuario, nome, email, senha 
                FROM usuario 
                WHERE email = ?";

        $stmt = $conexao->prepare($sql);

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 1) {

            $usuario = $resultado->fetch_assoc();

            if (password_verify($senha, $usuario["senha"])) {

                $_SESSION["id_usuario"] = $usuario["id_usuario"];
                $_SESSION["nome"] = $usuario["nome"];
                $_SESSION["email"] = $usuario["email"];

                header("Location: home.php");

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

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - Pampa Serra</title>

    <link 
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" 
        rel="stylesheet"
    >

    <link rel="stylesheet" href="style/style.css">

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

                <h2 id="titulo">LOGIN</h2>

                <?php if (!empty($erro)): ?>

                    <div class="alert alert-danger">

                        <?= htmlspecialchars($erro) ?>

                    </div>

                <?php endif; ?>

                <form 
                    id="formLogin" 
                    method="POST" 
                    action=""
                >

                    <div class="mb-3">

                        <label 
                            class="form-label" 
                            for="email"
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
                            class="form-label" 
                            for="senha"
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

    <script src="script/script.js"></script>

    <script 
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    ></script>

</body>

</html>