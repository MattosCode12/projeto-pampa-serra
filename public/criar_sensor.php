
<?php
require_once __DIR__ . "/verificar_acesso.php";
require_once __DIR__ . "/../conexao.php";

exigir_admin();

$erro = "";
$nome = "";
$localizacao = "";
$tipo_dado = "";
$descricao = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    validar_csrf();

    $campos = ["nome", "localizacao", "tipo_dado", "descricao"];

    foreach ($campos as $campo) {
        $valor = $_POST[$campo] ?? "";
        $$campo = is_string($valor) ? trim($valor) : "";
    }

    if (
        $nome === "" ||
        $localizacao === "" ||
        $tipo_dado === ""
    ) {
        $erro = "Preencha os campos obrigatórios.";
    } elseif (
        mb_strlen($nome) > 100 ||
        mb_strlen($localizacao) > 100 ||
        mb_strlen($tipo_dado) > 150
    ) {
        $erro = "Um dos campos ultrapassa o tamanho permitido.";
    } else {
        try {
            $stmt = $conexao->prepare(
                "INSERT INTO sensores
                 (nome, localizacao, tipo_dado, descricao)
                 VALUES (?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "ssss",
                $nome,
                $localizacao,
                $tipo_dado,
                $descricao
            );

            $stmt->execute();
            $stmt->close();

            header("Location: sensores.php?sucesso=cadastro");
            exit;

        } catch (mysqli_sql_exception $e) {
            error_log($e->getMessage());
            $erro = "Não foi possível cadastrar o sensor.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Sensor - Pampa Serra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/style.css">
    <link rel="stylesheet" href="../style/padrao-paginas.css">
    <style>
        .ps-criar-sensor .ps-moldura { width: 820px; }
        .ps-criar-sensor .sensor-introducao { display: flex; align-items: center; gap: 18px; padding-bottom: 24px; margin-bottom: 24px; border-bottom: 1px solid #dce4ef; }
        .ps-criar-sensor .sensor-icone { display: grid; place-items: center; width: 64px; height: 64px; flex-shrink: 0; border-radius: 18px; background: #eaf1fc; color: #174eae; }
        .ps-criar-sensor .sensor-introducao h2 { color: #15376b; font-weight: 600; }
        .ps-criar-sensor .sensor-introducao p { color: #586779; margin: 8px 0 0; }
        .ps-criar-sensor .form-label { font-weight: 600; color: #203655; }
        .ps-criar-sensor .form-control { min-height: 46px; border-radius: 10px; }
        .ps-criar-sensor .form-text { color: #586779; line-height: 1.5; }
        .ps-criar-sensor textarea.form-control { resize: vertical; min-height: 120px; }
        .ps-criar-sensor .sensor-acoes { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 12px; padding-top: 24px; margin-top: 28px; border-top: 1px solid #dce4ef; }
        .ps-criar-sensor .sensor-acoes .btn { padding: 11px 22px; border-radius: 10px; font-weight: 600; }
        .ps-criar-sensor .sensor-acoes .btn-primary { background: #174eae; border-color: #174eae; }
        .ps-criar-sensor .sensor-acoes .btn-primary:hover { background: #15376b; border-color: #15376b; }
        @media (max-width: 540px) {
            .ps-criar-sensor .sensor-introducao { align-items: flex-start; gap: 12px; }
            .ps-criar-sensor .sensor-icone { width: 48px; height: 48px; border-radius: 12px; }
            .ps-criar-sensor .sensor-acoes .btn { flex: 1 1 180px; }
        }
    </style>
</head>
<body class="ps-pagina ps-criar-sensor">
<header class="ps-cabecalho">
    <div class="ps-cabecalho-conteudo">
        <img src="../assets/img/logo.png" alt="Pampa Serra" class="ps-logo">
        <h1 class="ps-titulo">Cadastrar Sensor</h1>
        <a href="sensores.php" class="ps-voltar" aria-label="Voltar aos sensores cadastrados">
            <span aria-hidden="true">←</span> Voltar
        </a>
    </div>
</header>
<main class="ps-conteudo">
    <div class="ps-moldura ps-moldura-cadastro">
        <section class="ps-painel" aria-labelledby="titulo-cadastro">
            <div class="sensor-introducao">
                <div class="sensor-icone" aria-hidden="true">
                    <svg width="32" height="32" viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <rect x="9" y="9" width="14" height="14" rx="3"/>
                        <circle cx="16" cy="16" r="3"/>
                        <path d="M12 3v6m8-6v6M12 23v6m8-6v6M3 12h6m-6 8h6m14-8h6m-6 8h6"/>
                    </svg>
                </div>
                <div>
                    <h2 id="titulo-cadastro">Novo sensor</h2>
                    <p>Identifique o sensor e informe onde ele será utilizado.</p>
                </div>
            </div>

            <?php if ($erro !== ""): ?>
                <div class="alert alert-danger" role="alert"><?= h($erro) ?></div>
            <?php endif; ?>

            <p class="small text-secondary mb-4">Preencha os campos abaixo. A descrição é opcional.</p>
            <form method="POST">
                <?= campo_csrf() ?>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="nome" class="form-label">Nome do sensor</label>
                        <input type="text" id="nome" name="nome" maxlength="100" class="form-control"
                               placeholder="Ex.: Sensor de temperatura" value="<?= h($nome) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="tipo_dado" class="form-label">Tipo de dado</label>
                        <input type="text" id="tipo_dado" name="tipo_dado" maxlength="150" class="form-control"
                               placeholder="Ex.: Temperatura (°C)" value="<?= h($tipo_dado) ?>" required>
                    </div>
                    <div class="col-12">
                        <label for="localizacao" class="form-label">Localização</label>
                        <input type="text" id="localizacao" name="localizacao" maxlength="100" class="form-control"
                               placeholder="Ex.: KM 35 - São Leopoldo" value="<?= h($localizacao) ?>"
                               aria-describedby="ajuda-localizacao" required>
                        <div id="ajuda-localizacao" class="form-text">Para identificar a estação no mapa, use o formato KM + número + cidade, como no exemplo.</div>
                    </div>
                    <div class="col-12">
                        <label for="descricao" class="form-label">Descrição <span class="fw-normal text-secondary">(opcional)</span></label>
                        <textarea id="descricao" name="descricao" class="form-control" rows="4"
                                  placeholder="Descreva a função do sensor e observações sobre sua instalação."><?= h($descricao) ?></textarea>
                    </div>
                </div>
                <div class="sensor-acoes">
                    <a href="sensores.php" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" id="btn-criar-sensor" class="btn btn-primary">Cadastrar sensor</button>
                </div>
            </form>
        </section>
    </div>
</main>
</body>
</html>
