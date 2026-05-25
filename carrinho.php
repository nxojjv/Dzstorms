<?php
// Importa o ficheiro responsável pela ligação à base de dados
require_once __DIR__ . '/db.php';

// Importa o ficheiro com funções auxiliares do projeto
// Por exemplo: cart_items(), e(), redirect(), flash_set(), etc.
require_once __DIR__ . '/functions.php';

// Importa o cabeçalho da página
// Normalmente contém o início do HTML, menu/navbar e estilos principais
require_once __DIR__ . '/header.php';

// Vai buscar os IDs dos jogos que estão guardados no carrinho
// cart_items() devolve os itens do carrinho e array_keys() pega apenas nos IDs
$idsCarrinho = array_keys(cart_items());

// Cria um array vazio para guardar os jogos encontrados na base de dados
$jogos = [];

// Cria a variável total, que vai guardar o valor total do carrinho
$total = 0;

// Verifica se o carrinho tem algum jogo
if (!empty($idsCarrinho)) {

  // Cria os pontos de interrogação necessários para a consulta SQL
  // Por exemplo, se houver 3 jogos, fica: ?,?,?
  $placeholders = implode(',', array_fill(0, count($idsCarrinho), '?'));

  // Cria a ligação com a base de dados
  $pdo = db();

  // Prepara a consulta para buscar os jogos do carrinho que estejam ativos
  $st = $pdo->prepare("SELECT id, title, genre, price, image FROM jogos WHERE id IN ($placeholders) AND is_active = 1");

  // Executa a consulta usando os IDs dos jogos que estão no carrinho
  $st->execute($idsCarrinho);

  // Guarda todos os jogos encontrados num array
  $jogos = $st->fetchAll();

  // Percorre os jogos encontrados para calcular o total do carrinho
  foreach ($jogos as $jogo) {
    $total += (float)$jogo['price'];
  }
}
?>

<!-- Área do título da página do carrinho -->
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <!-- Título principal -->
    <h2 class="fw-bold mb-1">carrinho</h2>

    <!-- Pequena descrição da página -->
    <p class="text-light-emphasis mb-0">os teus jogos adicionados</p>
  </div>
</div>

<?php if (empty($jogos)): ?>
  <!-- Se não houver jogos no carrinho, mostra esta mensagem -->
  <div class="alert alert-warning">
    o teu carrinho está vazio.
  </div>

  <!-- Botão para voltar para a loja -->
  <a href="loja.php" class="btn btn-outline-light">ir para a loja</a>

<?php else: ?>
  <!-- Se existirem jogos no carrinho, mostra a lista dos jogos e o resumo da compra -->
  <div class="row g-4">

    <!-- Coluna principal onde aparecem os jogos do carrinho -->
    <div class="col-lg-8">

      <!-- Percorre todos os jogos que estão no carrinho -->
      <?php foreach ($jogos as $jogo): ?>

        <!-- Cartão individual de cada jogo -->
        <div class="card mb-3 border-0 shadow text-light" style="background: rgba(20,20,28,0.92);">
          <div class="row g-0 align-items-center">
            
            <!-- Área da imagem do jogo -->
            <div class="col-md-3">

              <?php if (!empty($jogo['image'])): ?>
                <!-- Se o jogo tiver imagem, mostra a imagem -->
                <img 
                  src="<?= e($jogo['image']) ?>" 
                  alt="<?= e($jogo['title']) ?>"
                  class="img-fluid rounded-start"
                  style="height: 160px; width: 100%; object-fit: cover;"
                >
              <?php else: ?>
                <!-- Se o jogo não tiver imagem, mostra uma caixa com o texto 'sem imagem' -->
                <div class="d-flex align-items-center justify-content-center h-100" style="min-height: 160px; background: #1a1a22;">
                  <span class="text-secondary">sem imagem</span>
                </div>
              <?php endif; ?>

            </div>

            <!-- Área com as informações do jogo -->
            <div class="col-md-9">
              <div class="card-body">

                <!-- Mostra o género/categoria do jogo -->
                <span class="badge bg-secondary mb-2"><?= e($jogo['genre']) ?></span>

                <!-- Mostra o título do jogo -->
                <h5 class="card-title fw-bold"><?= e($jogo['title']) ?></h5>

                <!-- Área com o preço e o botão de remover -->
                <div class="d-flex justify-content-between align-items-center mt-3">

                  <!-- Mostra o preço do jogo formatado em euros -->
                  <span class="fs-5 fw-bold text-success">
                    €<?= number_format((float)$jogo['price'], 2, ',', '.') ?>
                  </span>

                  <!-- Formulário para remover o jogo do carrinho -->
                  <form method="post" action="remover_carrinho.php" class="m-0">

                    <!-- Envia o ID do jogo que deve ser removido -->
                    <input type="hidden" name="jogo_id" value="<?= (int)$jogo['id'] ?>">

                    <!-- Botão para remover o jogo -->
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                      remover
                    </button>
                  </form>
                </div>
              </div>
            </div>

          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Coluna lateral com o resumo da compra -->
    <div class="col-lg-4">
      <div class="card border-0 shadow text-light" style="background: rgba(20,20,28,0.92);">
        <div class="card-body">

          <!-- Título do resumo -->
          <h5 class="fw-bold mb-3">resumo</h5>

          <!-- Mostra a quantidade de jogos no carrinho -->
          <div class="d-flex justify-content-between mb-2">
            <span>jogos</span>
            <span><?= count($jogos) ?></span>
          </div>

          <!-- Mostra o valor total do carrinho -->
          <div class="d-flex justify-content-between mb-3">
            <span>total</span>
            <span class="fw-bold text-success">
              €<?= number_format($total, 2, ',', '.') ?>
            </span>
          </div>

          <!-- Botões de ação do carrinho -->
          <div class="d-grid gap-2">

            <!-- Leva o utilizador para finalizar a compra -->
            <a href="finalizar_compra.php" class="btn btn-success">
              finalizar compra
            </a>

            <!-- Leva o utilizador de volta para a loja -->
            <a href="loja.php" class="btn btn-outline-light">
              continuar a comprar
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<?php
// Importa o rodapé da página
// Normalmente contém o fim do HTML e scripts finais
require_once __DIR__ . '/footer.php';
?>