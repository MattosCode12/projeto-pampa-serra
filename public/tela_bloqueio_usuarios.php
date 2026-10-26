
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../index.php");
    exit;
}

http_response_code(403);

$nome = $_SESSION["nome"] ?? "Usuário";

function bloqueio_h($valor) {
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES,
        "UTF-8"
    );
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso Negado - Pampa Serra</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/style.css">

    <style>
        body {
            margin: 0;
            background: #f3f3f3;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .painel-bloqueio {
            width: 100%;
            max-width: 550px;
            background: white;
            padding: 45px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 8px 30px rgba(0,0,0,.12);
        }

        .icone-bloqueio {
            font-size: 65px;
            color: #c62828;
            margin-bottom: 15px;
        }

        .titulo-bloqueio {
            color: #174eae;
            font-weight: 900;
            margin-bottom: 15px;
        }

        .btn-voltar {
            background: #0755bc;
            color: white;
            border-radius: 10px;
            padding: 12px 25px;
            text-decoration: none;
            display: inline-block;
            font-weight: bold;
            margin-top: 20px;
        }

        .btn-voltar:hover {
            background: #08469a;
            color: white;
        }
    </style>
</head>

<body>

<main class="painel-bloqueio">

    <div class="icone-bloqueio" aria-hidden="true">
        &#9888;
    </div>

    <h1 class="titulo-bloqueio">
        Acesso Negado
    </h1>

    <p>
        Olá, <?= bloqueio_h($nome) ?>.
    </p>

    <p>
        Você não possui permissão para acessar
        esta área do sistema.
    </p>

    <p class="text-muted">
        Entre em contato com um administrador
        caso precise de acesso.
    </p>

    <a href="home.php" class="btn-voltar">
        Voltar à Página Principal
    </a>

</main>

</body>
</html>
