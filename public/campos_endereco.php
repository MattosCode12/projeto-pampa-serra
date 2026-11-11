<fieldset class="endereco-usuario mb-4" data-endereco>
    <legend class="fs-5 fw-bold">Endereço</legend>
    <div class="row g-3">
        <div class="col-sm-6">
            <label for="cep" class="form-label">CEP</label>
            <div class="d-flex gap-2">
                <input id="cep" name="cep" class="form-control" inputmode="numeric"
                    autocomplete="postal-code" maxlength="9" pattern="[0-9]{5}-?[0-9]{3}"
                    placeholder="00000-000" value="<?= h($endereco['cep']) ?>"
                    <?= !empty($enderecoObrigatorio) ? 'required' : '' ?> aria-describedby="cep-status">
                <button type="button" class="btn btn-primary" data-consultar-cep>Consultar</button>
            </div>
        </div>
        <div class="col-12"><p id="cep-status" class="small text-muted mb-0" role="status" aria-live="polite">Informe o CEP para preencher o endereço. Você também pode completar os campos manualmente.</p></div>
        <?php foreach (['logradouro' => 'Rua', 'bairro' => 'Bairro', 'cidade' => 'Cidade', 'numero' => 'Número', 'complemento' => 'Complemento'] as $campo => $rotulo): ?>
            <div class="col-sm-6">
                <label for="<?= h($campo) ?>" class="form-label"><?= h($rotulo) ?></label>
                <input id="<?= h($campo) ?>" name="<?= h($campo) ?>" class="form-control"
                    maxlength="<?= endereco_campos()[$campo] ?>" value="<?= h($endereco[$campo]) ?>"
                    <?= $campo === 'cidade' && !empty($enderecoObrigatorio) ? 'required' : '' ?>>
            </div>
        <?php endforeach; ?>
        <div class="col-sm-6">
            <label for="uf" class="form-label">Estado</label>
            <select id="uf" name="uf" class="form-select" <?= !empty($enderecoObrigatorio) ? 'required' : '' ?>>
                <option value="">Selecione</option>
                <?php foreach (explode(' ', 'AC AL AP AM BA CE DF ES GO MA MT MS MG PA PB PR PE PI RJ RN RS RO RR SC SP SE TO') as $uf): ?>
                    <option value="<?= $uf ?>" <?= $endereco['uf'] === $uf ? 'selected' : '' ?>><?= $uf ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <small class="d-block mt-2 text-muted">Consulta de endereço por <a href="https://viacep.com.br/" target="_blank" rel="noopener">ViaCEP</a>.</small>
</fieldset>
