<?php
session_start();
require_once "../conexao.php";

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../index.php");
    exit;
}

$busca = trim($_GET["busca"] ?? "");

if ($busca != "") {
    $sql = "SELECT * FROM rota WHERE codigo LIKE ? OR origem LIKE ? OR destino LIKE ? ORDER BY id_rota DESC";
    $stmt = $conexao->prepare($sql);
    $termo = "%".$busca."%";
    $stmt->bind_param("sss", $termo, $termo, $termo);
    $stmt->execute();
    $resultado = $stmt->get_result();
} else {
    $resultado = $conexao->query("SELECT * FROM rota ORDER BY id_rota DESC");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rotas - Pampa Serra</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../style/style.css">
</head>
<body class="bg-light">
<div class="container mt-5">
<h2>Gerenciamento de Rotas</h2>
<a href="criar_rota.php" class="btn btn-success mb-3">Cadastrar Rota</a>
<form method="GET" class="mb-3">
<div class="input-group">
<input type="text" name="busca" class="form-control" placeholder="Buscar rota" value="<?= htmlspecialchars($busca) ?>">
<button type="submit" class="btn btn-dark">Buscar</button>
</div>
</form>
<div class="card shadow-sm p-4">
<table class="table table-hover">
<thead>
<tr>
<th>Código</th>
<th>Origem</th>
<th>Destino</th>
<th>Velocidade</th>
<th>Consumo</th>
<th>Status</th>
<th>Ações</th>
</tr>
</thead>
<tbody>
<?php if ($resultado && $resultado->num_rows > 0): ?>
<?php while ($rota = $resultado->fetch_assoc()): ?>
<tr>
<td><?= htmlspecialchars($rota["codigo"]) ?></td>
<td><?= htmlspecialchars($rota["origem"]) ?></td>
<td><?= htmlspecialchars($rota["destino"]) ?></td>
<td><?= $rota["velocidade_min"] ?> - <?= $rota["velocidade_max"] ?> km/h</td>
<td><?= $rota["consumo_min"] ?> - <?= $rota["consumo_max"] ?> L/km</td>
<td><?= $rota["ativa"] ? "Ativa" : "Inativa" ?></td>
<td>
<a href="visualizar_rota.php?id=<?= $rota["id_rota"] ?>" class="btn btn-primary btn-sm">Ver</a>
<a href="editar_rota.php?id=<?= $rota["id_rota"] ?>" class="btn btn-warning btn-sm">Editar</a>
<form action="excluir_rota.php" method="POST" class="d-inline" onsubmit="return confirm('Deseja realmente excluir esta rota?');">
<input type="hidden" name="id" value="<?= $rota["id_rota"] ?>">
<button type="submit" class="btn btn-danger btn-sm">Excluir</button>
</form>
</td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr>
<td colspan="7" class="text-center">Nenhuma rota encontrada.</td>
</tr>
<?php endif; ?>
</tbody>
</table>
</div>
<a href="home.php" class="btn btn-secondary mt-3">Voltar</a>
</div>
</body>
</html>