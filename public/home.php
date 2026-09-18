<?php

session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: index.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Home - Pampa Serra</title>

    <link 
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" 
        rel="stylesheet"
    >

    <link rel="stylesheet" href="style/style.css">

</head>

<body class="home-page">

    <nav class="navbar navbar-expand-lg navbar-custom">

        <div class="container-fluid">

            <a class="navbar-brand" href="#">
                <img 
                    src="assets/img/Logo.png" 
                    alt="Logo" 
                    id="logo-navbar"
                >
            </a>

            <button 
                class="navbar-toggler" 
                type="button" 
                data-bs-toggle="collapse" 
                data-bs-target="#navbarNav"
            >

                <span class="navbar-toggler-icon"></span>

            </button>

            <div 
                class="collapse navbar-collapse justify-content-between" 
                id="navbarNav"
            >

                <form class="d-flex mx-auto search-area">

                    <input
                        class="form-control search-input"
                        type="search"
                        placeholder="Buscar"
                    >

                    <button 
                        class="btn search-btn" 
                        type="submit"
                    >
                        Buscar
                    </button>

                </form>

                <div class="d-flex align-items-center gap-4">

                    <a 
                        href="logout.php" 
                        class="btn btn-sair"
                    >
                        Sair
                    </a>

                    <div class="admin-area">

                        <span>
                            <?= htmlspecialchars($_SESSION["nome"]) ?>
                        </span>

                    </div>

                </div>

            </div>

        </div>

    </nav>

    <main class="home-container container-fluid">

        <div class="d-flex flex-column align-items-center gap-4">

            <a 
                href="rotas.php" 
                class="btn btn-home-option"
            >
                Rotas
            </a>

            <a 
                href="sensores.php" 
                class="btn btn-home-option"
            >
                Sensores
            </a>

            <a 
                href="relatorios.php" 
                class="btn btn-home-option"
            >
                Relatórios
            </a>

            <a 
                href="cadastro.php" 
                class="btn btn-home-option"
            >
                Cadastrar
            </a>

        </div>

    </main>

    <script src="script/script.js"></script>

    <script 
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    ></script>

</body>

</html>