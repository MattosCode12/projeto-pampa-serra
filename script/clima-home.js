(() => {
    const card = document.getElementById('clima-home');
    if (!card || !card.dataset.cidade || !card.dataset.uf) return;
    const status = card.querySelector('[data-clima-status]');
    const botao = card.querySelector('[data-clima-atualizar]');
    const detalhes = card.querySelector('[data-clima-detalhes]');
    const normalizar = valor => String(valor || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('pt-BR').trim();
    const nomesUF = {
    AC: "Acre", AL: "Alagoas", AP: "Amapá", AM: "Amazonas",
    BA: "Bahia", CE: "Ceará", DF: "Distrito Federal",
    ES: "Espírito Santo", GO: "Goiás", MA: "Maranhão",
    MT: "Mato Grosso", MS: "Mato Grosso do Sul",
    MG: "Minas Gerais", PA: "Pará", PB: "Paraíba",
    PR: "Paraná", PE: "Pernambuco", PI: "Piauí",
    RJ: "Rio de Janeiro", RN: "Rio Grande do Norte",
    RS: "Rio Grande do Sul", RO: "Rondônia", RR: "Roraima",
    SC: "Santa Catarina", SP: "São Paulo", SE: "Sergipe",
    TO: "Tocantins"
  };
const descricoesTempo = {
    0: "Céu limpo",
    1: "Predominantemente limpo",
    2: "Parcialmente nublado",
    3: "Nublado",
    45: "Nevoeiro",
    48: "Nevoeiro com geada",
    51: "Garoa leve",
    53: "Garoa",
    55: "Garoa intensa",
    56: "Garoa congelante leve",
    57: "Garoa congelante intensa",
    61: "Chuva leve",
    63: "Chuva moderada",
    65: "Chuva forte",
    66: "Chuva congelante leve",
    67: "Chuva congelante forte",
    71: "Neve leve",
    73: "Neve",
    75: "Neve intensa",
    77: "Grãos de neve",
    80: "Pancadas de chuva leves",
    81: "Pancadas de chuva",
    82: "Pancadas de chuva fortes",
    85: "Pancadas de neve leves",
    86: "Pancadas de neve fortes",
    95: "Trovoadas",
    96: "Trovoadas com granizo leve",
    99: "Trovoadas com granizo forte"
  };
    const chave = 'pampa-clima-v1:' + normalizar(card.dataset.cidade) + ':' + card.dataset.uf;
    const formatar = valor => new Intl.NumberFormat('pt-BR', { maximumFractionDigits: 0 }).format(valor);
    const escrever = (seletor, valor) => { card.querySelector(seletor).textContent = valor; };
    let carregando = false;

    async function consultarJson(url) {
        const controle = new AbortController();
        const limite = setTimeout(() => controle.abort(), 10000);
        try {
            const resposta = await fetch(url, { signal: controle.signal });
            if (!resposta.ok) throw new Error('Serviço indisponível');
            return await resposta.json();
        } finally {
            clearTimeout(limite);
        }
    }

    function mostrar(dados) {
        if (!Number.isFinite(dados.current?.temperature_2m) || !Number.isFinite(dados.current?.time)) throw new Error('Dados incompletos');
        const atual = dados.current;
        escrever('[data-clima-temperatura]', formatar(atual.temperature_2m) + ' °C');
        escrever('[data-clima-condicao]', descricoesTempo[atual.weather_code] || 'Condição não informada');
        escrever('[data-clima-sensacao]', Number.isFinite(atual.apparent_temperature) ? 'Sensação de ' + formatar(atual.apparent_temperature) + ' °C' : 'Sensação indisponível');
        escrever('[data-clima-icone]', atual.weather_code <= 1 ? (atual.is_day === 0 ? '☾' : '☀') : atual.weather_code <= 48 ? '☁' : '☂');
        const horario = new Intl.DateTimeFormat('pt-BR', { timeZone: dados.timezone, day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' }).format(new Date(atual.time * 1000));
        status.textContent = 'Condições de ' + horario + ' · horário da cidade';
        detalhes.hidden = false;
    }

    async function atualizar(usarCache = false) {
        if (carregando) return;
        carregando = true;
        botao.disabled = true;
        status.textContent = 'Consultando o clima da sua cidade…';
        try {
            let cache = null;
            try { cache = JSON.parse(sessionStorage.getItem(chave)); } catch (_) { /* O cartão também funciona sem armazenamento local. */ }
            if (usarCache && cache && Date.now() - cache.salvo < 600000 && Date.now() >= cache.salvo) {
                try { mostrar(cache.dados); return; } catch (_) { /* Atualiza um cache inválido. */ }
            }
            const geoUrl = new URL('https://geocoding-api.open-meteo.com/v1/search');
            geoUrl.search = new URLSearchParams({ name: card.dataset.cidade, count: '100', countryCode: 'BR', language: 'pt' });
            const geo = await consultarJson(geoUrl);
            const locais = (geo.results || []).filter(local => local.country_code === 'BR' && normalizar(local.name) === normalizar(card.dataset.cidade) && normalizar(local.admin1) === normalizar(nomesUF[card.dataset.uf]));
            locais.sort((a, b) => (b.population || 0) - (a.population || 0));
            const local = locais[0];
            if (!local || !Number.isFinite(local.latitude) || !Number.isFinite(local.longitude)) throw new Error('Cidade não localizada');
            const climaUrl = new URL('https://api.open-meteo.com/v1/forecast');
            climaUrl.search = new URLSearchParams({ latitude: String(local.latitude), longitude: String(local.longitude), current: 'temperature_2m,apparent_temperature,weather_code,is_day', timezone: 'auto', timeformat: 'unixtime', temperature_unit: 'celsius' });
            const dados = await consultarJson(climaUrl);
            mostrar(dados);
            try { sessionStorage.setItem(chave, JSON.stringify({ salvo: Date.now(), dados })); } catch (_) { /* Cache opcional. */ }
        } catch (_) {
            detalhes.hidden = true;
            status.textContent = 'Clima indisponível no momento. Confira sua cidade ou tente novamente.';
        } finally {
            carregando = false;
            botao.disabled = false;
        }
    }
    botao.addEventListener('click', () => atualizar());
    atualizar(true);
})();
