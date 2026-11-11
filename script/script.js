

const formLogin = document.getElementById("formLogin");

if (formLogin) {
  formLogin.addEventListener("submit", function (e) {
    const email = document.getElementById("email")?.value.trim() ?? "";
    const senha = document.getElementById("senha")?.value.trim() ?? "";

    if (email === "" || senha === "") {
      e.preventDefault();
      alert("Preencha todos os campos");
    }
   
  });
}

const formCadastro = document.getElementById("formCadastro");

if (formCadastro) {
  formCadastro.addEventListener("submit", function (e) {
    e.preventDefault();

    const email = document.getElementById("email")?.value.trim() ?? "";
    const registro = document.getElementById("Registro")?.value.trim() ?? "";
    const senha = document.getElementById("senha")?.value.trim() ?? "";
    const confirmarSenha = document.getElementById("confirmarSenha")?.value.trim() ?? "";

    if (email === "" || registro === "" || senha === "" || confirmarSenha === "") {
      alert("Preencha todos os campos!");
      return;
    }

    if (!email.includes("@") || !email.includes(".")) {
      alert("Digite um email válido!");
      return;
    }

    if (senha.length < 6) {
      alert("A senha precisa ter no mínimo 6 caracteres!");
      return;
    }

    if (senha !== confirmarSenha) {
      alert("As senhas não são iguais!");
      return;
    }

  
    alert("Dados validados! Para concluir o cadastro, configure o envio ao servidor.");
  });
}

const searchBtn = document.getElementById("searchBtn");
const searchInput = document.getElementById("searchInput");

if (searchBtn && searchInput) {
  searchBtn.addEventListener("click", () => {
    const value = searchInput.value.trim();

    if (value === "") {
      alert("Digite uma rota para pesquisar.");
      return;
    }

    alert("Pesquisa realizada: " + value);
  });

  searchInput.addEventListener("keypress", (e) => {
    if (e.key === "Enter") {
      searchBtn.click();
    }
  });
}

const prevBtn = document.getElementById("prevBtn");
const nextBtn = document.getElementById("nextBtn");

if (prevBtn) {
  prevBtn.addEventListener("click", () => {
    alert("Voltar rota");
  });
}

if (nextBtn) {
  nextBtn.addEventListener("click", () => {
    alert("Próxima rota");
  });
}


const consultaCepForm = document.getElementById("consultaCepForm");

