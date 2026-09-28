<?php
session_start();

require_once "../conexao.php";

if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];


    if ($nome === "" || $email === "" || $senha === "") {


        $erro = "Preencha todos os campos.";


    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {


        $erro = "Digite um e-mail válido.";


    } elseif (strlen($senha) < 6) {


        $erro = "A senha deve ter pelo menos 6 caracteres.";


    } else {
        
    $stmt = $conexao->prepare(
            "SELECT id FROM usuarios WHERE email = ?"
        );


        $stmt->bind_param("s", $email);


        $stmt->execute();


        $resultado = $stmt->get_result();


        if ($resultado->num_rows > 0) {


            $erro = "Este e-mail já está cadastrado.";


        } else {


            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);


            $perfil = "usuario";


            $stmt = $conexao->prepare(
                "INSERT INTO usuarios (nome, email, senha, perfil)
                 VALUES (?, ?, ?, ?)"
            );


            $stmt->bind_param(
                "ssss",
                $nome,
                $email,
                $senhaHash,
                $perfil
            );


            if ($stmt->execute()) {


                $sucesso = "Usuário cadastrado com sucesso.";


                $nome = "";
                $email = "";


            } else {


                $erro = "Erro ao cadastrar usuário.";
            }
        }
    }
}


?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listar Cadastro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/style.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="input-group mb-3">
            <input type="text" class="form-control" placeholder="Buscar">
        </div>
        <a href="Cadastro.html" class="btn-voltar mb-4">Voltar</a>
        <div class="card shadow-sm p-4">
            <img src="../assets/img/logo.png" class="mx-auto d-block mb-4" alt="Logo Pampa Serra" style="width: 120px;">
            <div class="mb-3">
                <label class="form-label">Nome</label>
                <input type="text" class="form-control" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">Telefone</label>
                <input type="text" class="form-control" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">Tipo de Usuário</label>
                <input type="text" class="form-control" readonly>
            </div>
        </div>
    </div>


    <script src="../script/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
