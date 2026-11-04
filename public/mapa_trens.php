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
    <style>
        body { background: #f3f3f3; }
        .topo { background: #174eae; color: white; padding: 25px; }
        .painel { background: white; padding: 25px; border-radius: 15px; }
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
    </style>
</head>
<body>

<header class="topo">
    <div class="container d-flex justify-content-between align-items-center">
        <h1>Mapa dos Trens</h1>
        <a href="trens.php" class="btn btn-light">Voltar aos Trens</a>
    </div>
</header>

<main class="container py-4">
    <section class="painel">

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

        <svg id="mapa" viewBox="0 0 1000 420" role="img" aria-label="Mapa da rota">
            <path id="trilhoBase" class="trilho-base"
                  d="M60,340 C220,340 200,140 400,170 S620,340 780,220 S900,90 950,90"/>
            <path id="trilhoVia" class="trilho-via"
                  d="M60,340 C220,340 200,140 400,170 S620,340 780,220 S900,90 950,90"/>
            <g id="camadaEstacoes"></g>
            <g id="camadaTrens"></g>
        </svg>

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
</main>

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

ESTACOES.forEach((e, i) => {
    const p = pontoNoKm(e.km);
    const g = el("g", { class: "estacao" });
    g.appendChild(el("circle", { cx: p.x, cy: p.y, r: 7 }));
    const acima = i % 2 === 0;
    g.appendChild(el("text", { x: p.x, y: p.y + (acima ? -20 : 28), "text-anchor": "middle" }, e.nome));
    g.appendChild(el("text", { x: p.x, y: p.y + (acima ? -8 : 40), "text-anchor": "middle", class: "km" }, "KM " + e.km));
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