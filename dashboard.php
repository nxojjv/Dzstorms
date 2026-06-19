<?php
/*
  Protege a página para apenas administradores.
  O ficheiro admin.php verifica se o utilizador está logado
  e se tem permissão de administrador.
*/
require_once __DIR__ . '/admin.php';

/*
  Importa o cabeçalho da página.
  Contém o início do HTML, menu/navbar e estilos principais.
*/
require_once __DIR__ . '/header.php';
?>

<div class="container py-5">

  <div class="card card-dark shadow mx-auto" style="max-width: 700px;">
    <div class="card-body p-4">

      <!-- Título principal do painel de administração -->
      <h3 class="fw-bold mb-2">
        <i class="bi bi-shield-lock-fill me-2 text-warning"></i>
        painel do administrador
      </h3>

      <!-- Pequena descrição do painel -->
      <p class="text-light opacity-75 mb-4">
        Gestão do DZStorms: jogos e vendas.
      </p>

      <!-- Botões principais do painel -->
      <div class="d-grid gap-3">

        <!-- Página onde o administrador gere os jogos -->
        <a class="btn btn-warning fw-bold" href="games.php">
          <i class="bi bi-controller me-2"></i>
          gerir jogos
        </a>

        <!-- Página onde o administrador consulta os pedidos/vendas -->
        <a class="btn btn-outline-light fw-bold" href="pedidos.php">
          <i class="bi bi-receipt-cutoff me-2"></i>
          ver pedidos / vendas
        </a>

        <!-- Voltar para a página inicial do site -->
        <a class="btn btn-outline-secondary fw-bold" href="index.php">
          <i class="bi bi-house-door me-2"></i>
          voltar ao site
        </a>

        <!-- Terminar sessão do administrador -->
        <a class="btn btn-danger fw-bold" href="sair.php">
          <i class="bi bi-box-arrow-right me-2"></i>
          sair da conta
        </a>

      </div>

    </div>
  </div>

</div>

<?php
/*
  Importa o rodapé da página.
  Contém o fim do HTML e scripts finais.
*/
require_once __DIR__ . '/footer.php';
?>