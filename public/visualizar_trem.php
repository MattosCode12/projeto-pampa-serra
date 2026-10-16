
<?php
session_start();
require_once "conexao.php";

if (!isset($_SESSION["id_usuario"])) {
    header("Location: index.php");
    exit;
}

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    http_response_code(400);
    exit("Identificador inválido.");
}

$stmt = $conexao->prepare(
    "SELECT t.id_trem, t.codigo, t.modelo, t.status,
            u.nome AS responsavel
     FROM trens t
     INNER JOIN usuarios u ON t.id_usuario = u.id
     WHERE t.id_trem = ?"
);

$stmt->bind_param("i", $id);
$stmt->execute();
$trem = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$trem) {
    http_response_code(404);
    exit("Trem não encontrado.");
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Trem - Pampa Serra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <h1>Detalhes do trem</h1>

    <div class="card mt-3">
        <div class="card-body">
            <p><strong>Código:</strong>
                <?= htmlspecialchars($trem["codigo"]) ?></p>

            <p><strong>Modelo:</strong>
                <?= htmlspecialchars($trem["modelo"]) ?></p>

            <p><strong>Status:</strong>
                <?= htmlspecialchars($trem["status"]) ?></p>

            <p><strong>Usuário responsável:</strong>
                <?= htmlspecialchars($trem["responsavel"]) ?></p>
        </div>
    </div>

    <a href="trens.php" class="btn btn-secondary mt-3">Voltar</a>

    <?php if (($_SESSION["nivel_acesso"] ?? "") === "Administrador"): ?>
        <a href="editar_trem.php?id=<?= (int)$trem["id_trem"] ?>"
           class="btn btn-warning mt-3">Editar</a>
    <?php endif; ?>
</div>

</body>
</html>
