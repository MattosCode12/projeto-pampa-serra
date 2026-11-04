<?php
require_once __DIR__ . "/verificar_acesso.php";
require_once __DIR__ . "/../conexao.php";

exigir_login();

$nome = $_SESSION["nome"] ?? "Usuário";
$tipo_usuario = (int)($_SESSION["tipo_usuario"] ?? 0);

$busca = trim((string)($_GET["busca"] ?? ""));
if (mb_strlen($busca) > 100) {
    $busca = mb_substr($busca, 0, 100);
}

$resTrens = [];
$resSensores = [];
$resRotas = [];

function buscar_home(mysqli $conexao, string $sql, string $termo, int $qtd): array {
    $stmt = $conexao->prepare($sql);
    $params = array_fill(0, $qtd, $termo);
    $stmt->bind_param(str_repeat("s", $qtd), ...$params);
    $stmt->execute();
    $dados = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $dados;
}

if ($busca !== "") {
    $termo = "%" . addcslashes($busca, "%_\\") . "%";

    $resTrens = buscar_home(
        $conexao,
        "SELECT id_trem, codigo, modelo, status
         FROM trens
         WHERE codigo LIKE ? OR modelo LIKE ? OR status LIKE ?
         ORDER BY codigo
         LIMIT 20",
        $termo,
        3
    );

    $resSensores = buscar_home(
        $conexao,
        "SELECT id_sensor, nome, localizacao, tipo_dado
         FROM sensores
         WHERE nome LIKE ? OR localizacao LIKE ?
            OR tipo_dado LIKE ? OR descricao LIKE ?
         ORDER BY nome
         LIMIT 20",
        $termo,
        4
    );

    $resRotas = buscar_home(
        $conexao,
        "SELECT id_rota, codigo, origem, destino, parada
         FROM rota
         WHERE codigo LIKE ? OR origem LIKE ?
            OR destino LIKE ? OR parada LIKE ?
         ORDER BY codigo
         LIMIT 20",
        $termo,
        4
    );
}

$totalResultados = count($resTrens) + count($resSensores) + count($resRotas);
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
            display: flex;
            flex-direction: column;
        }

        .cabecalho {
            width: 100%;
            min-height: 138px;
            background: #174eae;
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            padding: 20px 35px;
            gap: 20px;
        }

        .logo-home {
            width: 90px;
            height: auto;
            justify-self: start;
        }

        .busca-home {
            display: flex;
            width: 415px;
            max-width: 100%;
            justify-self: center;
            margin: 0;
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
            justify-self: end;
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

        .conteudo-home {
            flex: 1;
            width: 100%;
            max-width: 535px;
            margin: 0 auto;
            padding: 37px 15px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: stretch;
        }

        .menu-home {
            display: flex;
            flex-direction: column;
            gap: 22px;
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

        .resultados-busca {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
        }

        .resultados-busca h2 {
            font-size: 20px;
            font-weight: 800;
            margin: 0;
        }

        .resultados-busca h3 {
            font-size: 16px;
            font-weight: 800;
            color: #0755bc;
            margin: 18px 0 8px;
        }

        .resultados-busca .item-resultado {
            display: block;
            padding: 8px 10px;
            border-radius: 8px;
            color: #222;
            text-decoration: none;
        }

        .resultados-busca .item-resultado:hover {
            background: #eef3fb;
        }

        .resultados-busca .item-resultado small {
            display: block;
            color: #666;
        }

        @media (max-width: 768px) {
            .cabecalho {
                grid-template-columns: 1fr;
                justify-items: center;
                padding: 18px;
            }

            .logo-home,
            .busca-home,
            .usuario-home {
                justify-self: center;
            }

            .busca-home {
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
            maxlength="100"
            value="<?= h($busca) ?>"
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

<main class="conteudo-home">

    <?php if ($busca !== ""): ?>
        <section class="resultados-busca">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h2>
                    <?= $totalResultados ?> resultado(s) para
                    "<?= h($busca) ?>"
                </h2>
                <a href="home.php">Limpar busca</a>
            </div>

            <?php if ($totalResultados === 0): ?>
                <p class="text-muted mt-3 mb-0">
                    Nada encontrado em trens, sensores ou rotas.
                </p>
            <?php endif; ?>

            <?php if ($resTrens): ?>
                <h3>Trens</h3>
                <?php foreach ($resTrens as $t): ?>
                    <a class="item-resultado"
                       href="visualizar_trem.php?id=<?= (int)$t["id_trem"] ?>">
                        <?= h($t["codigo"]) ?>
                        <small><?= h($t["modelo"]) ?> • <?= h($t["status"]) ?></small>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if ($resSensores): ?>
                <h3>Sensores</h3>
                <?php foreach ($resSensores as $s): ?>
                    <a class="item-resultado"
                       href="sensores.php?busca=<?= urlencode($s["nome"]) ?>">
                        <?= h($s["nome"]) ?>
                        <small><?= h($s["localizacao"]) ?> • <?= h($s["tipo_dado"]) ?></small>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if ($resRotas): ?>
                <h3>Rotas</h3>
                <?php foreach ($resRotas as $r): ?>
                    <a class="item-resultado"
                       href="rotas.php?busca=<?= urlencode($r["codigo"]) ?>">
                        <?= h($r["codigo"]) ?>
                        <small>
                            <?= h($r["origem"]) ?> → <?= h($r["destino"]) ?>
                            <?= $r["parada"] ? " • Parada: " . h($r["parada"]) : "" ?>
                        </small>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>

        </section>
    <?php endif; ?>

    <nav class="menu-home">

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

    </nav>

</main>

</body>
</html>