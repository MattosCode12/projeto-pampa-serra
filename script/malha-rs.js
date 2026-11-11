(() => {
    const painel = document.querySelector('[data-malha-rs]');
    if (!painel) return;

    const status = painel.querySelector('[data-malha-status]');
    if (!window.L) {
        status.textContent = 'Mapa indisponível. Tente recarregar a página.';
        return;
    }

    // Corredores da Malha Sul (ANTT), desenhados por pontos de referência.
    const linhas = [
        {
            nome: 'Santa Maria – Uruguaiana',
            cor: '#174eae',
            pontos: [
                ['Santa Maria', -29.69, -53.81],
                ['Dilermando de Aguiar', -29.71, -54.12],
                ['Cacequi', -29.88, -54.82],
                ['Alegrete', -29.79, -55.79],
                ['Uruguaiana', -29.76, -57.08],
            ],
        },
        {
            nome: 'Santa Maria – Passo Fundo',
            cor: '#087752',
            pontos: [
                ['Santa Maria', -29.69, -53.81],
                ['Cruz Alta', -28.64, -53.61],
                ['Ibirubá', -28.63, -53.09],
                ['Passo Fundo', -28.26, -52.41],
            ],
        },
        {
            nome: 'Cacequi – Rio Grande',
            cor: '#a14809',
            pontos: [
                ['Cacequi', -29.88, -54.82],
                ['Bagé', -31.33, -54.11],
                ['Pelotas', -31.77, -52.34],
                ['Rio Grande', -32.03, -52.10],
            ],
        },
        {
            nome: 'Cruz Alta – Santa Rosa',
            cor: '#793fa0',
            pontos: [
                ['Cruz Alta', -28.64, -53.61],
                ['Ijuí', -28.39, -53.91],
                ['Santo Ângelo', -28.30, -54.27],
                ['Santa Rosa', -27.87, -54.48],
            ],
        },
    ];

    const mapaElemento = painel.querySelector('[data-malha-mapa]');
    const filtro = painel.querySelector('select');
    mapaElemento.hidden = false;
    painel.querySelector('[data-malha-filtro]').hidden = false;

    const mapa = L.map(mapaElemento, { scrollWheelZoom: false });
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    })
        .on('tileerror', () => {
            status.textContent = 'O mapa de fundo está indisponível. Os corredores continuam visíveis.';
        })
        .on('tileload', () => { status.textContent = ''; })
        .addTo(mapa);

    const camadas = linhas.map((linha, indice) => {
        filtro.add(new Option(linha.nome, String(indice)));
        const trilho = L.polyline(linha.pontos.map(([, lat, lng]) => [lat, lng]), {
            color: linha.cor,
            weight: 5,
            dashArray: '8 5',
        }).bindTooltip(linha.nome);

        const pontos = linha.pontos.map(([cidade, lat, lng]) =>
            L.circleMarker([lat, lng], {
                radius: 5,
                color: linha.cor,
                fillColor: '#fff',
                fillOpacity: 1,
            }).bindTooltip(cidade)
        );

        return L.featureGroup([trilho, ...pontos]);
    });

    const visiveis = L.featureGroup().addTo(mapa);
    function mostrarLinhas() {
        visiveis.clearLayers();
        const selecionadas = filtro.value === '' ? camadas : [camadas[Number(filtro.value)]];
        selecionadas.forEach((camada) => visiveis.addLayer(camada));
        mapa.fitBounds(visiveis.getBounds(), { padding: [20, 20], maxZoom: 9 });
    }

    filtro.addEventListener('change', mostrarLinhas);
    mostrarLinhas();
})();
