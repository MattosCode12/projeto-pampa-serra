<?php

$rota = "Rota 3207";
$origem = "Porto Alegre";
$destino = "Gramado";
$parada = "Novo Hamburgo";
$velocidade = "39 Km/h";
$consumo = "3 L/km";

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Rotas - Pampa Serra</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
    >

    <link
        rel="stylesheet"
        href="../style/style.css"
    >

</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom px-4">

        <a
            class="navbar-brand d-flex align-items-center gap-3"
            href="home.php"
        >

            <img
                src="../assets/img/Logo.png"
                alt="Logo Pampa Serra"
                class="logo"
            >

        </a>

        <div class="mx-auto search-container">

            <div class="input-group">

                <input
                    type="text"
                    class="form-control"
                    id="searchInput"
                    placeholder="Rotas"
                >

                <button
                    class="btn btn-light"
                    id="searchBtn"
                    type="button"
                >

                    <i class="bi bi-search"></i>

                </button>

            </div>

        </div>

    </nav>


    <div class="container-fluid mt-4">

        <div class="row justify-content-center g-4">


            <!-- ALERTAS -->

            <div class="col-lg-3">

                <div class="info-card text-center">

                    <div class="alert-line danger">

                        Atenção Máxima -

                        <i class="bi bi-exclamation-triangle-fill"></i>

                    </div>

                    <div class="alert-line warning">

                        Atenção -

                        <i class="bi bi-exclamation-triangle-fill"></i>

                    </div>

                    <div class="alert-line success">

                        Esperado -

                        <i class="bi bi-exclamation-triangle-fill"></i>

                    </div>

                </div>

            </div>


            <!-- INFORMAÇÕES NORMAIS -->

            <div class="col-lg-2">

                <div class="info-card text-center">

                    <h2>
                        Normal:
                    </h2>

                    <p>
                        30-40 Km/h
                    </p>

                    <p>
                        Consumo:
                    </p>

                    <p>
                        3-5 L/km
                    </p>

                </div>

            </div>


            <!-- ROTAS -->

            <div class="col-lg-4">

                <div class="info-card text-center rotas-card">

                    <button
                        class="rota-btn"
                        type="button"
                    >

                        <?= htmlspecialchars($rota) ?>

                        -
                        
                        <?= htmlspecialchars($origem) ?>

                        a

                        <?= htmlspecialchars($destino) ?>.

                    </button>

                    <p class="my-2">

                        Parada Obrigatória:

                        Estação de
                        <?= htmlspecialchars($parada) ?>

                    </p>

                    <button
                        class="rota-btn"
                        type="button"
                    >

                        Rota 4180 -

                        Gramado a Porto Alegre.

                    </button>

                </div>

            </div>

        </div>


        <!-- MAPA -->

        <div class="mapa-container mt-4">

            <button
                class="btn-mapa btn-left"
                type="button"
            >

                <i class="bi bi-arrow-left-circle"></i>

            </button>


            <div class="mapa-box">

                <img
                    id="mapImage"
                    src=""
                    alt="Mapa da rota"
                >

            </div>


            <div class="info-mapa">

                <i
                    class="bi bi-exclamation-triangle-fill text-success fs-1">
                </i>

                <h2>
                    <?= htmlspecialchars($velocidade) ?>
                </h2>

                <h2>
                    <?= htmlspecialchars($consumo) ?>
                </h2>

            </div>


            <button
                class="btn-mapa btn-right"
                type="button"
            >

                <i class="bi bi-arrow-right-circle"></i>

            </button>

        </div>

    </div>


    <script src="../script/script.js"></script>

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js">
    </script>

</body>

</html>