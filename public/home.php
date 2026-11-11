<?php
require_once __DIR__ . "/verificar_acesso.php";
require_once __DIR__ . "/../conexao.php";
require_once __DIR__ . "/endereco_usuario.php";

exigir_login();
$enderecoHome = endereco_carregar($conexao, (int)$_SESSION["usuario_id"]);

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

$resumoRotas = null;
$rotasAtivas = [];

if ($busca === "") {
    try {
        $resumoRotas = $conexao->query(
            "SELECT COUNT(*) AS total,
                    COUNT(CASE WHEN ativa = 1 THEN 1 END) AS ativas
             FROM rota"
        )->fetch_assoc();

        $rotasAtivas = $conexao->query(
            "SELECT id_rota, codigo, origem, destino
             FROM rota
             WHERE ativa = 1
             ORDER BY id_rota DESC
             LIMIT 6"
        )->fetch_all(MYSQLI_ASSOC);
    } catch (mysqli_sql_exception $e) {
        error_log($e->getMessage());
        $resumoRotas = null;
    }
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

    <link rel="stylesheet" href="../style/clima-home.css">
    <link rel="stylesheet" href="../style/painel-home.css">
    <script src="../script/clima-home.js" defer></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="" defer></script>
    <script src="../script/malha-rs.js" defer></script>
</head>
<body class="painel-home">
<a class="visually-hidden-focusable" href="#conteudo-home">Ir para o conteúdo</a>

<aside class="lateral-home" aria-label="Menu do Pampa Serra">
    <a href="home.php" class="marca-home">
        <img src="../assets/img/logo.png" alt="Pampa Serra" width="104">
    </a>
    <nav class="menu-home" aria-label="Menu principal">
        <a href="home.php" aria-current="page">Visão geral</a>
        <a href="rotas.php">Rotas</a>
        <a href="sensores.php">Sensores</a>
        <a href="trens.php">Trens</a>
        <a href="relatorios.php">Relatórios</a>
        <?php if ($tipo_usuario === 1): ?>
            <a href="cadastro_usuarios.php">Cadastrar</a>
        <?php endif; ?>
    </nav>
</aside>

<div class="area-home">
    <header class="barra-home">
        <form class="input-group busca-home" action="home.php" method="get" role="search">
            <input type="search" name="busca" class="form-control" maxlength="100"
                   aria-label="Buscar trens, sensores e rotas"
                   placeholder="Buscar trens, sensores e rotas" value="<?= h($busca) ?>">
            <button type="submit" class="btn btn-primary">Buscar</button>
        </form>

        <div class="conta-home">
            <a href="logout.php" class="btn btn-outline-primary btn-sm">Sair</a>
            <svg class="icone-perfil-home" width="36" height="36" viewBox="0 0 24 24"
                 fill="currentColor" aria-hidden="true">
                <circle cx="12" cy="7.5" r="4.5"/>
                <path d="M3 21c0-5 4-8 9-8s9 3 9 8z"/>
            </svg>
            <div class="identidade-home">
                <span class="nome-home"><?= h($nome) ?></span>
                <span class="perfil-home"><?= $tipo_usuario === 1 ? "Administrador" : "Usuário" ?></span>
            </div>
        </div>
    </header>

    <main id="conteudo-home" class="conteudo-home">
        <h1>Visão geral</h1>
        <?php require __DIR__ . "/cartao_clima.php"; ?>

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

        <?php if ($busca === ""): ?>
            <section class="resumo-rotas" aria-labelledby="titulo-rotas">
                <div class="cabecalho-rotas">
                    <h2 id="titulo-rotas">Situação das rotas</h2>
                    <a href="relatorios.php" class="btn btn-outline-primary btn-sm">Ver relatórios</a>
                </div>

                <?php if ($resumoRotas === null): ?>
                    <p class="mb-0 text-muted">Não foi possível carregar o resumo das rotas.</p>
                <?php else: ?>
                    <?php
                    $total = (int)$resumoRotas["total"];
                    $ativas = (int)$resumoRotas["ativas"];
                    ?>
                    <dl class="indicadores-rotas">
                        <div>
                            <dt>Cadastradas</dt>
                            <dd><?= $total ?></dd>
                        </div>
                        <div>
                            <dt>Ativas</dt>
                            <dd><?= $ativas ?></dd>
                        </div>
                        <div>
                            <dt>Inativas</dt>
                            <dd><?= $total - $ativas ?></dd>
                        </div>
                    </dl>

                    <div class="cabecalho-rotas">
                        <h3>Rotas ativas</h3>
                        <a href="rotas.php">Ver todas as rotas</a>
                    </div>

                    <?php if (!$rotasAtivas): ?>
                        <p class="mb-0 text-muted">Nenhuma rota ativa no momento.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0" aria-label="Rotas ativas mais recentes">
                                <thead>
                                    <tr>
                                        <th scope="col">Código</th>
                                        <th scope="col">Origem</th>
                                        <th scope="col">Destino</th>
                                        <th scope="col">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rotasAtivas as $rota): ?>
                                        <tr>
                                            <td>
                                                <a href="tela_rotas.php?id=<?= (int)$rota["id_rota"] ?>">
                                                    <?= h($rota["codigo"]) ?>
                                                </a>
                                            </td>
                                            <td><?= h($rota["origem"]) ?></td>
                                            <td><?= h($rota["destino"]) ?></td>
                                            <td><span class="badge text-bg-success">Ativa</span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </section>
        <?php endif; ?>
        <?php require __DIR__ . "/malha_rs.php"; ?>
    </main>
</div>
</body>
</html>
