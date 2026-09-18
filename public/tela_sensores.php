<?php

$sensores = [

    [
        "nome" => "Sensor 1.21",
        "km" => "KM 0",
        "local" => "Porto Alegre",
        "tipo" => "GPS + Sensor de partida",
        "descricao" => "Início da viagem e acompanhamento da rota"
    ],

    [
        "nome" => "Sensor 1.47",
        "km" => "KM 22",
        "local" => "Aeroporto",
        "tipo" => "Sensor de aproximação",
        "descricao" => "Controle do trem em área urbana."
    ],

    [
        "nome" => "Sensor 1.82",
        "km" => "KM 35",
        "local" => "São Leopoldo",
        "tipo" => "Sensor de obstáculos",
        "descricao" => "Aumentar a segurança nos trilhos."
    ],

    [
        "nome" => "Sensor 1.93",
        "km" => "KM 52",
        "local" => "Novo Hamburgo",
        "tipo" => "Sensor de passagem e sensor de peso dos vagões",
        "descricao" => "Esta será a única parada intermediária da linha."
    ],

    [
        "nome" => "Sensor 2.03",
        "km" => "KM 62",
        "local" => "Sapiranga",
        "tipo" => "Sensor de temperatura",
        "descricao" => "Monitoramento dos freios e motor."
    ],

    [
        "nome" => "Sensor 2.19",
        "km" => "KM 74",
        "local" => "Parobé",
        "tipo" => "Sensor de energia",
        "descricao" => "Controle do consumo do trem."
    ],

    [
        "nome" => "Sensor 2.34",
        "km" => "KM 84",
        "local" => "Taquara",
        "tipo" => "Sensor de presença",
        "descricao" => "Detectar a passagem do trem."
    ],

    [
        "nome" => "Sensor 2.51",
        "km" => "KM 96",
        "local" => "Igrejinha",
        "tipo" => "Sensor Climático",
        "descricao" => "Para detectar chuva e neblina."
    ],

    [
        "nome" => "Sensor 2.79",
        "km" => "KM 106",
        "local" => "Três Coroas",
        "tipo" => "Sensor de vibração",
        "descricao" => "Monitorar desgaste dos trilhos."
    ],

    [
        "nome" => "Sensor 3.12",
        "km" => "KM 114",
        "local" => "Canela",
        "tipo" => "Sensor de velocidade",
        "descricao" => "Para controle em curvas."
    ],

    [
        "nome" => "Sensor 3.36",
        "km" => "KM 120",
        "local" => "Gramado",
        "tipo" => "Sensor final e GPS",
        "descricao" => "Encerramento da rota e registro da chegada."
    ]

];

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Sensores - Pampa Serra</title>

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

<body class="sensores-page">

    <nav class="navbar-sensores">

        <img
            src="../assets/img/Logo.png"
            id="logo-sensores"
            alt="Logo Pampa Serra"
        >

        <div class="buscar-sensor">

            <input
                type="text"
                id="buscarSensor"
                placeholder="Buscar"
            >

            <button type="button">

                <i class="bi bi-search"></i>

            </button>

        </div>

        <div class="botoes-topo">

            <button
                class="btn-topo"
                onclick="window.location.href='criar_sensor.php'"
            >
                Criar
            </button>

        </div>

    </nav>


    <main class="sensores-container">

        <div class="row g-4 justify-content-center">

            <?php foreach ($sensores as $sensor) { ?>

                <div class="col-12 col-md-6 col-lg-4 sensor-item">

                    <div class="sensor-card">

                        <div class="sensor-linha sensor-titulo">

                            <span>
                                <?= htmlspecialchars($sensor["nome"]) ?>
                            </span>

                            <button
                                type="button"
                                title="Excluir sensor"
                            >

                                <i class="bi bi-trash"></i>

                            </button>

                        </div>


                        <div class="sensor-linha">

                            <u>
                                <?= htmlspecialchars($sensor["km"]) ?>
                            </u>

                        </div>


                        <div class="sensor-linha">

                            <?= htmlspecialchars($sensor["local"]) ?>

                        </div>


                        <div class="sensor-linha">

                            <?= htmlspecialchars($sensor["tipo"]) ?>

                        </div>


                        <div class="sensor-linha sensor-desc">

                            <?= htmlspecialchars($sensor["descricao"]) ?>

                        </div>

                    </div>

                </div>

            <?php } ?>

        </div>

    </main>


    <script src="../script/script.js"></script>

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js">
    </script>

</body>

</html>