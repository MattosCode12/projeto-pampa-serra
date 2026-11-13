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

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: trens.php");
    exit;
}

$stmt = $conexao->prepare(
    "SELECT t.id_trem, t.codigo, t.modelo, t.status, t.id_usuario,
            r.id_rota, r.codigo AS codigo_rota
     FROM trens t
     LEFT JOIN trem_rota tr ON tr.id_trem = t.id_trem
     LEFT JOIN rota r ON r.id_rota = tr.id_rota
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

$usuarios = $conexao->query(
    "SELECT id, nome FROM usuarios ORDER BY nome"
)->fetch_all(MYSQLI_ASSOC);

$erro = "";

$idRota = null;

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
            $conexao->begin_transaction();
            if ($idRota !== null) {
                bloquear_rota_ativa($conexao, $idRota);
            }

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

            if ($idRota !== null) {
                vincular_trem_rota($conexao, $id, $idRota);
            }
            $conexao->commit();

            header("Location: visualizar_trem.php?id=" . $id);
            exit;

        } catch (DomainException $e) {
            $conexao->rollback();
            $erro = $e->getMessage();
        } catch (mysqli_sql_exception $e) {
            $conexao->rollback();
            error_log($e->getMessage());
            $erro = "Não foi possível atualizar o trem. Verifique se o código já existe.";
        }
    }
}

$rotasAtivas = $trem["id_rota"] ? [] : $conexao->query(
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
    <title>Editar Trem - Pampa Serra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/style.css">
    <link rel="stylesheet" href="../style/padrao-paginas.css">
    <style>
        .ps-editar-trem .ps-moldura { width: 950px; }
        .imagem-trem {
            width: 100%;
            max-height: 320px;
            object-fit: contain;
        }
    </style>
</head>
<body class="ps-pagina ps-editar-trem">

<header class="ps-cabecalho">
    <div class="ps-cabecalho-conteudo">
        <img src="../assets/img/logo.png" alt="Pampa Serra" class="ps-logo">
        <h1 class="ps-titulo">Editar Trem</h1>
        <a href="visualizar_trem.php?id=<?= (int)$id ?>" class="ps-voltar" aria-label="Voltar aos detalhes do trem">
            <span aria-hidden="true">←</span> Voltar
        </a>
    </div>
</header>

<main class="ps-conteudo">
    <div class="ps-moldura ps-moldura-cadastro">
        <section class="ps-painel" aria-labelledby="titulo-edicao">
        <h2 id="titulo-edicao" class="mb-4">Editar Trem</h2>

        <?php if ($erro !== ""): ?>
            <div class="alert alert-danger" role="alert">
                <?= trem_h($erro) ?>
            </div>
        <?php endif; ?>

        <div class="row align-items-center g-4">

            <div class="col-md-5 text-center">
                <img src="../assets/img/trem.png.jpeg"
                     alt="Trem"
                     class="imagem-trem">
            </div>

            <div class="col-md-7">
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

                    <div class="mb-3">
                        <?php if ($trem["id_rota"]): ?>
                            <p class="mb-1"><strong>Rota atual</strong>
                                <a href="tela_rotas.php?id=<?= (int)$trem["id_rota"] ?>"><?= trem_h($trem["codigo_rota"]) ?></a>
                            </p>
                            <div class="form-text">Encerre a rota atual para liberar o trem.</div>
                        <?php else: ?>
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
                        <?php endif; ?>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary">
                        Salvar alterações
                    </button>

                    <a href="visualizar_trem.php?id=<?= (int)$id ?>"
                       class="btn btn-secondary">
                        Cancelar
                    </a>
                    </div>

                </form>
            </div>

        </div>
        </section>
    </div>
</main>
</body>
</html>