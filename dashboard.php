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
      <h3 class="fw-bold mb-2">painel do administrador</h3>

      <!-- Pequena descrição do painel -->
      <p class="text-light opacity-75 mb-4">
        Gestão do DZStorms: jogos e vendas.
      </p>

      <!-- Botões principais do painel -->
      <div class="d-grid gap-3">

        <!-- Página onde o administrador gere os jogos -->
        <a class="btn btn-warning" href="games.php">
          gerir jogos
        </a>

        <!-- Página onde o administrador consulta os pedidos/vendas -->
        <a class="btn btn-outline-light" href="pedidos.php">
          ver pedidos / vendas
        </a>

        <!-- Voltar para a página inicial do site -->
        <a class="btn btn-outline-secondary" href="index.php">
          voltar ao site
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