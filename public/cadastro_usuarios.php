<?php
session_start();

require_once "../conexao.php";

<<<<<<< HEAD
if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../index.php");
    exit;
}
=======
if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
>>>>>>> 92acdd56b836ce8ec1f71c985ce26aae061e1754

$mensagem = "";
$erro = "";

<<<<<<< HEAD
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = trim($_POST["nome"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $telefone = trim($_POST["telefone"] ?? "");
    $tipo_usuario = $_POST["tipo_usuario"] ?? "usuario";
    $senha = trim($_POST["senha"] ?? "");
=======
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
>>>>>>> 92acdd56b836ce8ec1f71c985ce26aae061e1754

    if ($nome === "" || $email === "" || $senha === "") {
        $erro = "Preencha os campos obrigatórios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Informe um email válido.";
    } elseif (!in_array($tipo_usuario, ["admin", "usuario"])) {
        $erro = "Tipo de usuário inválido.";
    } else {
        $sql = "SELECT id FROM usuarios WHERE email = ?";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();

<<<<<<< HEAD
        if ($resultado->num_rows > 0) {
            $erro = "Já existe um usuário com este email.";
        } else {
            $sql = "INSERT INTO usuarios (nome, email, senha, telefone, tipo_usuario) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conexao->prepare($sql);
            $stmt->bind_param("sssss", $nome, $email, $senha, $telefone, $tipo_usuario);

            if ($stmt->execute()) {
                $mensagem = "Usuário cadastrado com sucesso.";
            } else {
=======
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


>>>>>>> 92acdd56b836ce8ec1f71c985ce26aae061e1754
                $erro = "Erro ao cadastrar usuário.";
            }
        }
    }
}
<<<<<<< HEAD
=======


>>>>>>> 92acdd56b836ce8ec1f71c985ce26aae061e1754
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Usuário</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/style.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="d-flex justify-content-between mb-4">
        <div class="input-group">
            <input type="text" class="form-control" placeholder="Cadastrar usuário" disabled>
        </div>

        <a href="home.php" class="btn btn-dark ms-3">Voltar</a>
    </div>

    <div class="card p-4 shadow-sm">
        <img src="../assets/img/logo.png" class="mx-auto d-block mb-4" alt="Logo Pampa Serra" style="width: 120px;">

        <?php if ($mensagem): ?>
            <div class="alert alert-success"><?= htmlspecialchars($mensagem) ?></div>
        <?php endif; ?>

        <?php if ($erro): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Nome</label>
                <input type="text" name="nome" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Telefone</label>
                <input type="text" name="telefone" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Senha</label>
                <input type="password" name="senha" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Tipo de Usuário</label>
                <select name="tipo_usuario" class="form-select">
                    <option value="usuario">Usuário</option>
                    <option value="admin">Administrador</option>
                </select>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Cadastrar</button>
                <a href="listar_cadastro.php" class="btn btn-dark">Ver Usuários</a>
            </div>
        </form>
    </div>
</div>

<script src="../script/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>