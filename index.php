<?php


$pagina = (
    isset($_GET["pagina"]) &&
    $_GET["pagina"] === "login"
) ? "login" : "landing";


$erro = "";



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

                .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 16px 27px;
            border-radius: 11px;
            font-size: 15px;
            font-weight: 800;
            transition: .2s;
        }


        .btn-primary {
            color: white;
            background: #2876f0;
        }


        .btn-primary:hover {
            background: #4b91ff;
            transform: translateY(-2px);
        }


        .btn-outline {
            border: 1px solid #8ba9d2;
            color: white;
        }


        .btn-outline:hover {
            background: #193f73;
        }


        .hero-image {
            position: relative;
            padding: 12px;
            border-radius: 23px;
            border: 1px solid #5578a5;
            background: rgba(255,255,255,.08);
            box-shadow: 0 25px 55px rgba(0,0,0,.3);
        }


        .hero-image img {
            display: block;
            width: 100%;
            height: 360px;
            object-fit: cover;
            border-radius: 14px;
        }


        .image-badge {
            position: absolute;
            left: 27px;
            bottom: 27px;
            padding: 12px 18px;
            background: #071b38;
            border-radius: 10px;
            color: white;
            font-size: 13px;
            font-weight: 700;
        }


        /* SEÇÕES */


        .section {
            padding: 95px 0;
        }


        .section-heading {
            text-align: center;
            max-width: 700px;
            margin: 0 auto 50px;
        }


        .section-heading span {
            color: #2366db;
            font-weight: 800;
            font-size: 12px;
            letter-spacing: 2px;
        }


        .section-heading h2 {
            font-size: clamp(30px, 4vw, 44px);
            margin: 15px 0;
        }


        .section-heading p {
            color: var(--cinza);
            font-size: 17px;
            line-height: 1.7;
        }


        .sobre {
            background: white;
        }


        .sobre-grid {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 60px;
            align-items: center;
        }


        .sobre-text h3 {
            font-size: 29px;
            margin: 0 0 22px;
        }


        .sobre-text p {
            color: #52647e;
            line-height: 1.9;
            margin-bottom: 18px;
        }


        .sobre-card {
            background: linear-gradient(140deg,#0b2e60,#164c9c);
            color: white;
            padding: 42px;
            border-radius: 22px;
        }


        .sobre-card h3 {
            font-size: 25px;
        }


        .sobre-card p {
            line-height: 1.8;
            color: #d7e5ff;
        }


        .funcionalidades {
            background: #f3f7ff;
        }


        .cards {
            display: grid;
            grid-template-columns: repeat(3,1fr);
            gap: 22px;
        }


        .card {
            background: white;
            border: 1px solid #e2eaf6;
            border-radius: 19px;
            padding: 30px;
            transition: .2s;
        }


        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(12,45,90,.1);
        }


        .card-icon {
            width: 57px;
            height: 57px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eaf2ff;
            border-radius: 14px;
            font-size: 27px;
            margin-bottom: 20px;
        }


        .card h3 {
            margin: 0 0 12px;
            font-size: 19px;
        }


        .card p {
            color: var(--cinza);
            font-size: 14px;
            line-height: 1.8;
            margin: 0;
        }


        /* CHAMADA FINAL */


        .cta {
            text-align: center;
            padding: 90px 20px;
            background: linear-gradient(130deg,#071b38,#134582);
            color: white;
        }


        .cta h2 {
            font-size: clamp(30px,4vw,44px);
            max-width: 750px;
            margin: 0 auto 20px;
        }


        .cta p {
            color: #c8dbf8;
            margin-bottom: 30px;
        }


        footer {
            background: #06152c;
            color: #9eb2cf;
            text-align: center;
            padding: 26px 15px;
            font-size: 13px;
        }


        /* TELA DE LOGIN */


        body.login-page {
            min-height: 100vh;
            background: #d0d4db;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 25px 15px;
        }


        .voltar {
            width: 820px;
            max-width: 95%;
            margin-bottom: 18px;
            color: #123d79;
            font-weight: 700;
        }


        #card-login {
            display: flex;
            width: 820px;
            max-width: 95%;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 12px 40px rgba(0,0,0,.18);
        }


        #lado-esquerdo {
            width: 35%;
            min-height: 400px;
            background: #1a5fcc;
            display: flex;
            align-items: center;
            justify-content: center;
        }


        #logo-login {
            width: 80%;
            max-width: 230px;
        }


        #lado-direito {
            width: 65%;
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
            margin: 0 0 35px;
            color: black;
        }


        .campo {
            margin-bottom: 19px;
        }


        .campo label {
            display: block;
            font-weight: 700;
            font-size: 13px;
            margin-bottom: 7px;
            color: #222;
        }


        .campo input {
            display: block;
            width: 100%;
            height: 38px;
            border: none;
            border-radius: 9px;
            padding: 10px 14px;
            background: #e5edfc;
            font: inherit;
            font-size: 14px;
        }


        .campo input:focus {
            outline: 2px solid #1a5fcc;
        }


        #btn-entrar {
            display: block;
            width: 100%;
            margin-top: 25px;
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


        .erro-login {
            background: #ffe3e3;
            color: #8b1717;
            padding: 12px;
            border-radius: 9px;
            margin-bottom: 20px;
        }


        /* RESPONSIVIDADE */


        @media(max-width:900px) {
            .hero-grid,
            .sobre-grid {
                grid-template-columns: 1fr;
            }


            .hero-image {
                max-width: 600px;
                margin: auto;
            }


            .cards {
                grid-template-columns: repeat(2,1fr);
            }
        }


        @media(max-width:650px) {
            .navbar {
                flex-direction: column;
                padding: 15px 0;
            }


            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
                gap: 15px;
            }


            .hero {
                padding: 65px 0;
            }


            .hero h1 {
                font-size: 38px;
            }


            .buttons {
                flex-direction: column;
            }


            .hero-image img {
                height: 240px;
            }


            .cards {
                grid-template-columns: 1fr;
            }


            .section {
                padding: 65px 0;
            }


            #card-login {
                flex-direction: column;
            }


            #lado-esquerdo {
                width: 100%;
                min-height: 170px;
                padding: 20px;
            }


            #logo-login {
                max-width: 150px;
            }


            #lado-direito {
                width: 100%;
            }


            #form-area {
                padding: 30px 25px;
            }
        }


        a:focus-visible,
        button:focus-visible {
            outline: 3px solid #fbbf24;
            outline-offset: 3px;
        }
    </style>


