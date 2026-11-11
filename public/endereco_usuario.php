<?php
function endereco_campos(): array {
    return ['cep' => 8, 'logradouro' => 150, 'bairro' => 100,
        'cidade' => 100, 'uf' => 2, 'numero' => 20, 'complemento' => 100];
}

function endereco_ler(array $dados): array {
    $endereco = [];
    foreach (endereco_campos() as $campo => $limite) {
        $valor = $dados[$campo] ?? '';
        $endereco[$campo] = is_string($valor) ? trim($valor) : '';
    }
    $endereco['cep'] = str_replace(['-', ' '], '', $endereco['cep']);
    $endereco['uf'] = strtoupper($endereco['uf']);
    return $endereco;
}

function endereco_validar(array $endereco, bool $obrigatorio = true): string {
    if (!$obrigatorio && implode('', $endereco) === '') {
        return '';
    }
    foreach (endereco_campos() as $campo => $limite) {
        if (mb_strlen($endereco[$campo]) > $limite) {
            return 'O campo ' . $campo . ' do endereço ultrapassa o tamanho permitido.';
        }
    }
    if (!preg_match('/^[0-9]{8}$/D', $endereco['cep'])) {
        return 'Informe um CEP com 8 números.';
    }
    $ufs = explode(' ', 'AC AL AP AM BA CE DF ES GO MA MT MS MG PA PB PR PE PI RJ RN RS RO RR SC SP SE TO');
    if ($endereco['cidade'] === '' || !in_array($endereco['uf'], $ufs, true)) {
        return 'Preencha a cidade e selecione um estado válido.';
    }
    return '';
}

function endereco_carregar(mysqli $conexao, int $id): array {
    $stmt = $conexao->prepare('SELECT cep, logradouro, bairro, cidade, uf, numero, complemento FROM usuario_enderecos WHERE usuario_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $dados = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return endereco_ler($dados ?: []);
}

function endereco_salvar(mysqli $conexao, int $id, array $endereco): void {
    if (implode('', $endereco) === '') {
        return;
    }
    $stmt = $conexao->prepare(
        'INSERT INTO usuario_enderecos (usuario_id, cep, logradouro, bairro, cidade, uf, numero, complemento)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE cep = VALUES(cep), logradouro = VALUES(logradouro),
         bairro = VALUES(bairro), cidade = VALUES(cidade), uf = VALUES(uf),
         numero = VALUES(numero), complemento = VALUES(complemento)'
    );
    $stmt->bind_param('isssssss', $id, $endereco['cep'], $endereco['logradouro'],
        $endereco['bairro'], $endereco['cidade'], $endereco['uf'],
        $endereco['numero'], $endereco['complemento']);
    $stmt->execute();
    $stmt->close();
}
