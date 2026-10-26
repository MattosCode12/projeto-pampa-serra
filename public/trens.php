<?php
require_once __DIR__ . "/../includes/bootstrap.php";

exigir_login();
$admin = eh_admin();
$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    exigir_admin();
    validar_csrf();

    $id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

    if (($_POST["acao"] ?? "") !== "excluir" || !$id || $id < 1) {
        $erro = "Trem inválido.";
    } else {
        try {
            $stmt = $conexao->prepare("DELETE FROM trens WHERE id_trem = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            definir_aviso("sucesso", "Trem excluído com sucesso.");
            redirecionar("public/trens.php");
        } catch (mysqli_sql_exception $e) {
            error_log($e->getMessage());
            $erro = "Não foi possível excluir o trem.";
        }
    }
}

$busca = busca_get();
$termo = termo_like($busca);
$trens = [];

try {
    $stmt = $conexao->prepare(
        "SELECT t.id_trem, t.codigo, t.modelo, t.status,
                u.nome AS responsavel
         FROM trens t
         INNER JOIN usuarios u ON u.id = t.id_usuario
         WHERE t.codigo LIKE ? OR t.modelo LIKE ? OR u.nome LIKE ?
         ORDER BY t.codigo"
    );
    $stmt->bind_param("sss", $termo, $termo, $termo);
    $stmt->execute();
    $trens = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());
    $erro = "Não foi possível carregar os trens. Verifique se o banco foi atualizado (database/banco.sql).";
}

$porStatus = array_count_values(array_column($trens, "status"));

$acoes = $admin
    ? '<a class="btn btn-claro" href="cadastrar_trem.php">' . icone("mais") . " Novo trem</a>"
    : "";

pagina_inicio("Trens", [
    "ativo" => "trens",
    "subtitulo" => "Composições cadastradas e status operacional.",
    "voltar" => "home.php",
    "busca" => ["placeholder" => "Buscar por código, modelo ou responsável...", "valor" => $busca],
    "acoes" => $acoes,
]);

mostrar_erro($erro);
?>

<section class="faixa-azul">
    <div class="indicadores">
        <div class="indicador">
            <b>Trens</b>
            <strong><?= count($trens) ?></strong>
            <small>Na listagem</small>
        </div>
        <div class="indicador">
            <b>Normal</b>
            <strong><?= $porStatus["Normal"] ?? 0 ?></strong>
            <?= selo("Operação normal", "verde") ?>
        </div>
        <div class="indicador">
            <b>Alerta</b>
            <strong><?= $porStatus["Alerta"] ?? 0 ?></strong>
            <?= selo("Requer atenção", "amarelo") ?>
        </div>
        <div class="indicador">
            <b>Falha</b>
            <strong><?= $porStatus["Falha"] ?? 0 ?></strong>
            <?= selo("Fora de operação", "vermelho") ?>
        </div>
    </div>
</section>

<section class="faixa-azul">
    <div class="painel">
        <div class="painel-cabecalho">
            <div>
                <h2>Trens cadastrados</h2>
                <span class="legenda"><?= count($trens) ?> resultado(s)</span>
            </div>
            <a class="btn btn-contorno btn-pequeno" href="relatorios.php?exportar=trens">
                <?= icone("baixar") ?> CSV
            </a>
        </div>

        <div class="tabela-rolagem">
            <table class="tabela">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Modelo</th>
                        <th>Status</th>
                        <th>Responsável</th>
                        <th class="numero">Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($trens as $trem): ?>
                    <tr>
                        <td><strong><?= h($trem["codigo"]) ?></strong></td>
                        <td><?= h($trem["modelo"]) ?></td>
                        <td><?= selo($trem["status"], tom_status_trem($trem["status"])) ?></td>
                        <td><?= h($trem["responsavel"]) ?></td>
                        <td>
                            <div class="acoes">
                                <a class="btn btn-contorno btn-pequeno" title="Ver trem"
                                   href="visualizar_trem.php?id=<?= (int)$trem["id_trem"] ?>">
                                    <?= icone("ver") ?> <span>Ver</span>
                                </a>
                                <?php if ($admin): ?>
                                    <a class="btn btn-contorno btn-pequeno" title="Editar trem"
                                       href="editar_trem.php?id=<?= (int)$trem["id_trem"] ?>">
                                        <?= icone("editar") ?> <span>Editar</span>
                                    </a>
                                    <?php botao_post(
                                        "excluir",
                                        $trem["id_trem"],
                                        "Excluir",
                                        "lixeira",
                                        "btn-perigo",
                                        "Deseja excluir o trem " . $trem["codigo"] . "?"
                                    ); ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$trens): ?>
                    <tr>
                        <td colspan="5" class="vazio">Nenhum trem encontrado.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php pagina_fim(); ?>
