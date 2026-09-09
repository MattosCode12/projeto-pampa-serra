<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Rotas - Pampa Serra</title>

  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../style/style.css">
</head>

<body>

  
  <nav class="navbar navbar-expand-lg navbar-dark navbar-custom px-4">

    <a class="navbar-brand d-flex align-items-center gap-3" href="#">
      <img src="../assets/img/.png" alt="Logo" class="logo">
    </a>

    <div class="mx-auto search-container">
      <div class="input-group">
        <input type="text" class="form-control" id="searchInput" placeholder="Rotas">
        <button class="btn btn-light" id="searchBtn">
          <i class="bi bi-search"></i>
        </button>
      </div>
    </div>

  </nav>

   <div class="container-fluid mt-4">

   
    <div class="row justify-content-center g-4">

      
      <div class="col-lg-3">
        <div class="info-card text-center">

          <div class="alert-line danger">
            Atenção Máxima -
            <i class="bi bi-exclamation-triangle-fill"></i>
          </div>

          <div class="alert-line warning">
            Atenção -
            <i class="bi bi-exclamation-triangle-fill"></i>
          </div>

          <div class="alert-line success">
            Esperado -
            <i class="bi bi-exclamation-triangle-fill"></i>
          </div>

        </div>
      </div>

    
      <div class="col-lg-2">
        <div class="info-card text-center">
          <h2>Normal:</h2>

          <p>30-40 Km/h</p>
          <p>Consumo:</p>
          <p>3-5 L/km</p>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="info-card text-center rotas-card">

          <button class="rota-btn">
            Rota 3207 - Porto Alegre a Gramado.
          </button>

          <p class="my-2">
            Parada Obrigatória Estação de Novo Hamburgo
          </p>

          <button class="rota-btn">
            Rota 4180 - Gramado a Porto Alegre.
          </button>

        </div>
      </div>

    </div>

    
    <div class="mapa-container mt-4"></div>

      
      <button class="btn-mapa btn-left">
        <i class="bi bi-arrow-left-circle"></i>
      </button>

      
      <div class="mapa-box"></div>

       
        <img id="mapImage" src="" alt="Mapa">

      </div>

      <div class="info-mapa"></div>
        <i class="bi bi-exclamation-triangle-fill text-success fs-1"></i>

        <h2>39 Km/h</h2>
        <h2>3 L/km</h2>
      </div>

      
      <button class="btn-mapa btn-right">
        <i class="bi bi-arrow-right-circle"></i>
      </button>

    </div>

  </div>
    <script src="../script/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
