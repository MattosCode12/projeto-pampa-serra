<?php
require_once __DIR__ . "/../includes/bootstrap.php";
require_once __DIR__ . "/../includes/rotas.php";

exigir_admin();

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    redirecionar("public/rotas.php");
}

$stmt = $conexao->prepare(
    "SELECT codigo, origem, destino, parada,
            velocidade_min, velocidade_max, consumo_min, consumo_max, ativa
     FROM rota WHERE id_rota = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$rota = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$rota) {
    definir_aviso("erro", "Rota não encontrada.");
    redirecionar("public/rotas.php");
}

$erro = "";
$codigoOriginal = $rota["codigo"];
$rota["parada"] = (string)$rota["parada"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    validar_csrf();

    $rota = ler_rota_post();
    $erro = validar_rota($rota);

    if ($erro === "") {
        try {
            $valores = valores_rota($rota);
            $valores[] = $id;
            $stmt = $conexao->prepare(
                "UPDATE rota
                 SET codigo = ?, origem = ?, destino = ?, parada = ?,
                     velocidade_min = ?, velocidade_max = ?,
                     consumo_min = ?, consumo_max = ?, ativa = ?
                 WHERE id_rota = ?"
            );
            $stmt->bind_param("ssssddddii", ...$valores);
            $stmt->execute();
            $stmt->close();

            definir_aviso("sucesso", "Rota atualizada com sucesso.");
            redirecionar("public/tela_rotas.php?id=" . $id);
        } catch (mysqli_sql_exception $e) {
            error_log($e->getMessage());
            $erro = $e->getCode() === 1062
                ? "Já existe uma rota com este código."
                : "Não foi possível salvar as alterações.";
        }
    }
}

pagina_inicio("Editar rota " . $codigoOriginal, [
    "ativo" => "rotas",
    "subtitulo" => "Atualize o trajeto e os limites de operação.",
    "voltar" => "tela_rotas.php?id=" . $id,
]);

mostrar_erro($erro);
?>

<section class="faixa-azul">
    <form method="POST" class="painel formulario">
        <?= campo_csrf() ?>
        <?php campos_rota($rota); ?>

        <div class="form-acoes">
            <a href="tela_rotas.php?id=<?= (int)$id ?>" class="btn btn-contorno">Cancelar</a>
            <button type="submit" class="btn btn-primario">Salvar alterações</button>
        </div>
    </form>
</section>

<?php pagina_fim(); ?>
