(() => {
    const bloco = document.querySelector('[data-endereco]');
    if (!bloco) return;
    const cep = bloco.querySelector('#cep');
    const botao = bloco.querySelector('[data-consultar-cep]');
    const status = bloco.querySelector('#cep-status');
    let pedido = null;
    let revisao = 0;
    const digitos = () => cep.value.replace(/\D/g, '');

    // Descarta respostas antigas, inclusive quando o endereço é editado manualmente.
    bloco.addEventListener('input', () => {
        revisao++;
        pedido?.abort();
        botao.disabled = false;
    });
    cep.addEventListener('input', () => {
        const valor = digitos().slice(0, 8);
        cep.value = valor.length > 5 ? valor.slice(0, 5) + '-' + valor.slice(5) : valor;
        status.textContent = 'Consulte o CEP ou confira o endereço abaixo antes de salvar.';
    });

    async function consultar() {
        pedido?.abort();
        const versao = ++revisao;
        const codigo = digitos();
        if (codigo.length !== 8) {
            status.textContent = 'Informe um CEP com 8 números.';
            return;
        }
        pedido = new AbortController();
        const controle = pedido;
        const limite = setTimeout(() => controle.abort(), 10000);
        botao.disabled = true;
        status.textContent = 'Consultando endereço…';
        try {
            const resposta = await fetch(`https://viacep.com.br/ws/${codigo}/json/`, { signal: controle.signal });
            if (!resposta.ok) throw new Error('Consulta indisponível');
            const dados = await resposta.json();
            if (versao !== revisao || codigo !== digitos()) return;
            if (dados.erro || !dados.localidade || !dados.uf) {
                status.textContent = 'CEP não encontrado. Confira o número ou preencha o endereço manualmente.';
                return;
            }
            for (const [campo, chave] of Object.entries({ logradouro: 'logradouro', bairro: 'bairro', cidade: 'localidade', uf: 'uf' })) {
                bloco.querySelector('#' + campo).value = dados[chave] || '';
            }
            status.textContent = 'Endereço encontrado. Confira os dados e complete número e complemento.';
        } catch (erro) {
            if (versao === revisao) status.textContent = 'Consulta indisponível. Você pode preencher o endereço manualmente.';
        } finally {
            clearTimeout(limite);
            if (versao === revisao) botao.disabled = false;
        }
    }
    botao.addEventListener('click', consultar);
    cep.addEventListener('blur', () => { if (digitos().length === 8) consultar(); });
})();
