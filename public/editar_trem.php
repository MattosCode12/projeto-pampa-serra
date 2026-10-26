
<?php
require_once __DIR__ . "/verificar_acesso.php";
require_once __DIR__ . "/../conexao.php";

exigir_login();

if ((int)($_SESSION["tipo_usuario"] ?? 0) !== 1) {
    http_response_code(403);
    exit("Acesso negado. Somente administradores.");
}

if (empty($_SESSION["csrf_trens"])) {
    $_SESSION["csrf_trens"] = bin2hex(random_bytes(32));
}

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: trens.php");
    exit;
}

$stmt = $conexao->prepare(
    "SELECT id_trem, codigo, modelo, status, id_usuario
     FROM trens WHERE id_trem = ?"
);

$stmt->bind_param("i", $id);
$stmt->execute();
$trem = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$trem) {
    http_response_code(404);
    exit("Trem não encontrado.");
}

$usuarios = $conexao->query(
    "SELECT id, nome FROM usuarios ORDER BY nome"
)->fetch_all(MYSQLI_ASSOC);

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $token = $_POST["csrf_trens"] ?? "";

    if (!is_string($token) ||
        !hash_equals($_SESSION["csrf_trens"], $token)) {
        http_response_code(403);
        exit("Solicitação inválida.");
    }

    $trem["codigo"] = trim((string)($_POST["codigo"] ?? ""));
    $trem["modelo"] = trim((string)($_POST["modelo"] ?? ""));
    $trem["status"] = $_POST["status"] ?? "";
    $trem["id_usuario"] = filter_input(
        INPUT_POST, "id_usuario", FILTER_VALIDATE_INT
    );

    $idsValidos = array_map(
        "intval",
        array_column($usuarios, "id")
    );

    if (
        $trem["codigo"] === "" ||
        mb_strlen($trem["codigo"]) > 50 ||
        $trem["modelo"] === "" ||
        mb_strlen($trem["modelo"]) > 100 ||
        !in_array($trem["status"], ["Normal", "Alerta", "Falha"], true) ||
        !in_array($trem["id_usuario"], $idsValidos, true)
    ) {
        $erro = "Preencha os campos corretamente.";
    } else {
        try {
            $stmt = $conexao->prepare(
                "UPDATE trens
                 SET codigo = ?, modelo = ?, status = ?, id_usuario = ?
                 WHERE id_trem = ?"
            );

            $stmt->bind_param(
                "sssii",
                $trem["codigo"],
                $trem["modelo"],
                $trem["status"],
                $trem["id_usuario"],
                $id
            );

            $stmt->execute();
            $stmt->close();

            header("Location: visualizar_trem.php?id=" . $id);
            exit;

        } catch (mysqli_sql_exception $e) {
            error_log($e->getMessage());
            $erro = "Não foi possível atualizar o trem. Verifique se o código já existe.";
        }
    }
}

function trem_h($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar Trem - Pampa Serra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/style.css">
    <style>
        body { background: #f3f3f3; }
        .topo { background: #174eae; color: white; padding: 25px; }
        .painel { background: white; padding: 30px; border-radius: 15px; }
    </style>
</head>
<body>

<header class="topo">
    <div class="container">
        <h1>Editar Trem</h1>
    </div>
</header>

<main class="container py-4">
    <div class="painel mx-auto" style="max-width: 700px;">

        <?php if ($erro !== ""): ?>
            <div class="alert alert-danger">
                <?= trem_h($erro) ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <input type="hidden"
                   name="csrf_trens"
                   value="<?= trem_h($_SESSION["csrf_trens"]) ?>">

            <div class="mb-3">
                <label for="codigo" class="form-label">Código</label>
                <input type="text" name="codigo" id="codigo"
                       class="form-control" maxlength="50" required
                       value="<?= trem_h($trem["codigo"]) ?>">
            </div>

            <div class="mb-3">
                <label for="modelo" class="form-label">Modelo</label>
                <input type="text" name="modelo" id="modelo"
                       class="form-control" maxlength="100" required
                       value="<?= trem_h($trem["modelo"]) ?>">
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <?php foreach (["Normal", "Alerta", "Falha"] as $opcao): ?>
                        <option value="<?= $opcao ?>"
                            <?= $trem["status"] === $opcao ? "selected" : "" ?>>
                            <?= $opcao ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="id_usuario" class="form-label">
                    Responsável
                </label>
                <select name="id_usuario" id="id_usuario"
                        class="form-select" required>
                    <?php foreach ($usuarios as $usuario): ?>
                        <option value="<?= (int)$usuario["id"] ?>"
                            <?= (int)$trem["id_usuario"] === (int)$usuario["id"]
                                ? "selected" : "" ?>>
                            <?= trem_h($usuario["nome"]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">
                Salvar alterações
            </button>

            <a href="visualizar_trem.php?id=<?= (int)$id ?>"
               class="btn btn-secondary">
                Cancelar
            </a>

        </form>
    </div>
</main>
</body>
</html>
