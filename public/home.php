<?php
require_once __DIR__ . "/verificar_acesso.php";

exigir_login();

$nome = $_SESSION["nome"] ?? "Usuário";
$tipo_usuario = (int)($_SESSION["tipo_usuario"] ?? 0);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Pampa Serra</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/style.css">

    <style>
        body {
            margin: 0;
            background: #f3f3f3;
            min-height: 100vh;
        }

        .cabecalho {
            width: 100%;
            min-height: 138px;
            background: #174eae;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 35px;
            gap: 20px;
        }

        .logo-home {
            width: 90px;
            height: auto;
        }

        .busca-home {
            display: flex;
            width: 415px;
            max-width: 100%;
        }

        .busca-home input {
            border-radius: 14px 0 0 14px;
            background: #e5e7eb;
            border: none;
        }

        .busca-home button {
            border-radius: 0 14px 14px 0;
            background: white;
            color: black;
            border: none;
            font-weight: bold;
        }

        .usuario-home {
            display: flex;
            align-items: center;
            gap: 22px;
            color: white;
            font-size: 28px;
            font-weight: bold;
        }

        .btn-sair {
            background: #123a85;
            color: white;
            text-decoration: none;
            border-radius: 14px;
            padding: 9px 33px;
            font-size: 14px;
        }

        .btn-sair:hover {
            background: #0c2b65;
            color: white;
        }

        .menu-home {
            max-width: 535px;
            margin: 37px auto;
            display: flex;
            flex-direction: column;
            gap: 22px;
            padding: 0 15px;
        }

        .botao-menu {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            min-height: 90px;
            border-radius: 12px;
            background: #0755bc;
            color: white;
            text-decoration: none;
            font-size: 36px;
            font-weight: 900;
            transition: background 0.2s, transform 0.2s;
        }

        .botao-menu:hover {
            background: #08469a;
            color: white;
            transform: scale(1.02);
        }

        @media (max-width: 768px) {
            .cabecalho {
                flex-wrap: wrap;
                justify-content: center;
                padding: 18px;
            }

            .busca-home {
                order: 3;
                width: 100%;
            }

            .usuario-home {
                font-size: 20px;
            }

            .botao-menu {
                min-height: 75px;
                font-size: 28px;
            }
        }
    </style>
</head>

<body>

<header class="cabecalho">

    <img
        src="../assets/img/logo.png"
        alt="Pampa Serra"
        class="logo-home"
    >

    <form class="busca-home" action="home.php" method="GET">
        <input
            type="text"
            name="busca"
            class="form-control"
            placeholder="Buscar"
            value="<?= h($_GET["busca"] ?? "") ?>"
        >

        <button type="submit" class="btn">
            Buscar
        </button>
    </form>

    <div class="usuario-home">
        <a href="logout.php" class="btn-sair">
            Sair
        </a>

        <span><?= h($nome) ?></span>
    </div>

</header>

<main class="menu-home">

    <a href="rotas.php" class="botao-menu">
        Rotas
    </a>

    <a href="sensores.php" class="botao-menu">
        Sensores
    </a>

    <a href="trens.php" class="botao-menu">
        Trens
    </a>

    <a href="relatorios.php" class="botao-menu">
        Relatórios
    </a>

    <?php if ($tipo_usuario === 1): ?>
        <a href="cadastro_usuarios.php" class="botao-menu">
            Cadastrar
        </a>
    <?php endif; ?>

</main>

</body>
</html>