<?php
require_once "verificar_acesso.php";
exigir_login();

require_once "../conexao.php";

$totalRotas = (int)$conexao->query(
    "SELECT COUNT(*) AS total FROM rota"
)->fetch_assoc()["total"];

$rotasAtivas = (int)$conexao->query(
    "SELECT COUNT(*) AS total
     FROM rota WHERE ativa = 1"
)->fetch_assoc()["total"];

$totalSensores = (int)$conexao->query(
    "SELECT COUNT(*) AS total FROM sensores"
)->fetch_assoc()["total"];

$totalUsuarios = (int)$conexao->query(
    "SELECT COUNT(*) AS total FROM usuarios"
)->fetch_assoc()["total"];

$rotas = $conexao->query(
    "SELECT codigo, origem, destino,
            velocidade_min, velocidade_max,
            consumo_min, consumo_max, ativa
     FROM rota ORDER BY codigo"
)->fetch_all(MYSQLI_ASSOC);

$sensores = $conexao->query(
    "SELECT nome, localizacao, tipo_dado, descricao
     FROM sensores ORDER BY nome"
)->fetch_all(MYSQLI_ASSOC);

function seguro_csv($valor) {
    $texto = (string)$valor;

    if (preg_match('/^[\s]*[=+\-@]/u', $texto)) {
        $texto = "'" . $texto;
    }

    return $texto;
}

