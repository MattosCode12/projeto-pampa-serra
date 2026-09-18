<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Relatórios</title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<style>

  
  :root{
    --azul-claro: #1457c2;
    --azul-escuro: #0d3f8f;
    --fundo: #f4f8ff;
    --texto: #0e2340;
    --texto-suave: #5d738f;
    --borda: #c9d8ee;
    --sombra: 0 10px 24px rgba(13, 63, 143, 0.18);
  }

  @media (prefers-color-scheme: dark) {
    :root:not([data-theme="light"]) {
      --fundo: #0f1c30;
      --texto: #eaf1ff;
      --texto-suave: #9db2d1;
      --borda: #274a7c;
    }
  }
  :root[data-theme="dark"] {
    --fundo: #0f1c30;
    --texto: #eaf1ff;
    --texto-suave: #9db2d1;
    --borda: #274a7c;
  }

  * {
    box-sizing: border-box;
  }

  body {
    margin: 0;
    padding: 28px 16px 60px;
    background: var(--fundo);
    color: var(--texto);
    font-family: Arial, sans-serif;
  }

  .app {
    max-width: 980px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 18px;
  }

  button {
    cursor: pointer;
    font-family: inherit;
  }

  
  .faixa-azul {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px;
    border-radius: 28px;
    background: linear-gradient(180deg, var(--azul-claro), var(--azul-escuro));
    box-shadow: var(--sombra);
  }

  
  .btn-voltar {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    border: none;
    border-radius: 12px;
    background: #ffffff;
    font-weight: 700;
  }

  
  .busca {
    flex: 1;
    max-width: 420px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 16px;
    border-radius: 999px;
    background: #ffffff;
  }

  .busca input {
    flex: 1;
    border: none;
    outline: none;
    background: transparent;
    font-size: 14px;
  }

  
  .seta {
    flex-shrink: 0;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    border-radius: 50%;
    background: #ffffff;
    color: var(--azul-claro);
    font-size: 18px;
  }

 
  .lista-cards {
    flex: 1;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
  }

  .card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    min-height: 96px;
    padding: 10px;
    text-align: center;
    background: #ffffff;
    border: 1px dashed var(--borda);
    border-radius: 16px;
  }

  .card b {
    font-size: 12px;
    color: var(--texto-suave);
  }

  .card span {
    font-size: 10px;
    color: var(--texto-suave);
    opacity: 0.75;
  }

  .btn-novo {
    flex-shrink: 0;
    width: 56px;
    height: 56px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    border-radius: 14px;
    background: #ffffff;
    color: var(--azul-claro);
    font-size: 22px;
  }

  
  .grade-paineis {
    flex: 1;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
  }

  @media (max-width: 640px) {
    .grade-paineis {
      grid-template-columns: 1fr;
    }
    .lista-cards {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  .painel {
    display: flex;
    flex-direction: column;
    min-height: 190px;
    padding: 14px;
    background: #ffffff;
    border-radius: 16px;
  }

  .painel-cabecalho {
    display: flex;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 10px;
  }

  .painel-cabecalho h3 {
    margin: 0 0 2px;
    font-size: 14px;
  }

  .painel-cabecalho h3 i {
    display: block;
    margin-top: 2px;
    font-style: normal;
    font-weight: 400;
    font-size: 10px;
    color: var(--texto-suave);
  }

  
  .botoes-exportar {
    display: flex;
    gap: 6px;
  }

  .botoes-exportar button {
    padding: 5px 9px;
    border: 1px solid var(--borda);
    border-radius: 8px;
    background: transparent;
    color: var(--azul-claro);
    font-size: 10px;
    font-weight: 700;
  }

  .botoes-exportar button:hover {
    background: var(--fundo);
  }

  
  .sem-dados {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
    padding: 16px;
    text-align: center;
    color: var(--texto-suave);
    border: 1.5px dashed var(--borda);
    border-radius: 12px;
  }

  .sem-dados b {
    font-size: 12px;
  }

  .sem-dados span {
    font-size: 10.5px;
    opacity: 0.8;
  }

  
  .rodape {
    display: flex;
    justify-content: center;
    gap: 12px;
  }

  .btn-principal {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 11px 22px;
    border: none;
    border-radius: 999px;
    background: var(--azul-claro);
    color: #ffffff;
    font-size: 13px;
    font-weight: 700;
    box-shadow: 0 6px 16px rgba(20, 87, 194, 0.35);
  }

  .btn-principal.contorno {
    background: #ffffff;
    color: var(--azul-claro);
    border: 1.5px solid var(--azul-claro);
    box-shadow: none;
  }

  
  .aviso {
    position: fixed;
    left: 50%;
    bottom: 22px;
    transform: translateX(-50%) translateY(20px);
    padding: 10px 18px;
    border-radius: 999px;
    background: var(--texto);
    color: #ffffff;
    font-size: 13px;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.25s ease, transform 0.25s ease;
  }

  .aviso.mostrar {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
  }

</style>
</head>
<body>
<div class="app">

  
  <div class="faixa-azul">
    <button class="btn-voltar" onclick="history.back()">← Voltar</button>
    <div class="busca">
      🔍
      <input id="campo-busca" placeholder="Buscar relatório..." oninput="filtrarCards()">
    </div>
    <div style="flex:1; max-width:60px"></div>
  </div>

  
  <div class="faixa-azul">
    <button class="seta" onclick="rolarCards(-1)">‹</button>
    <div class="lista-cards" id="lista-cards"></div>
    <button class="seta" onclick="rolarCards(1)">›</button>
    <button class="btn-novo" onclick="mostrarAviso('Criar novo relatório')">+</button>
  </div>

 
  <div class="faixa-azul">
    <button class="seta" onclick="mostrarAviso('Grupo anterior de relatórios')">‹</button>
    <div class="grade-paineis" id="grade-paineis"></div>
    <button class="seta" onclick="mostrarAviso('Próximo grupo de relatórios')">›</button>
  </div>

  
  <div class="rodape">
    <button class="btn-principal contorno" onclick="exportarCSV('todos')">⬇ Exportar tudo em CSV</button>
    <button class="btn-principal" onclick="exportarPDF('todos')">⬇ Exportar tudo em PDF</button>
  </div>

</div>

<div class="aviso" id="aviso"></div>

<script>

  
  const relatorios = [
    { chave: 'velocidade',    titulo: 'Velocidade das rotas',    legenda: 'Sem período selecionado', mensagem: 'Selecione um período para carregar o relatório' },
    { chave: 'pontualidade',  titulo: 'Pontualidade e padrões',  legenda: 'Últimas 24h',              mensagem: 'Aguardando geração do relatório' },
    { chave: 'status',        titulo: 'Status operacional',      legenda: 'Tempo real',               mensagem: 'Sem eventos registrados' },
    { chave: 'rotas',         titulo: 'Relatório de rotas',      legenda: 'Todas as rotas',            mensagem: 'Gere um relatório para visualizar aqui' }
  ];

  
  document.getElementById('lista-cards').innerHTML = [1, 2, 3, 4].map(numero => `
    <div class="card" data-nome="Relatório ${numero}">
      <b>Relatório ${numero}</b>
      <span>Sem dados</span>
    </div>
  `).join('');


  document.getElementById('grade-paineis').innerHTML = relatorios.map(r => `
    <div class="painel">
      <div class="painel-cabecalho">
        <h3>${r.titulo}<i>${r.legenda}</i></h3>
        <div class="botoes-exportar">
          <button onclick="exportarCSV('${r.chave}')">CSV</button>
          <button onclick="exportarPDF('${r.chave}')">PDF</button>
        </div>
      </div>
      <div class="sem-dados">
        <b>Nenhum dado disponível</b>
        <span>${r.mensagem}</span>
      </div>
    </div>
  `).join('');

  
  const nomesDosRelatorios = Object.fromEntries(
    relatorios.map(r => [r.chave, r.titulo]).concat([['todos', 'Todos os relatórios']])
  );

  
  function mostrarAviso(mensagem) {
    const aviso = document.getElementById('aviso');
    aviso.textContent = mensagem;
    aviso.classList.add('mostrar');
    clearTimeout(window.temporizadorAviso);
    window.temporizadorAviso = setTimeout(() => aviso.classList.remove('mostrar'), 2200);
  }

  
  function rolarCards(direcao) {
    document.getElementById('lista-cards').scrollBy({ left: direcao * 160, behavior: 'smooth' });
  }

  
  function filtrarCards() {
    const termo = document.getElementById('campo-busca').value.trim().toLowerCase();
    document.querySelectorAll('.card').forEach(card => {
      const nome = card.dataset.nome.toLowerCase();
      card.style.display = nome.includes(termo) ? '' : 'none';
    });
  }

  
  function exportarCSV(chave) {
    try {
      const nome = nomesDosRelatorios[chave] || chave;
      const linhas = [
        ['Relatorio', nome],
        ['Gerado em', new Date().toLocaleString('pt-BR')],
        [],
        ['Data', 'Rota', 'Valor', 'Status']
      ];
      const conteudoCSV = linhas.map(linha => linha.join(',')).join('\n');
      const url = URL.createObjectURL(new Blob([conteudoCSV], { type: 'text/csv;charset=utf-8;' }));

      const link = document.createElement('a');
      link.href = url;
      link.download = `${chave}-relatorio.csv`;
      document.body.appendChild(link);
      link.click();
      link.remove();
      URL.revokeObjectURL(url);

      mostrarAviso('CSV exportado: ' + nome);
    } catch (erro) {
      mostrarAviso('Não foi possível exportar o CSV');
    }
  }

  function exportarPDF(chave) {
    try {
      const nome = nomesDosRelatorios[chave] || chave;
      const { jsPDF } = window.jspdf;
      const documento = new jsPDF();

      documento.setFontSize(16);
      documento.text(nome, 14, 20);

      documento.setFontSize(10);
      documento.setTextColor(120);
      documento.text('Gerado em ' + new Date().toLocaleString('pt-BR'), 14, 28);

      documento.setDrawColor(200);
      documento.line(14, 34, 196, 34);

      documento.setTextColor(90);
      documento.setFontSize(11);
      documento.text('Nenhum dado disponível para este período.', 14, 46);

      documento.save(`${chave}-relatorio.pdf`);
      mostrarAviso('PDF exportado: ' + nome);
    } catch (erro) {
      mostrarAviso('Não foi possível exportar o PDF');
    }
  }

</script>
</body>
</html>

