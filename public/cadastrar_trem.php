
<?php
require_once __DIR__ . "/verificar_acesso.php";
require_once __DIR__ . "/../conexao.php";
require_once __DIR__ . "/trem_rota.php";

exigir_login();

if ((int)($_SESSION["tipo_usuario"] ?? 0) !== 1) {
    http_response_code(403);
    exit("Acesso negado. Somente administradores.");
}

if (empty($_SESSION["csrf_trens"])) {
    $_SESSION["csrf_trens"] = bin2hex(random_bytes(32));
}

$erro = "";
$codigo = "";
$modelo = "";
$status = "Normal";
$id_usuario = 0;

$usuarios = $conexao->query(
    "SELECT id, nome FROM usuarios ORDER BY nome"
)->fetch_all(MYSQLI_ASSOC);

$idRota = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $token = $_POST["csrf_trens"] ?? "";

    if (!is_string($token) ||
        !hash_equals($_SESSION["csrf_trens"], $token)) {
        http_response_code(403);
        exit("Solicitação inválida.");
    }

    $codigo = trim((string)($_POST["codigo"] ?? ""));
    $modelo = trim((string)($_POST["modelo"] ?? ""));
    $status = $_POST["status"] ?? "";
    $id_usuario = filter_input(
        INPUT_POST, "id_usuario", FILTER_VALIDATE_INT
    );

    $rotaEnviada = $_POST["id_rota"] ?? "";
    $idRota = $rotaEnviada === "" ? null : filter_var(
        $rotaEnviada, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]
    );

    $idsValidos = array_map(
        "intval",
        array_column($usuarios, "id")
    );

    if (
        $idRota === false ||
        $codigo === "" ||
        mb_strlen($codigo) > 50 ||
        $modelo === "" ||
        mb_strlen($modelo) > 100 ||
        !in_array($status, ["Normal", "Alerta", "Falha"], true) ||
        !in_array($id_usuario, $idsValidos, true)
    ) {
        $erro = "Preencha todos os campos corretamente.";
    } else {
        try {
            $conexao->begin_transaction();
            if ($idRota !== null) {
                bloquear_rota_ativa($conexao, $idRota);
            }

            $stmt = $conexao->prepare(
                "INSERT INTO trens (codigo, modelo, status, id_usuario)
                 VALUES (?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "sssi",
                $codigo,
                $modelo,
                $status,
                $id_usuario
            );

            $stmt->execute();
            $idTrem = $conexao->insert_id;
            $stmt->close();

            if ($idRota !== null) {
                vincular_trem_rota($conexao, $idTrem, $idRota);
            }
            $conexao->commit();

            header("Location: trens.php?sucesso=cadastrado");
            exit;

        } catch (DomainException $e) {
            $conexao->rollback();
            $erro = $e->getMessage();
        } catch (mysqli_sql_exception $e) {
            $conexao->rollback();
            error_log($e->getMessage());
            $erro = "Não foi possível cadastrar. Verifique se o código já existe.";
        }
    }
}

$rotasAtivas = $conexao->query(
    "SELECT id_rota, codigo, origem, destino FROM rota WHERE ativa = 1 ORDER BY codigo"
)->fetch_all(MYSQLI_ASSOC);

function trem_h($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cadastrar Trem - Pampa Serra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/style.css">
    <link rel="stylesheet" href="../style/padrao-paginas.css">
</head>
<body class="ps-pagina">

<header class="ps-cabecalho">
    <div class="ps-cabecalho-conteudo">
        <img src="../assets/img/logo.png" alt="Pampa Serra" class="ps-logo">

        <h1 class="ps-titulo">Cadastrar Trem</h1>

        <a href="trens.php" class="ps-voltar" aria-label="Voltar aos trens cadastrados">
            <span aria-hidden="true">←</span> Voltar
        </a>
    </div>
</header>

<main class="ps-conteudo">
    <div class="ps-moldura ps-moldura-cadastro">
        <section class="ps-painel">
        <h2 class="mb-4">Cadastrar Trem</h2>

        <?php if ($erro !== ""): ?>
            <div class="alert alert-danger" role="alert">
                <?= trem_h($erro) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="row g-3">

            <input type="hidden"
                   name="csrf_trens"
                   value="<?= trem_h($_SESSION["csrf_trens"]) ?>">

            <div class="col-md-6">
                <label for="codigo" class="form-label">Código do trem</label>
                <input type="text" name="codigo" id="codigo"
                       class="form-control" maxlength="50" required
                       value="<?= trem_h($codigo) ?>">
            </div>

            <div class="col-md-6">
                <label for="modelo" class="form-label">Modelo</label>
                <input type="text" name="modelo" id="modelo"
                       class="form-control" maxlength="100" required
                       value="<?= trem_h($modelo) ?>">
            </div>

            <div class="col-md-6">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <?php foreach (["Normal", "Alerta", "Falha"] as $opcao): ?>
                        <option value="<?= $opcao ?>"
                            <?= $status === $opcao ? "selected" : "" ?>>
                            <?= $opcao ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label for="id_usuario" class="form-label">
                    Responsável
                </label>
                <select name="id_usuario" id="id_usuario"
                        class="form-select" required>
                    <option value="">Selecione um usuário</option>
                    <?php foreach ($usuarios as $usuario): ?>
                        <option value="<?= (int)$usuario["id"] ?>"
                            <?= $id_usuario == $usuario["id"]
                                ? "selected" : "" ?>>
                            <?= trem_h($usuario["nome"]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12">
                <label for="id_rota" class="form-label">Rota ativa</label>
                <select name="id_rota" id="id_rota" class="form-select">
                    <option value="">Sem rota</option>
                    <?php foreach ($rotasAtivas as $rotaAtiva): ?>
                        <option value="<?= (int)$rotaAtiva["id_rota"] ?>"
                            <?= $idRota === (int)$rotaAtiva["id_rota"] ? "selected" : "" ?>>
                            <?= trem_h($rotaAtiva["codigo"] . " — " . $rotaAtiva["origem"] . " → " . $rotaAtiva["destino"]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!$rotasAtivas): ?>
                    <div class="form-text">Nenhuma rota ativa disponível.</div>
                <?php endif; ?>
            </div>

            <div class="col-12 d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary">
                Cadastrar Trem
            </button>

            <a href="trens.php" class="btn btn-secondary">
                Cancelar
            </a>
            </div>

        </form>
        </section>
    </div>
</main>

</body>
</html>
