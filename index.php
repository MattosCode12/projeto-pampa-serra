<?php

require_once __DIR__ . "/public/verificar_acesso.php";
require_once __DIR__ . "/conexao.php";

$erro = "";

if (isset($_SESSION["usuario_id"])) {
    header("Location: public/home.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    validar_csrf();

    $email = $_POST["email"] ?? "";
    $senha = $_POST["senha"] ?? "";

    if (!is_string($email) || !is_string($senha)) {
        $erro = "Preencha todos os campos.";
    } else {

        $email = trim(mb_strtolower($email, "UTF-8"));

        if (
            !filter_var($email, FILTER_VALIDATE_EMAIL) ||
            $senha === ""
        ) {
            $erro = "Preencha todos os campos corretamente.";
        } else {

            $stmt = $conexao->prepare(
                "SELECT id, nome, email, senha, tipo_usuario
                 FROM usuarios
                 WHERE email = ?
                 LIMIT 1"
            );

            $stmt->bind_param("s", $email);
            $stmt->execute();

            $usuario = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            $autenticado = false;
            $senhaAntiga = false;

            if ($usuario) {

                if (password_verify($senha, $usuario["senha"])) {
                    $autenticado = true;
                } elseif (
                    password_get_info($usuario["senha"])["algoName"] === "unknown" &&
                    hash_equals($usuario["senha"], $senha)
                ) {
                    $autenticado = true;
                    $senhaAntiga = true;
                }

            } else {
                password_verify(
                    $senha,
                    '$2y$10$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG'
                );
            }

            if ($autenticado) {

                if (
                    $senhaAntiga ||
                    password_needs_rehash(
                        $usuario["senha"],
                        PASSWORD_DEFAULT
                    )
                ) {

                    $hash = password_hash(
                        $senha,
                        PASSWORD_DEFAULT
                    );

                    $id = (int)$usuario["id"];

                    $update = $conexao->prepare(
                        "UPDATE usuarios
                         SET senha = ?
                         WHERE id = ?"
                    );

                    $update->bind_param("si", $hash, $id);
                    $update->execute();
                    $update->close();
                }

                session_regenerate_id(true);

                $_SESSION["usuario_id"] = (int)$usuario["id"];
                $_SESSION["nome"] = $usuario["nome"];
                $_SESSION["email"] = $usuario["email"];
                $_SESSION["tipo_usuario"] = (int)$usuario["tipo_usuario"];

                unset($_SESSION["csrf"]);

                header("Location: public/home.php");
                exit;

            } else {
                $erro = "E-mail ou senha incorretos.";
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

    <title>Login - Pampa Serra</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="style/style.css">

    <style>
        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #d0d4db;
        }

        #card-login {
            display: flex;
            width: 820px;
            max-width: 95%;
            margin: 30px auto;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 12px 40px rgba(0,0,0,.18);
        }

        #lado-esquerdo {
            flex: 0 0 35%;
            min-height: 400px;
            background: #1a5fcc;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #logo {
            width: 80%;
            max-width: 230px;
        }

        #lado-direito {
            flex: 1;
            width: auto;
            background: #f3f3f3;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #form-area {
            width: 100%;
            padding: 40px 45px;
        }

        #titulo {
            text-align: center;
            font-size: 2.2rem;
            font-weight: 900;
            margin-bottom: 35px;
        }

        #btn-entrar {
            display: block;
            width: 100%;
            margin: 25px auto 0;
            padding: 12px;
            border: none;
            border-radius: 12px;
            background: #1a5fcc;
            color: white;
            font-size: 20px;
            font-weight: 800;
            cursor: pointer;
        }

        #btn-entrar:hover {
            background: #144ea8;
        }

        @media (max-width: 768px) {

            #card-login {
                flex-direction: column;
            }

            #lado-esquerdo {
                flex: none;
                min-height: 170px;
                padding: 20px;
            }

            #lado-direito {
                width: 100%;
            }

            #form-area {
                padding: 30px 25px;
            }
        }
    </style>
</head>

<body class="login-page">

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

            <?php if ($erro !== ""): ?>
                <div class="alert alert-danger" role="alert">
                    <?= h($erro) ?>
                </div>
            <?php endif; ?>

            <form method="POST">

                <?= campo_csrf() ?>

                <div class="mb-3">
                    <label class="form-label" for="email">
                        Email
                    </label>

                    <input
                        type="email"
                        name="email"
                        id="email"
                        class="form-control"
                        maxlength="100"
                        autocomplete="username"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label" for="senha">
                        Senha
                    </label>

                    <input
                        type="password"
                        name="senha"
                        id="senha"
                        class="form-control"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <button type="submit" id="btn-entrar">
                    Entrar
                </button>

            </form>

        </div>

    </div>

</div>

</body>
</html>