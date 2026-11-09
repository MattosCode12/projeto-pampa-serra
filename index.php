<?php
// Pampa-Serra — Landing page + Login


$pagina = (
    isset($_GET["pagina"]) &&
    $_GET["pagina"] === "login"
) ? "login" : "landing";


$erro = "";


// A landing page não exige autenticação.
// Carrega sessão, segurança e banco somente no login.
if ($pagina === "login" || $_SERVER["REQUEST_METHOD"] === "POST") {


    $pagina = "login";


    require_once __DIR__ . "/public/verificar_acesso.php";
    require_once __DIR__ . "/conexao.php";


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
                            "UPDATE usuarios SET senha = ? WHERE id = ?"
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
}
?>
<!DOCTYPE html>
<html lang="pt-BR">


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">


    <title>
        <?= $pagina === "login"
            ? "Login | Pampa-Serra"
            : "Pampa-Serra | Monitoramento Ferroviário" ?>
    </title>


    <meta
        name="description"
        content="Pampa-Serra: tecnologia IoT para monitoramento ferroviário inteligente."
    >


    <style>
        :root {
            --azul: #1a5fcc;
            --azul-escuro: #071b38;
            --azul-claro: #60a5fa;
            --texto: #152c4b;
            --cinza: #64748b;
        }


        * {
            box-sizing: border-box;
        }


        html {
            scroll-behavior: smooth;
        }


        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: var(--texto);
        }


        a {
            text-decoration: none;
        }


        .container {
            width: 90%;
            max-width: 1180px;
            margin: auto;
        }


        /* CABEÇALHO */


        .header {
            background: #071b38;
            color: white;
        }


        .navbar {
            min-height: 88px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }


        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
            font-size: 22px;
            font-weight: 800;
        }


        .logo img {
            width: 58px;
            height: 58px;
            object-fit: contain;
        }


        .nav-links {
            display: flex;
            align-items: center;
            gap: 28px;
        }


        .nav-links a {
            color: #e2e8f0;
            font-weight: 600;
            font-size: 14px;
        }


        .nav-links .btn-nav {
            background: var(--azul);
            padding: 13px 24px;
            border-radius: 10px;
            color: white;
        }


        .nav-links a:hover {
            color: #8bbcff;
        }


        /* HERO */


        .hero {
            background:
                radial-gradient(
                    circle at 85% 20%,
                    #21529b 0%,
                    transparent 42%
                ),
                linear-gradient(
                    130deg,
                    #06182f,
                    #0b2a56
                );


            color: white;
            padding: 100px 0;
        }


        .hero-grid {
            display: grid;
            grid-template-columns: 1.1fr 1fr;
            align-items: center;
            gap: 60px;
        }


        .tag {
            display: inline-block;
            border: 1px solid #6592d0;
            color: #b4d2ff;
            padding: 9px 16px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 1.5px;
        }


        .hero h1 {
            font-size: clamp(38px, 5vw, 65px);
            line-height: 1.1;
            letter-spacing: -2px;
            margin: 28px 0;
        }


        .hero h1 span {
            color: #60a5fa;
        }


        .hero p {
            color: #cbdaf1;
            font-size: 17px;
            line-height: 1.9;
            margin-bottom: 32px;
        }


        .buttons {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
        }