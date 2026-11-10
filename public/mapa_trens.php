<?php
require_once __DIR__ . "/verificar_acesso.php";
require_once __DIR__ . "/../conexao.php";

exigir_login();

function trem_h($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, "UTF-8");
}

$trens = $conexao->query(
    "SELECT t.id_trem, t.codigo, t.modelo, t.status, u.nome AS responsavel
     FROM trens t
     INNER JOIN usuarios u ON u.id = t.id_usuario
     ORDER BY t.codigo"
)->fetch_all(MYSQLI_ASSOC);

$rotas = $conexao->query(
    "SELECT id_rota, codigo, origem, destino, parada,
            velocidade_min, velocidade_max
     FROM rota WHERE ativa = 1 ORDER BY codigo"
)->fetch_all(MYSQLI_ASSOC);

$sensoresBD = $conexao->query(
    "SELECT nome, localizacao FROM sensores ORDER BY id_sensor"
)->fetch_all(MYSQLI_ASSOC);

$estacoes = [];
foreach ($sensoresBD as $s) {
    if (preg_match('/KM\s*(\d+)\s*-\s*(.+)/u', $s["localizacao"], $m)) {
        $estacoes[] = [
            "km"     => (int)$m[1],
            "nome"   => trim($m[2]),
            "sensor" => $s["nome"],
        ];
    }
}
usort($estacoes, fn($a, $b) => $a["km"] <=> $b["km"]);

