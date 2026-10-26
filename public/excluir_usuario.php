
<?php
require_once __DIR__ . "/verificar_acesso.php";
require_once __DIR__ . "/../conexao.php";

exigir_admin();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: listar_cadastro.php");
    exit;
}

validar_csrf();

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
$idLogado = (int)($_SESSION["usuario_id"] ?? 0);

if (!$id || $id < 1) {
    $_SESSION["mensagem_erro"] = "Usuário inválido.";
    header("Location: listar_cadastro.php");
    exit;
}

if ($id === $idLogado) {
    $_SESSION["mensagem_erro"] =
        "Você não pode excluir sua própria conta.";
    header("Location: listar_cadastro.php");
    exit;
}

try {
    $stmt = $conexao->prepare(
        "SELECT id, nome, tipo_usuario
         FROM usuarios
         WHERE id = ?"
    );

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$usuario) {
        $_SESSION["mensagem_erro"] =
            "Usuário não encontrado.";
        header("Location: listar_cadastro.php");
        exit;
    }

    if ((int)$usuario["tipo_usuario"] === 1) {
        $_SESSION["mensagem_erro"] =
            "Contas de administradores não podem ser excluídas por esta tela.";
        header("Location: listar_cadastro.php");
        exit;
    }

    $stmt = $conexao->prepare(
        "SELECT COUNT(*) AS total
         FROM trens
         WHERE id_usuario = ?"
    );

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ((int)$resultado["total"] > 0) {
        $_SESSION["mensagem_erro"] =
            "Este usuário é responsável por um ou mais trens. Altere o responsável antes de excluir.";
        header("Location: listar_cadastro.php");
        exit;
    }

    $stmt = $conexao->prepare(
        "DELETE FROM usuarios
         WHERE id = ? AND tipo_usuario = 0"
    );

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $excluidos = $stmt->affected_rows;
    $stmt->close();

    if ($excluidos === 1) {
        $_SESSION["mensagem_sucesso"] =
            "Usuário excluído com sucesso!";
    } else {
        $_SESSION["mensagem_erro"] =
            "Não foi possível excluir o usuário.";
    }

} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());

    $_SESSION["mensagem_erro"] =
        "Não foi possível excluir o usuário. Verifique se existem registros vinculados.";
}

header("Location: listar_cadastro.php");
exit;
