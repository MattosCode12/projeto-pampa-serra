const formLogin = document.getElementById("formLogin");

if (formLogin) {
    formLogin.addEventListener("submit", function(e) {

        const email = document.getElementById("email").value.trim();
        const senha = document.getElementById("senha").value.trim();

        if (email === "" || senha === "") {
            e.preventDefault();
            alert("Preencha todos os campos.");
            return;
        }

    });
}


const formCadastro = document.getElementById("formCadastro");

if (formCadastro) {
    formCadastro.addEventListener("submit", function(e) {

        const email = document.getElementById("email").value.trim();
        const registro = document.getElementById("Registro").value.trim();
        const senha = document.getElementById("senha").value.trim();
        const confirmarSenha = document.getElementById("confirmarSenha").value.trim();

        if (
            email === "" ||
            registro === "" ||
            senha === "" ||
            confirmarSenha === ""
        ) {
            e.preventDefault();
            alert("Preencha todos os campos!");
            return;
        }

        if (!email.includes("@") || !email.includes(".")) {
            e.preventDefault();
            alert("Digite um email válido!");
            return;
        }

        if (senha.length < 6) {
            e.preventDefault();
            alert("A senha precisa ter no mínimo 6 caracteres!");
            return;
        }

        if (senha !== confirmarSenha) {
            e.preventDefault();
            alert("As senhas não são iguais!");
            return;
        }

      
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