if (isset($_GET["exportar"])) {

    $tipo = $_GET["exportar"];

    if (!in_array($tipo, ["rotas", "sensores"], true)) {
        http_response_code(400);
        exit("Exportação inválida.");
    }

    header("Content-Type: text/csv; charset=utf-8");
    header(
        'Content-Disposition: attachment; filename="' .
        $tipo . '.csv"'
    );

    $saida = fopen("php://output", "w");
    fwrite($saida, "\xEF\xBB\xBF");

    if ($tipo === "rotas") {

        fputcsv($saida, [
            "Código", "Origem", "Destino",
            "Velocidade mínima", "Velocidade máxima",
            "Consumo mínimo", "Consumo máximo", "Status"
        ], ";");

        foreach ($rotas as $r) {
            fputcsv($saida, array_map(
                "seguro_csv",
                [
                    $r["codigo"],
                    $r["origem"],
                    $r["destino"],
                    $r["velocidade_min"],
                    $r["velocidade_max"],
                    $r["consumo_min"],
                    $r["consumo_max"],
                    $r["ativa"] ? "Ativa" : "Inativa"
                ]
            ), ";");
        }

    } else {

        fputcsv($saida, [
            "Nome", "Localização", "Tipo", "Descrição"
        ], ";");

        foreach ($sensores as $s) {
            fputcsv($saida, array_map(
                "seguro_csv",
                [
                    $s["nome"], $s["localizacao"],
                    $s["tipo_dado"], $s["descricao"]
                ]
            ), ";");
        }
    }

    fclose($saida);
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Relatórios - Pampa Serra</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link rel="stylesheet" href="../style/style.css">

    <style>
        .relatorios-page {
            background: #f4f8ff;
            color: #0e2340;
            font-family: Arial, sans-serif;
            min-height: 100vh;
        }

        .relatorios-app {
            max-width: 1100px;
            margin: 0 auto;
            padding: 28px 16px 60px;
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .faixa-azul {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px;
            border-radius: 28px;
            background: linear-gradient(
                180deg, #1457c2, #0d3f8f
            );
            box-shadow: 0 10px 24px rgba(13,63,143,.18);
        }

        .btn-voltar-rel {
            padding: 10px 18px;
            border-radius: 12px;
            background: white;
            color: #0e2340;
            font-weight: 700;
            text-decoration: none;
        }

        .busca-rel {
            flex: 1;
            max-width: 420px;
            margin: auto;
            display: flex;
            background: white;
            border-radius: 999px;
            padding: 10px 16px;
        }

        .busca-rel input {
            width: 100%;
            border: none;
            outline: none;
            background: transparent;
        }

        .lista-cards {
            flex: 1;
            display: grid;
            grid-template-columns: repeat(4,1fr);
            gap: 12px;
        }

        .rel-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-height: 100px;
            background: white;
            border: 1px dashed #c9d8ee;
            border-radius: 16px;
            padding: 12px;
            text-align: center;
        }

        .rel-card b {
            font-size: 13px;
        }

        .rel-card strong {
            font-size: 26px;
            color: #1457c2;
        }

        .grade-paineis {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            width: 100%;
        }

        .painel {
            background: white;
            border-radius: 16px;
            padding: 16px;
            min-height: 190px;
        }

        .painel h3 {
            font-size: 17px;
            font-weight: 700;
        }

        .painel .legenda {
            font-size: 12px;
            color: #5d738f;
        }

        .sem-dados {
            border: 1.5px dashed #c9d8ee;
            border-radius: 12px;
            padding: 25px;
            margin-top: 12px;
            text-align: center;
            color: #5d738f;
        }

        .botoes-exportar {
            display: flex;
            gap: 8px;
            margin-top: 14px;
        }

        .botoes-exportar a {
            padding: 8px 13px;
            background: #1457c2;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 13px;
        }

        .rodape-rel {
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-principal-rel {
            border-radius: 999px;
            background: #1457c2;
            color: white;
            font-weight: 700;
            padding: 12px 22px;
            text-decoration: none;
        }

        @media(max-width:640px) {
            .lista-cards {
                grid-template-columns: repeat(2,1fr);
            }

            .grade-paineis {
                grid-template-columns: 1fr;
            }

            .faixa-azul {
                flex-wrap: wrap;
            }
        }
    </style>
</head>

<body class="relatorios-page">

<div class="relatorios-app">

    <div class="faixa-azul">

        <a href="home.php" class="btn-voltar-rel">
            ← Voltar
        </a>

        <div class="busca-rel">
            🔍
            <input
                type="search"
                id="campo-busca-rel"
                placeholder="Buscar relatório...">
        </div>

    </div>

    <div class="faixa-azul">

        <div class="lista-cards">

            <div class="rel-card">
                <b>Rotas</b>
                <strong><?= $totalRotas ?></strong>
                <small>Cadastradas</small>
            </div>

            <div class="rel-card">
                <b>Ativas</b>
                <strong><?= $rotasAtivas ?></strong>
                <small>Em operação cadastrada</small>
            </div>

            <div class="rel-card">
                <b>Sensores</b>
                <strong><?= $totalSensores ?></strong>
                <small>Cadastrados</small>
            </div>

            <div class="rel-card">
                <b>Usuários</b>
                <strong><?= $totalUsuarios ?></strong>
                <small>Contas</small>
            </div>

        </div>
    </div>

    <div class="faixa-azul">

        <div class="grade-paineis">

            <div class="painel" data-relatorio="velocidade rotas">
                <h3>Velocidade das rotas</h3>
                <span class="legenda">
                    Intervalos cadastrados
                </span>

                <div class="sem-dados">
                    <b><?= $totalRotas ?> rotas cadastradas</b>
                    <p>
                        A velocidade em tempo real ainda não
                        está conectada ao sistema.
                    </p>
                </div>

                <div class="botoes-exportar">
                    <a href="relatorios.php?exportar=rotas">
                        CSV
                    </a>
                </div>
            </div>

            <div class="painel" data-relatorio="pontualidade padrões">
                <h3>Pontualidade e padrões</h3>
                <span class="legenda">
                    Histórico operacional
                </span>

                <div class="sem-dados">
                    <b>Sem medições disponíveis</b>
                    <p>
                        É necessário cadastrar horários
                        previstos e reais das viagens.
                    </p>
                </div>
            </div>

            <div class="painel" data-relatorio="status operacional">
                <h3>Status operacional</h3>
                <span class="legenda">
                    Resumo cadastral
                </span>

                <div class="sem-dados">
                    <b><?= $rotasAtivas ?> rotas ativas</b>
                    <p>
                        <?= $totalSensores ?> sensores cadastrados.
                    </p>
                </div>

                <div class="botoes-exportar">
                    <a href="relatorios.php?exportar=sensores">
                        CSV
                    </a>
                </div>
            </div>

            <div class="painel" data-relatorio="relatório rotas">
                <h3>Relatório de rotas</h3>
                <span class="legenda">
                    Todas as rotas
                </span>

                <div class="sem-dados">
                    <b><?= $totalRotas ?> rotas registradas</b>
                    <p>
                        Exporte os registros cadastrados
                        para visualizar os detalhes.
                    </p>
                </div>

                <div class="botoes-exportar">
                    <a href="relatorios.php?exportar=rotas">
                        CSV
                    </a>

                    <a href="rotas.php">
                        Ver rotas
                    </a>
                </div>
            </div>

        </div>
    </div>

    <div class="rodape-rel">

        <a
            class="btn-principal-rel"
            href="relatorios.php?exportar=rotas">
            ↓ Exportar rotas em CSV
        </a>

        <a
            class="btn-principal-rel"
            href="relatorios.php?exportar=sensores">
            ↓ Exportar sensores em CSV
        </a>

    </div>

</div>

<script>
const busca = document.getElementById("campo-busca-rel");

busca.addEventListener("input", function () {
    const termo = busca.value.toLowerCase().trim();

    document.querySelectorAll("[data-relatorio]").forEach(
        function (painel) {
            painel.style.display =
                painel.dataset.relatorio.includes(termo)
                ? ""
                : "none";
        }
    );
});
</script>

</body>
</html>