<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Criar Sensor - Pampa Serra</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="style/style.css">
</head>
<body>

    <div class="tela-criar-sensor">

    <div class="topo-criar">
      <div class="buscar-criar">
        <input type="text" placeholder="Buscar">
        <button type="button">
          <i class="bi bi-search"></i>
        </button>
      </div>

      <button class="btn-voltar-criar" onclick="window.location.href='sensores.html'">Voltar</button>
    </div>

    <div class="card-criar-sensor">

      <img src="assets/img/Logo.png" class="logo-criar-sensor" alt="Logo Pampa Serra">

      <form>

      <label for="sensor">Sensor:</label>
        <input type="text" id="sensor" class="form-control input-criar-sensor">

        <label for="localizacao">Localização:</label>
        <input type="text" id="localizacao" class="form-control input-criar-sensor">

        <label for="tipo">Tipo de dado:</label>
        <input type="text" id="tipo" class="form-control input-criar-sensor">

        <button type="submit" id="btn-criar-sensor">criar</button>

      </form>

    </div>

  </div>

  <script src="script.js"></script>
</body>
</html>