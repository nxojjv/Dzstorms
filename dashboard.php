<?php
// Importa o ficheiro de proteção da área de administrador
// Este ficheiro garante que só utilizadores com permissão de admin podem aceder
require_once __DIR__ . '/../includes/admin.php';

// Importa o cabeçalho da página
// Normalmente contém o início do HTML, menu/navbar e estilos principais
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Título principal do painel de administração -->
<h3 class="fw-bold">Admin - Painel</h3>

<!-- Pequena descrição do que pode ser feito nesta área -->
<p class="text-light opacity-75">
  Gestão do DZStorm (jogos e vendas).
</p>

<!-- Área com os botões principais do painel -->
<div class="d-flex gap-2 mt-3">

  <!-- Botão que leva para a página onde o administrador pode gerir os jogos -->
  <a class="btn btn-warning" href="/dzstorms/admin/games.php">
    Gerir Jogos
  </a>

  <!-- Botão que leva para a página onde o administrador pode ver as vendas/pedidos -->
  <a class="btn btn-outline-light" href="/dzstorms/admin/orders.php">
    Ver Vendas
  </a>
</div>

<?php
// Importa o rodapé da página
// Normalmente contém o fim do HTML e scripts finais
require_once __DIR__ . '/../includes/footer.php';
?>