if (consultaCepForm) {
  const inputCep = document.getElementById("cepConsulta");
  const botaoCep = document.getElementById("btnConsultarCep");
  const statusConsulta = document.getElementById("consultaStatus");
  const resultados = document.getElementById("consultaResultados");

  const escrever = (id, texto) => {
    document.getElementById(id).textContent = texto;
  };

  const normalizar = (valor) => String(valor || "")
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .toLocaleLowerCase("pt-BR")
    .trim();

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

  // Máscara do CEP: 00000-000
  inputCep.addEventListener("input", () => {
    const digitos = inputCep.value.replace(/\D/g, "").slice(0, 8);

    inputCep.value = digitos.length > 5
      ? `${digitos.slice(0, 5)}-${digitos.slice(5)}`
      : digitos;
  });

  consultaCepForm.addEventListener("submit", async (event) => {
    event.preventDefault();

    const cep = inputCep.value.replace(/\D/g, "");

    resultados.hidden = true;
    statusConsulta.classList.remove("erro");

    if (cep.length !== 8) {
      statusConsulta.textContent = "Digite um CEP válido com 8 números.";
      statusConsulta.classList.add("erro");
      inputCep.focus();
      return;
    }

    botaoCep.disabled = true;
    statusConsulta.textContent = "Consultando endereço e clima...";

    try {

      const respostaCep = await fetch(
        `https://viacep.com.br/ws/${cep}/json/`
      );

      if (!respostaCep.ok) {
        throw new Error("Não foi possível consultar o CEP agora.");
      }

      const dadosCep = await respostaCep.json();

      if (dadosCep.erro) {
        throw new Error("CEP não encontrado. Confira os números digitados.");
      }

      escrever(
        "resultadoCidade",
        `${dadosCep.localidade} / ${dadosCep.uf}`
      );

      escrever(
        "resultadoEndereco",
        dadosCep.logradouro || "Logradouro não informado para este CEP."
      );

      escrever(
        "resultadoBairro",
        dadosCep.bairro
          ? `Bairro: ${dadosCep.bairro}`
          : "Bairro não informado."
      );

      escrever(
        "resultadoCep",
        `CEP: ${dadosCep.cep || cep}`
      );

      escrever("resultadoTemperatura", "Buscando clima...");
      escrever("resultadoCondicao", "");
      escrever("resultadoUmidade", "");
      escrever("resultadoVento", "");

      resultados.hidden = false;
      statusConsulta.textContent = "Endereço encontrado. Consultando o clima...";


      const urlGeo = new URL(
        "https://geocoding-api.open-meteo.com/v1/search"
      );

      urlGeo.searchParams.set("name", dadosCep.localidade);
      urlGeo.searchParams.set("count", "100");
      urlGeo.searchParams.set("countryCode", "BR");
      urlGeo.searchParams.set("language", "pt");

      const respostaGeo = await fetch(urlGeo);

      if (!respostaGeo.ok) {
        throw new Error(
          "O endereço foi encontrado, mas o clima está indisponível."
        );
      }

      const dadosGeo = await respostaGeo.json();

      const nomeCidade = normalizar(dadosCep.localidade);

      const nomeEstado = normalizar(
        dadosCep.estado || nomesUF[dadosCep.uf]
      );

      const local = (dadosGeo.results || []).find((cidade) =>
        normalizar(cidade.name) === nomeCidade &&
        normalizar(cidade.admin1) === nomeEstado
      );

      if (!local) {
        throw new Error(
          "Endereço encontrado, mas não foi possível localizar o clima desta cidade."
        );
      }


      const urlClima = new URL(
        "https://api.open-meteo.com/v1/forecast"
      );

      urlClima.searchParams.set(
        "latitude",
        String(local.latitude)
      );

      urlClima.searchParams.set(
        "longitude",
        String(local.longitude)
      );

      urlClima.searchParams.set(
        "current",
        "temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m"
      );

      urlClima.searchParams.set("timezone", "auto");

      const respostaClima = await fetch(urlClima);

      if (!respostaClima.ok) {
        throw new Error(
          "Endereço encontrado, mas não foi possível consultar o clima."
        );
      }

      const dadosClima = await respostaClima.json();
      const agora = dadosClima.current;

      if (!agora || typeof agora.temperature_2m !== "number") {
        throw new Error(
          "A API de clima não retornou uma temperatura válida."
        );
      }


      const formatar = (numero) =>
        Number(numero).toLocaleString("pt-BR", {
          maximumFractionDigits: 1
        });

      escrever(
        "resultadoTemperatura",
        `${formatar(agora.temperature_2m)} °C`
      );

      escrever(
        "resultadoCondicao",
        descricoesTempo[agora.weather_code] ||
        "Condição do tempo não informada."
      );

      escrever(
        "resultadoUmidade",
        `Umidade: ${formatar(agora.relative_humidity_2m)}%`
      );

      escrever(
        "resultadoVento",
        `Vento: ${formatar(agora.wind_speed_10m)} km/h`
      );

      statusConsulta.textContent =
        "Consulta realizada! O clima representa a cidade, não o endereço exato do CEP.";

    } catch (erro) {

      statusConsulta.textContent =
        erro.message || "Ocorreu um erro inesperado.";

      statusConsulta.classList.add("erro");

      if (!resultados.hidden) {
        escrever("resultadoTemperatura", "Clima indisponível");
        escrever(
          "resultadoCondicao",
          "Tente novamente mais tarde."
        );
      }

    } finally {
      botaoCep.disabled = false;
    }
  });
}
