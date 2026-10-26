<?php
require_once __DIR__ . "/../includes/bootstrap.php";
require_once __DIR__ . "/../includes/trens.php";

exigir_admin();

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    redirecionar("public/trens.php");
}

$stmt = $conexao->prepare(
    "SELECT codigo, modelo, status, id_usuario FROM trens WHERE id_trem = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$trem = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$trem) {
    definir_aviso("erro", "Trem não encontrado.");
    redirecionar("public/trens.php");
}

$erro = "";
$codigoOriginal = $trem["codigo"];
$responsaveis = listar_responsaveis();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    validar_csrf();

    $trem = ler_trem_post();
    $erro = validar_trem($trem, $responsaveis);

    if ($erro === "") {
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

            definir_aviso("sucesso", "Trem atualizado com sucesso.");
            redirecionar("public/visualizar_trem.php?id=" . $id);
        } catch (mysqli_sql_exception $e) {
            error_log($e->getMessage());
            $erro = $e->getCode() === 1062
                ? "Já existe um trem com este código."
                : "Não foi possível salvar as alterações.";
        }
    }
}

pagina_inicio("Editar trem " . $codigoOriginal, [
    "ativo" => "trens",
    "subtitulo" => "Atualize os dados da composição.",
    "voltar" => "visualizar_trem.php?id=" . $id,
]);

mostrar_erro($erro);
?>

<section class="faixa-azul">
    <form method="POST" class="painel formulario">
        <?= campo_csrf() ?>
        <?php campos_trem($trem, $responsaveis); ?>

        <div class="form-acoes">
            <a href="visualizar_trem.php?id=<?= (int)$id ?>" class="btn btn-contorno">Cancelar</a>
            <button type="submit" class="btn btn-primario">Salvar alterações</button>
        </div>
    </form>
</section>

<?php pagina_fim(); ?>