</head>


<body class="<?= $pagina === "login" ? "login-page" : "landing-page" ?>">


<?php if ($pagina === "landing"): ?>



    <!-- LANDING PAGE PRINCIPAL -->

       <header class="header">
        <nav class="navbar container">


            <a href="index.php" class="logo">
                <img src="assets/img/logo.png" alt="Logo Pampa-Serra">
                <span>Pampa-Serra</span>
            </a>


            <div class="nav-links">
                <a href="#inicio">Início</a>
                <a href="#sobre">Sobre</a>
                <a href="#funcionalidades">Funcionalidades</a>


                <a href="index.php?pagina=login" class="btn-nav">
                    Acessar sistema
                </a>
            </div>


        </nav>
    </header>


    <main>


        <section class="hero" id="inicio">


            <div class="container hero-grid">


                <div>


                    <span class="tag">
                        TECNOLOGIA • INOVAÇÃO • IoT
                    </span>


                    <h1>
                        O futuro do
                        <span>monitoramento ferroviário</span>
                        começa aqui.
                    </h1>


                    <p>
                        Conheça o Pampa-Serra, um projeto que une
                        sensores, conectividade e uma interface
                        inteligente para acompanhar informações
                        ferroviárias com mais clareza e eficiência.
                    </p>


                    <div class="buttons">


                        <a href="index.php?pagina=login" class="btn btn-primary">
                            Acessar sistema →
                        </a>


                        <a href="#sobre" class="btn btn-outline">
                            Conhecer o projeto
                        </a>


                    </div>


                </div>


                <div class="hero-image">


                    <img
                        src="assets/img/trem.png.jpeg"
                        alt="Trem do projeto Pampa-Serra"
                    >


                    <div class="image-badge">
                        Tecnologia ferroviária
                    </div>


                </div>


            </div>


        </section>


        <!-- SOBRE -->


        <section class="section sobre" id="sobre">


            <div class="container">


                <div class="section-heading">


                    <span>CONHEÇA O PROJETO</span>


                    <h2>O que é o Pampa-Serra?</h2>


                    <p>
                        Um projeto de tecnologia que busca transformar
                        o acompanhamento das operações ferroviárias.
                    </p>


                </div>


                <div class="sobre-grid">


                    <div class="sobre-text">


                        <h3>
                            Monitoramento inteligente através
                            da Internet das Coisas.
                        </h3>


                        <p>
                            O Pampa-Serra é um sistema desenvolvido
                            com foco em tecnologia, automação e
                            monitoramento ferroviário.
                        </p>


                        <p>
                            O projeto utiliza conceitos de IoT
                            para integrar sensores, conectividade
                            e ferramentas de visualização de dados.
                        </p>


                        <p>
                            Nosso objetivo é facilitar o
                            acompanhamento de informações
                            sobre trens, rotas, sensores
                            e condições operacionais.
                        </p>


                    </div>


                    <div class="sobre-card">


                        


                        <h3>Nossa missão</h3>


                        <p>
                            Desenvolver uma solução inteligente
                            para organizar informações ferroviárias
                            e apoiar o monitoramento das operações
                            por meio da tecnologia.
                        </p>


                    </div>


                </div>


            </div>


        </section>


        <!-- FUNCIONALIDADES -->


        <section class="section funcionalidades" id="funcionalidades">


            <div class="container">


                <div class="section-heading">


                    <span>NOSSA TECNOLOGIA</span>


                    <h2>Funcionalidades do sistema</h2>


                    <p>
                        Conheça os recursos que fazem parte
                        da proposta do Pampa-Serra.
                    </p>


                </div>


                <div class="cards">


                    <article class="card">


                        <div class="card-icon">📡</div>


                        <h3>Monitoramento IoT</h3>


                        <p>
                            Integração de sensores para
                            acompanhamento de informações
                            ferroviárias.
                        </p>


                    </article>


                    <article class="card">


                        <div class="card-icon">📍</div>


                        <h3>Localização dos trens</h3>


                        <p>
                            Visualização de informações
                            relacionadas à localização
                            e ao deslocamento dos trens.
                        </p>


                    </article>


                    <article class="card">


                        <div class="card-icon">⚠️</div>


                        <h3>Alertas operacionais</h3>


                        <p>
                            Identificação de condições
                            normais, alertas e possíveis
                            falhas nas operações.
                        </p>


                    </article>


                    <article class="card">


                        <div class="card-icon">📊</div>


                        <h3>Relatórios</h3>


                        <p>
                            Organização de dados para
                            análise e acompanhamento
                            do sistema ferroviário.
                        </p>


                    </article>


                    <article class="card">


                        <div class="card-icon">🛤️</div>


                        <h3>Gestão de rotas</h3>


                        <p>
                            Cadastro e gerenciamento
                            de rotas utilizadas pelo
                            sistema.
                        </p>


                    </article>


                    <article class="card">


                        <div class="card-icon">🔒</div>


                        <h3>Controle de acesso</h3>


                        <p>
                            Login de usuários cadastrados
                            com controle de acesso
                            ao ambiente administrativo.
                        </p>


                    </article>


                </div>


            </div>


        </section>


        <!-- BOTÃO FINAL -->


        <section class="cta">


            <h2>
                Pronto para conhecer o Pampa-Serra?
            </h2>


            <p>
                Entre na plataforma e conheça
                nosso ambiente de monitoramento ferroviário.
            </p>


            <a href="index.php?pagina=login" class="btn btn-primary">
                Entrar no sistema →
            </a>


        </section>


    </main>


    <footer>


        © <?= date("Y") ?> Pampa-Serra —
        Monitoramento Ferroviário Inteligente


    </footer>

    <?php else: ?>


    <!-- LOGIN: SÓ APARECE AO CLICAR NO BOTÃO -->


    <a href="index.php" class="voltar">
        ← Voltar para a página inicial
    </a>


    <main id="card-login">


        <div id="lado-esquerdo">


            <img
                src="assets/img/logo.png"
                id="logo-login"
                alt="Logo Pampa-Serra"
            >


        </div>


        <div id="lado-direito">


            <div id="form-area">


                <h1 id="titulo">LOGIN</h1>


                <?php if ($erro !== ""): ?>


                    <div class="erro-login" role="alert">
                        <?= h($erro) ?>
                    </div>


                <?php endif; ?>


                <form
                    method="POST"
                    action="index.php?pagina=login"
                >


                    <?= campo_csrf() ?>


                    <div class="campo">


                        <label for="email">
                            Email
                        </label>


                        <input
                            type="email"
                            name="email"
                            id="email"
                            maxlength="100"
                            autocomplete="username"
                            required
                        >


                    </div>


                    <div class="campo">


                        <label for="senha">
                            Senha
                        </label>


                        <input
                            type="password"
                            name="senha"
                            id="senha"
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


    </main>


<?php endif; ?>


</body>
</html>