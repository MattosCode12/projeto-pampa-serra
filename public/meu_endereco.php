<?php
require_once __DIR__ . '/verificar_acesso.php';
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/endereco_usuario.php';
exigir_login();
$id = (int)$_SESSION['usuario_id'];
$endereco = endereco_carregar($conexao, $id);
$enderecoObrigatorio = true;
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validar_csrf();
    $endereco = endereco_ler($_POST);
    $erro = endereco_validar($endereco);
    if ($erro === '') {
        try {
            endereco_salvar($conexao, $id, $endereco);
            header('Location: home.php');
            exit;
        } catch (mysqli_sql_exception $e) {
            error_log($e->getMessage());
            $erro = 'Não foi possível salvar seu endereço. Tente novamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Meu endereço - Pampa Serra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/style.css">
    <link rel="stylesheet" href="../style/padrao-paginas.css">
    <script src="../script/endereco-usuario.js" defer></script>
</head>
<body class="ps-pagina">
<header class="ps-cabecalho"><div class="ps-cabecalho-conteudo">
    <img src="../assets/img/logo.png" alt="Pampa Serra" class="ps-logo">
    <h1 class="ps-titulo">Meu endereço</h1>
    <a href="home.php" class="ps-voltar"><span aria-hidden="true">←</span> Voltar</a>
</div></header>
<main class="ps-conteudo"><div class="ps-moldura ps-moldura-cadastro"><section class="ps-painel">
    <h2 class="mb-3">Sua localização</h2>
    <p class="text-muted">Usamos sua cidade para mostrar o clima na página inicial.</p>
    <?php if ($erro !== ''): ?><div class="alert alert-danger" role="alert"><?= h($erro) ?></div><?php endif; ?>
    <form method="POST">
        <?= campo_csrf() ?>
        <?php require __DIR__ . '/campos_endereco.php'; ?>
        <button class="btn btn-primary" type="submit">Salvar endereço</button>
        <a href="home.php" class="btn btn-secondary">Cancelar</a>
    </form>
</section></div></main>
</body></html>
