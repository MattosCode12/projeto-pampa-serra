<?php
require_once __DIR__ . "/../includes/bootstrap.php";

exigir_login();

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    redirecionar("public/trens.php");
}

$stmt = $conexao->prepare(
    "SELECT t.id_trem, t.codigo, t.modelo, t.status,
            u.nome AS responsavel
     FROM trens t
     INNER JOIN usuarios u ON u.id = t.id_usuario
     WHERE t.id_trem = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$trem = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$trem) {
    definir_aviso("erro", "Trem não encontrado.");
    redirecionar("public/trens.php");
}

$acoes = eh_admin()
    ? '<a class="btn btn-claro" href="editar_trem.php?id=' . (int)$trem["id_trem"] . '">' .
      icone("editar") . " Editar</a>"
    : "";

pagina_inicio("Trem " . $trem["codigo"], [
    "ativo" => "trens",
    "subtitulo" => $trem["modelo"],
    "voltar" => "trens.php",
    "acoes" => $acoes,
]);
?>

<section class="faixa-azul">
    <div class="grade-paineis">
        <div class="painel">
            <h2>Dados do trem</h2>
            <span class="legenda">Informações cadastrais</span>

            <div class="detalhes">
                <div class="detalhe">
                    <span>Código</span>
                    <strong><?= h($trem["codigo"]) ?></strong>
                </div>
                <div class="detalhe">
                    <span>Modelo</span>
                    <strong><?= h($trem["modelo"]) ?></strong>
                </div>
                <div class="detalhe">
                    <span>Status</span>
                    <strong><?= selo($trem["status"], tom_status_trem($trem["status"])) ?></strong>
                </div>
                <div class="detalhe">
                    <span>Responsável</span>
                    <strong><?= h($trem["responsavel"]) ?></strong>
                </div>
            </div>
        </div>

        <div class="painel">
            <h2>Localização atual</h2>
            <span class="legenda">Monitoramento em tempo real</span>

            <div class="sem-dados">
                <b>Sem posição disponível</b>
                O trem ainda não está enviando dados de GPS ao sistema.
            </div>
        </div>
    </div>
</section>

<?php pagina_fim(); ?>
