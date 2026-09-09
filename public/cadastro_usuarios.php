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