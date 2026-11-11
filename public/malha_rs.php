<section class="resumo-rotas mt-4" aria-labelledby="titulo-malha-rs" data-malha-rs>
    <h2 id="titulo-malha-rs" class="h5 fw-bold mb-2">Linhas ferroviárias do Rio Grande do Sul</h2>
    <p class="small text-muted mb-3">
        Traçados aproximados de corredores da Malha Sul.
        <a href="https://www.gov.br/antt/pt-br/assuntos/ferrovias/concessoes-ferroviarias/rumo-malha-sul-s-a"
           target="_blank" rel="noopener noreferrer">Fonte dos corredores: ANTT</a>
    </p>
    <div class="mb-3" data-malha-filtro hidden>
        <label for="linha-malha-rs" class="form-label">Linha ferroviária</label>
        <select id="linha-malha-rs" class="form-select">
            <option value="">Todas as linhas</option>
        </select>
    </div>
    <p class="small mb-0" role="status" data-malha-status></p>
    <div class="rounded border" style="height: clamp(320px, 52vh, 510px); z-index: 0"
         role="region" aria-label="Mapa geográfico das linhas ferroviárias" data-malha-mapa hidden></div>
    <noscript><p class="mb-0">Ative o JavaScript para visualizar o mapa.</p></noscript>
</section>