$flags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mapa dos Trens - Pampa Serra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/style.css">
    <link rel="stylesheet" href="../style/padrao-paginas.css">
    <style>
        .ps-mapa .ps-moldura { width: 1140px; }
        .btn-azul { background: #0755bc; color: white; }
        .btn-azul:hover { background: #08469a; color: white; }
        #mapa { width: 100%; height: auto; background: #eaf3e4;
                border-radius: 12px; border: 1px solid #cfd8c8; }
        .trilho-base { fill: none; stroke: #6b6b6b; stroke-width: 10; stroke-linecap: round; }
        .trilho-via  { fill: none; stroke: #d9d9d9; stroke-width: 4;
                       stroke-dasharray: 8 6; stroke-linecap: round; }
        .estacao circle { fill: #fff; stroke: #174eae; stroke-width: 3; }
        .estacao text { font-size: 12px; fill: #222; font-family: sans-serif; }
        .estacao .km { font-size: 10px; fill: #666; }
        .trem { cursor: pointer; }
        .trem text { font-size: 11px; font-weight: bold; fill: #111;
                     font-family: sans-serif; paint-order: stroke;
                     stroke: #fff; stroke-width: 3px; }
        .legenda span { display: inline-block; width: 12px; height: 12px;
                        border-radius: 50%; margin-right: 4px; }
        .estacao { cursor: pointer; }
        .estacao:hover circle:not(.alvo-estacao), .estacao:focus circle:not(.alvo-estacao) { fill: #b9d5ff; stroke-width: 5; }
        .estacao circle.alvo-estacao { fill: transparent; stroke: none; }
        #cidadeModal { width: min(620px, calc(100% - 24px)); max-height: 90dvh; padding: 0; border: 0; border-radius: 20px; box-shadow: 0 20px 70px #102b5766; color: #20314c; }
        #cidadeModal::backdrop { background: #102b57aa; backdrop-filter: blur(4px); }
        .cidade-cabecalho { display: flex; justify-content: space-between; align-items: center; gap: 16px; background: #174eae; color: white; padding: 22px 24px; }
        .cidade-cabecalho h2 { font-size: 28px; margin: 4px 0 0; }
        #cidadeFechar { flex-shrink: 0; width: 44px; height: 44px; border: 1px solid #ffffff88; border-radius: 50%; background: #15376b; color: white; font-size: 28px; }
        .cidade-conteudo { padding: 24px; }
        #cidadeFoto { width: 100%; max-height: 320px; object-fit: cover; border-radius: 12px; margin-bottom: 18px; }
        #cidadeDescricao { line-height: 1.7; }
        #cidadeEstado { color: #516078; }

        .mapa-rolagem { overflow-x: auto; border-radius: 12px; }
        #mapa { display: block; min-width: 900px; }
        .ligacao-estacao { fill: none; stroke: #718ba7; stroke-width: 1.5; stroke-dasharray: 4 4; pointer-events: none; }
        .rotulo-estacao { fill: #fff; stroke: #a9bdd7; stroke-width: 1.5; }
        .estacao .nome-estacao { font-size: 13px; font-weight: 600; fill: #15376b; }
        .estacao .km { font-size: 11px; }
        .estacao:hover .rotulo-estacao, .estacao:focus .rotulo-estacao { fill: #e9f2ff; stroke: #174eae; stroke-width: 2.5; }
    </style>
</head>
<body class="ps-pagina ps-mapa">

<header class="ps-cabecalho">
    <div class="ps-cabecalho-conteudo">
        <img src="../assets/img/logo.png" alt="Pampa Serra" class="ps-logo">
        <h1 class="ps-titulo">Mapa dos Trens</h1>
        <a href="trens.php" class="ps-voltar" aria-label="Voltar aos trens cadastrados">
            <span aria-hidden="true">←</span> Voltar
        </a>
    </div>
</header>

<main class="ps-conteudo">
    <div class="ps-moldura">
    <section class="ps-painel" aria-label="Simulação das rotas dos trens">

        <div class="row g-3 mb-3 align-items-end">
            <div class="col-md-6">
                <label for="selRota" class="form-label fw-bold">Rota</label>
                <select id="selRota" class="form-select"></select>
            </div>
            <div class="col-md-3">
                <label for="selVel" class="form-label fw-bold">Velocidade da simulação</label>
                <select id="selVel" class="form-select">
                    <option value="30">Lenta</option>
                    <option value="60" selected>Normal</option>
                    <option value="120">Rápida</option>
                </select>
            </div>
            <div class="col-md-3">
                <button id="btnPausa" class="btn btn-azul w-100">Pausar</button>
            </div>
        </div>

        <div id="infoRota" class="alert alert-primary py-2"></div>

        <p class="small text-muted">Clique nas bolinhas das estações para conhecer cada cidade.</p>
        <div class="mapa-rolagem" tabindex="0" role="region" aria-label="Mapa das estações, com rolagem horizontal em telas pequenas">
        <svg id="mapa" viewBox="0 -100 1000 600" role="group" aria-label="Mapa interativo da rota. Selecione uma estação para conhecer a cidade.">
            <path id="trilhoBase" class="trilho-base"
                  d="M60,340 C220,340 200,140 400,170 S620,340 780,220 S900,90 950,90"/>
            <path id="trilhoVia" class="trilho-via"
                  d="M60,340 C220,340 200,140 400,170 S620,340 780,220 S900,90 950,90"/>
            <g id="camadaEstacoes"></g>
            <g id="camadaTrens"></g>
        </svg>
        </div>

        <div class="legenda mt-3 d-flex gap-4 flex-wrap">
            <div><span style="background:#198754"></span>Normal</div>
            <div><span style="background:#ffc107"></span>Alerta (anda mais devagar)</div>
            <div><span style="background:#dc3545"></span>Falha (parado)</div>
        </div>

        <div id="detalheTrem" class="mt-3"></div>

        <?php if (!$trens): ?>
            <p class="text-center text-muted mt-3">Nenhum trem cadastrado.</p>
        <?php endif; ?>
    </section>
    </div>
</main>

<dialog id="cidadeModal" aria-labelledby="cidadeTitulo">
    <header class="cidade-cabecalho">
        <div><small id="cidadeKm"></small><h2 id="cidadeTitulo"></h2></div>
        <button type="button" id="cidadeFechar" aria-label="Fechar detalhes da cidade" autofocus>×</button>
    </header>
    <div class="cidade-conteudo">
        <p id="cidadeEstado" role="status">Carregando informações…</p>
        <img id="cidadeFoto" hidden alt="">
        <p id="cidadeDescricao"></p>
        <div class="d-flex flex-wrap gap-3 small">
            <a id="cidadeFonte" target="_blank" rel="noopener noreferrer" hidden>Sobre a cidade · Wikipédia</a>
        </div>
        <button id="cidadeTentar" type="button" class="btn btn-primary mt-3" hidden>Tentar novamente</button>
    </div>
</dialog>

<script>
const TRENS    = <?= json_encode($trens,    $flags) ?>;
const ROTAS    = <?= json_encode($rotas,    $flags) ?>;
const ESTACOES = <?= json_encode($estacoes, $flags) ?>;

const KM_TOTAL = ESTACOES.length ? Math.max(...ESTACOES.map(e => e.km)) : 120;
const NS = "http://www.w3.org/2000/svg";
const COR = { Normal: "#198754", Alerta: "#ffc107", Falha: "#dc3545" };

const base = document.getElementById("trilhoBase");
const compr = base.getTotalLength();
const camadaEst = document.getElementById("camadaEstacoes");
const camadaTrens = document.getElementById("camadaTrens");
const selRota = document.getElementById("selRota");
const selVel = document.getElementById("selVel");
const btnPausa = document.getElementById("btnPausa");
const infoRota = document.getElementById("infoRota");
const detalhe = document.getElementById("detalheTrem");

function el(tag, attrs = {}, texto) {
    const e = document.createElementNS(NS, tag);
    for (const k in attrs) e.setAttribute(k, attrs[k]);
    if (texto !== undefined) e.textContent = texto;
    return e;
}

function esc(s) {
    const d = document.createElement("div");
    d.textContent = s ?? "";
    return d.innerHTML;
}

function pontoNoKm(km) {
    return base.getPointAtLength((km / KM_TOTAL) * compr);
}

const cidadeModal = document.getElementById('cidadeModal');
const cidadeFoto = document.getElementById('cidadeFoto');
const cidadeEstado = document.getElementById('cidadeEstado');
const cidadeDescricao = document.getElementById('cidadeDescricao');
const cidadeFonte = document.getElementById('cidadeFonte');
const cidadeTentar = document.getElementById('cidadeTentar');
const cidadesCache = new Map();
// Títulos explícitos evitam confundir cidades com palavras ou outros lugares.
const cidadesWiki = {
    'Porto Alegre': 'Porto Alegre', 'Aeroporto': 'Porto Alegre',
    'São Leopoldo': 'São Leopoldo', 'Novo Hamburgo': 'Novo Hamburgo',
    'Sapiranga': 'Sapiranga', 'Parobé': 'Parobé', 'Taquara': 'Taquara',
    'Igrejinha': 'Igrejinha', 'Três Coroas': 'Três Coroas',
    'Canela': 'Canela (Rio Grande do Sul)', 'Gramado': 'Gramado'
};
let cidadePedido = 0;
let cidadeAtual = null;
let cidadeGatilho = null;
let cidadeController = null;

async function abrirCidade(estacao, gatilho) {
    cidadeAtual = estacao;
    cidadeGatilho = gatilho;
    const pedido = ++cidadePedido;
    cidadeController?.abort();
    cidadeController = new AbortController();
    document.getElementById('cidadeTitulo').textContent = estacao.nome;
    document.getElementById('cidadeKm').textContent = 'Conheça a parada · KM ' + estacao.km;
    cidadeFoto.hidden = cidadeFonte.hidden = cidadeTentar.hidden = true;
    cidadeFoto.removeAttribute('src');
    cidadeDescricao.textContent = '';
    cidadeEstado.textContent = 'Carregando foto e informações…';
    cidadeEstado.hidden = false;
    if (!cidadeModal.open) cidadeModal.showModal();
    const titulo = cidadesWiki[estacao.nome];
    if (!titulo) {
        cidadeEstado.textContent = 'Ainda não há informações turísticas cadastradas para esta parada.';
        return;
    }
    const temporizador = setTimeout(() => cidadeController?.abort(), 12000);
    try {
        let pagina = cidadesCache.get(titulo);
        if (!pagina) {
            const params = new URLSearchParams({ action: 'query', format: 'json', formatversion: '2',
                origin: '*', prop: 'extracts|pageimages', titles: titulo, redirects: '1',
                exintro: '1', explaintext: '1', exsentences: '3', piprop: 'thumbnail', pithumbsize: '900' });
            const resposta = await fetch('https://pt.wikipedia.org/w/api.php?' + params, { signal: cidadeController.signal });
            if (!resposta.ok) throw new Error('Consulta indisponível');
            const dados = await resposta.json();
            pagina = dados.query?.pages?.[0];
            if (!pagina || pagina.missing || !pagina.extract) throw new Error('Cidade indisponível');
            cidadesCache.set(titulo, pagina);
        }
        if (pedido !== cidadePedido || !cidadeModal.open) return;
        cidadeDescricao.textContent = pagina.extract;
        cidadeFonte.href = 'https://pt.wikipedia.org/wiki/' + encodeURIComponent(pagina.title.replaceAll(' ', '_'));
        cidadeFonte.hidden = false;
        const foto = pagina.thumbnail?.source;
        if (foto && new URL(foto).protocol === 'https:' && ['upload.wikimedia.org', 'thumb.wikimedia.org'].includes(new URL(foto).hostname)) {
            cidadeFoto.alt = 'Vista de ' + pagina.title;
            cidadeFoto.onload = () => { if (pedido === cidadePedido) { cidadeFoto.hidden = false; cidadeEstado.hidden = true; } };
            cidadeFoto.onerror = () => { if (pedido === cidadePedido) { cidadeFoto.hidden = true; cidadeEstado.hidden = false; cidadeEstado.textContent = 'A foto não pôde ser carregada.'; } };
            cidadeFoto.src = foto;
            cidadeEstado.textContent = 'Carregando foto…';
        } else {
            cidadeEstado.textContent = 'Foto indisponível para esta cidade.';
        }
    } catch (erro) {
        if (pedido !== cidadePedido || !cidadeModal.open) return;
        cidadeEstado.textContent = 'Não foi possível carregar as informações. Confira sua conexão e tente novamente.';
        cidadeTentar.hidden = false;
    } finally { clearTimeout(temporizador); }
}
document.getElementById('cidadeFechar').addEventListener('click', () => cidadeModal.close());
cidadeModal.addEventListener('click', evento => {
    const r = cidadeModal.getBoundingClientRect();
    if (evento.target === cidadeModal && (evento.clientX < r.left || evento.clientX > r.right || evento.clientY < r.top || evento.clientY > r.bottom)) cidadeModal.close();
});
cidadeModal.addEventListener('close', () => { ++cidadePedido; cidadeController?.abort(); cidadeGatilho?.focus(); });
cidadeTentar.addEventListener('click', () => abrirCidade(cidadeAtual, cidadeGatilho));


ESTACOES.forEach((e, i) => {
    const p = pontoNoKm(e.km);
    const g = el("g", { class: "estacao", role: "button", tabindex: "0", "aria-haspopup": "dialog", "aria-label": "Conhecer " + e.nome + ", KM " + e.km });
    g.appendChild(el("circle", { cx: p.x, cy: p.y, r: 18, class: "alvo-estacao" }));
    g.addEventListener("click", () => abrirCidade(e, g));
    g.addEventListener("keydown", evento => {
        if (evento.key === "Enter" || evento.key === " ") {
            evento.preventDefault();
            abrirCidade(e, g);
        }
    });
    const acima = i % 2 === 0;
    const quantidade = Math.ceil((ESTACOES.length - (acima ? 0 : 1)) / 2);
    const indice = Math.floor(i / 2);
    const largura = Math.min(150, 940 / Math.max(quantidade, 1) - 10);
    const x = quantidade <= 1 ? 500 : 90 + indice * 820 / (quantidade - 1);
    const y = acima ? -60 : 440;
    const borda = acima ? y + 24 : y - 24;
    g.appendChild(el("path", {
        d: `M${p.x},${p.y} L${p.x},${acima ? 60 : 385} L${x},${borda}`,
        class: "ligacao-estacao"
    }));
    g.appendChild(el("rect", { x: x - largura / 2, y: y - 24,
        width: largura, height: 48, rx: 10, class: "rotulo-estacao" }));
    const nome = el("text", { x, y: y - 3, "text-anchor": "middle", class: "nome-estacao" }, e.nome);
    g.appendChild(nome);
    g.appendChild(el("text", { x, y: y + 14, "text-anchor": "middle", class: "km" }, "KM " + e.km));
    // A bolinha fica acima da linha de ligação e continua clicável.
    g.appendChild(el("circle", { cx: p.x, cy: p.y, r: 7 }));

    camadaEst.appendChild(g);
});

if (!ROTAS.length) {
    selRota.appendChild(new Option("Nenhuma rota ativa", ""));
}
ROTAS.forEach(r => selRota.appendChild(new Option(r.codigo + " — " + r.origem + " → " + r.destino, r.id_rota)));

let rotaAtual = ROTAS[0] || null;

function mostrarInfoRota() {
    if (!rotaAtual) {
        infoRota.textContent = "Sem rota ativa. Mostrando simulação padrão.";
        return;
    }
    infoRota.innerHTML =
        "<strong>" + esc(rotaAtual.origem) + " → " + esc(rotaAtual.destino) + "</strong>" +
        (rotaAtual.parada ? " | Parada: " + esc(rotaAtual.parada) : "") +
        " | Velocidade: " + Number(rotaAtual.velocidade_min) + " a " +
        Number(rotaAtual.velocidade_max) + " km/h";
}

let objs = [];

function velocidadeBase() {
    if (!rotaAtual) return 60;
    const min = Number(rotaAtual.velocidade_min);
    const max = Number(rotaAtual.velocidade_max);
    return (min + max) / 2;
}

function criarTrens() {
    camadaTrens.innerHTML = "";
    objs = TRENS.map((t, i) => {
        const g = el("g", { class: "trem" });
        const corpo = el("rect", { x: -14, y: -8, width: 28, height: 16, rx: 4,
                                   fill: COR[t.status] || "#555", stroke: "#222", "stroke-width": 1.5 });
        const farol = el("rect", { x: 8, y: -5, width: 5, height: 10, fill: "#fff" });
        const rot = el("text", { x: 0, y: -14, "text-anchor": "middle" }, t.codigo);
        g.append(corpo, farol, rot);
        g.addEventListener("click", () => mostrarTrem(t));
        camadaTrens.appendChild(g);

        const v = velocidadeBase() * (0.85 + ((i * 37) % 30) / 100);
        return {
            t, g, rot,
            km: (KM_TOTAL / (TRENS.length + 1)) * (i + 1),
            dir: i % 2 === 0 ? 1 : -1,
            kmh: v
        };
    });
}

function mostrarTrem(t) {
    detalhe.innerHTML =
        '<div class="alert alert-secondary mb-0"><strong>' + esc(t.codigo) + "</strong> — " +
        esc(t.modelo) + " | Status: " + esc(t.status) +
        " | Responsável: " + esc(t.responsavel) +
        ' | <a href="visualizar_trem.php?id=' + Number(t.id_trem) + '">Ver detalhes</a></div>';
}

let pausado = false;
let ultimo = null;

function frame(agora) {
    if (ultimo === null) ultimo = agora;
    const dt = (agora - ultimo) / 1000;
    ultimo = agora;

    if (!pausado) {
        const fator = Number(selVel.value);
        objs.forEach(o => {
            let mult = 1;
            if (o.t.status === "Falha") mult = 0;
            else if (o.t.status === "Alerta") mult = 0.5;

            o.km += o.dir * (o.kmh * mult / 3600) * fator * dt;

            if (o.km >= KM_TOTAL) { o.km = KM_TOTAL; o.dir = -1; }
            if (o.km <= 0)        { o.km = 0;        o.dir = 1;  }
        });
    }

    objs.forEach(o => {
        const L = (o.km / KM_TOTAL) * compr;
        const p = base.getPointAtLength(L);
        const a = base.getPointAtLength(Math.min(L + 1, compr));
        const b = base.getPointAtLength(Math.max(L - 1, 0));
        let ang = Math.atan2(a.y - b.y, a.x - b.x) * 180 / Math.PI;
        if (o.dir === -1) ang += 180;
        o.g.setAttribute("transform", "translate(" + p.x + "," + p.y + ")");
        o.g.children[0].setAttribute("transform", "rotate(" + ang + ")");
        o.g.children[1].setAttribute("transform", "rotate(" + ang + ")");
    });

    requestAnimationFrame(frame);
}

selRota.addEventListener("change", () => {
    rotaAtual = ROTAS.find(r => String(r.id_rota) === selRota.value) || null;
    mostrarInfoRota();
    criarTrens();
});

btnPausa.addEventListener("click", () => {
    pausado = !pausado;
    btnPausa.textContent = pausado ? "Continuar" : "Pausar";
});

mostrarInfoRota();
criarTrens();
requestAnimationFrame(frame);
</script>
</body>
</html>