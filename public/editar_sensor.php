<?php
require_once __DIR__ . "/../includes/bootstrap.php";
require_once __DIR__ . "/../includes/sensores.php";

exigir_admin();

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    redirecionar("public/sensores.php");
}

$stmt = $conexao->prepare(
    "SELECT nome, localizacao, tipo_dado, descricao FROM sensores WHERE id_sensor = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$sensor = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$sensor) {
    definir_aviso("erro", "Sensor não encontrado.");
    redirecionar("public/sensores.php");
}

$erro = "";
$nomeOriginal = $sensor["nome"];
$sensor["descricao"] = (string)$sensor["descricao"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    validar_csrf();

    $sensor = ler_sensor_post();
    $erro = validar_sensor($sensor);

    if ($erro === "") {
        try {
            $stmt = $conexao->prepare(
                "UPDATE sensores
                 SET nome = ?, localizacao = ?, tipo_dado = ?, descricao = ?
                 WHERE id_sensor = ?"
            );
            $stmt->bind_param(
                "ssssi",
                $sensor["nome"],
                $sensor["localizacao"],
                $sensor["tipo_dado"],
                $sensor["descricao"],
                $id
            );
            $stmt->execute();
            $stmt->close();

            definir_aviso("sucesso", "Sensor atualizado com sucesso.");
            redirecionar("public/tela_sensores.php?id=" . $id);
        } catch (mysqli_sql_exception $e) {
            error_log($e->getMessage());
            $erro = "Não foi possível salvar as alterações.";
        }
    }
}

pagina_inicio("Editar " . $nomeOriginal, [
    "ativo" => "sensores",
    "subtitulo" => "Atualize os dados do ponto de leitura.",
    "voltar" => "tela_sensores.php?id=" . $id,
]);

mostrar_erro($erro);
?>

<section class="faixa-azul">
    <form method="POST" class="painel formulario">
        <?= campo_csrf() ?>
        <?php campos_sensor($sensor); ?>

        <div class="form-acoes">
            <a href="tela_sensores.php?id=<?= (int)$id ?>" class="btn btn-contorno">Cancelar</a>
            <button type="submit" class="btn btn-primario">Salvar alterações</button>
        </div>
    </form>
</section>

<?php pagina_fim(); ?>
