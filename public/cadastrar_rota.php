<?php
require_once __DIR__ . "/../includes/bootstrap.php";
require_once __DIR__ . "/../includes/rotas.php";

exigir_admin();

$erro = "";
$rota = [
    "codigo" => "",
    "origem" => "",
    "destino" => "",
    "parada" => "",
    "velocidade_min" => "",
    "velocidade_max" => "",
    "consumo_min" => "",
    "consumo_max" => "",
    "ativa" => "1",
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    validar_csrf();

    $rota = ler_rota_post();
    $erro = validar_rota($rota);

    if ($erro === "") {
        try {
            $valores = valores_rota($rota);
            $stmt = $conexao->prepare(
                "INSERT INTO rota
                    (codigo, origem, destino, parada,
                     velocidade_min, velocidade_max,
                     consumo_min, consumo_max, ativa)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("ssssddddi", ...$valores);
            $stmt->execute();
            $stmt->close();

            definir_aviso("sucesso", "Rota cadastrada com sucesso.");
            redirecionar("public/rotas.php");
        } catch (mysqli_sql_exception $e) {
            error_log($e->getMessage());
            $erro = $e->getCode() === 1062
                ? "Já existe uma rota com este código."
                : "Não foi possível cadastrar a rota.";
        }
    }
}

pagina_inicio("Nova rota", [
    "ativo" => "rotas",
    "subtitulo" => "Cadastre um trajeto e seus limites de operação.",
    "voltar" => "rotas.php",
]);

mostrar_erro($erro);
?>

<section class="faixa-azul">
    <form method="POST" class="painel formulario">
        <?= campo_csrf() ?>
        <?php campos_rota($rota); ?>

        <div class="form-acoes">
            <a href="rotas.php" class="btn btn-contorno">Cancelar</a>
            <button type="submit" class="btn btn-primario">Cadastrar rota</button>
        </div>
    </form>
</section>

<?php pagina_fim(); ?>
