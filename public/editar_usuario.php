<?php
require_once __DIR__ . "/../includes/bootstrap.php";
require_once __DIR__ . "/../includes/usuarios.php";

$logado = exigir_admin();

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    redirecionar("public/listar_cadastro.php");
}

$stmt = $conexao->prepare(
    "SELECT nome, email, telefone, tipo_usuario FROM usuarios WHERE id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$usuario) {
    definir_aviso("erro", "Usuário não encontrado.");
    redirecionar("public/listar_cadastro.php");
}

$erro = "";
$nomeOriginal = $usuario["nome"];
$proprioUsuario = $id === (int)$logado["id"];
$usuario["telefone"] = (string)$usuario["telefone"];
$usuario["tipo_usuario"] = (string)$usuario["tipo_usuario"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    validar_csrf();

    $usuario = ler_usuario_post();
    $senha = $_POST["senha"] ?? "";
    $erro = validar_usuario($usuario, $senha, $_POST["confirmar_senha"] ?? "", false);

    // Impede que o administrador tire o próprio acesso e fique sem
    // ninguém para administrar o sistema.
    if ($erro === "" && $proprioUsuario && $usuario["tipo_usuario"] !== "1") {
        $erro = "Você não pode remover o seu próprio perfil de administrador.";
    }

    if ($erro === "") {
        $tipo = (int)$usuario["tipo_usuario"];
        $telefone = $usuario["telefone"] !== "" ? $usuario["telefone"] : null;

        try {
            if ($senha === "") {
                $stmt = $conexao->prepare(
                    "UPDATE usuarios
                     SET nome = ?, email = ?, telefone = ?, tipo_usuario = ?
                     WHERE id = ?"
                );
                $stmt->bind_param("sssii", $usuario["nome"], $usuario["email"], $telefone, $tipo, $id);
            } else {
                $hash = password_hash($senha, PASSWORD_DEFAULT);
                $stmt = $conexao->prepare(
                    "UPDATE usuarios
                     SET nome = ?, email = ?, telefone = ?, tipo_usuario = ?, senha = ?
                     WHERE id = ?"
                );
                $stmt->bind_param("sssisi", $usuario["nome"], $usuario["email"], $telefone, $tipo, $hash, $id);
            }

            $stmt->execute();
            $stmt->close();

            definir_aviso("sucesso", "Usuário atualizado com sucesso.");
            redirecionar("public/listar_cadastro.php");
        } catch (mysqli_sql_exception $e) {
            error_log($e->getMessage());
            $erro = $e->getCode() === 1062
                ? "Já existe um usuário com este e-mail."
                : "Não foi possível salvar as alterações.";
        }
    }
}

pagina_inicio("Editar " . $nomeOriginal, [
    "ativo" => "usuarios",
    "subtitulo" => $proprioUsuario
        ? "Você está editando a sua própria conta."
        : "Atualize os dados e o perfil de acesso.",
    "voltar" => "listar_cadastro.php",
]);

mostrar_erro($erro);
?>

<section class="faixa-azul">
    <form method="POST" class="painel formulario">
        <?= campo_csrf() ?>
        <?php campos_usuario($usuario, true); ?>

        <div class="form-acoes">
            <a href="listar_cadastro.php" class="btn btn-contorno">Cancelar</a>
            <button type="submit" class="btn btn-primario">Salvar alterações</button>
        </div>
    </form>
</section>

<?php pagina_fim(); ?>
