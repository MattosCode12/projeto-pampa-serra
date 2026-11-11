<section class="clima-home" id="clima-home" aria-label="Clima da sua cidade"
    data-cidade="<?= h($enderecoHome['cidade']) ?>" data-uf="<?= h($enderecoHome['uf']) ?>">
    <h2>Olá, <?= h($nome) ?></h2>
    <?php if ($enderecoHome['cidade'] !== '' && $enderecoHome['uf'] !== ''): ?>
        <p class="clima-local">Clima em <?= h($enderecoHome['cidade']) ?>, <?= h($enderecoHome['uf']) ?></p>
        <div class="clima-detalhes" data-clima-detalhes hidden>
            <span class="clima-icone" data-clima-icone aria-hidden="true"></span>
            <div><div class="clima-temperatura" data-clima-temperatura></div>
                <div class="clima-condicao" data-clima-condicao></div>
                <small class="text-muted" data-clima-sensacao></small>
            </div>
        </div>
        <p class="small text-muted mt-3 mb-0" role="status" data-clima-status>Consultando o clima da sua cidade…</p>
        <div class="clima-rodape">
            <a href="meu_endereco.php">Editar meu endereço</a>
            <button type="button" class="btn btn-outline-primary btn-sm" data-clima-atualizar>Atualizar clima</button>
        </div>
        <p class="clima-fonte text-muted mb-0">Dados meteorológicos por <a href="https://open-meteo.com/" target="_blank" rel="noopener">Open-Meteo</a>.</p>
        <noscript><p>Ative o JavaScript para consultar o clima.</p></noscript>
    <?php else: ?>
        <p class="text-muted">Complete seu endereço para ver o clima da sua cidade.</p>
        <a href="meu_endereco.php" class="btn btn-primary">Adicionar meu endereço</a>
    <?php endif; ?>
</section>
