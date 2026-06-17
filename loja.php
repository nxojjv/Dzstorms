<?php
/* importa a ligação com a base de dados */
require_once __DIR__ . '/db.php';

/* importa funções auxiliares, como e(), redirect(), flash_set(), etc. */
require_once __DIR__ . '/functions.php';

/* importa o cabeçalho do site */
require_once __DIR__ . '/header.php';

/* cria a ligação com a base de dados */
$pdo = db();

/* pega o texto pesquisado na URL */
$pesquisa = trim($_GET['q'] ?? '');

/* se existir pesquisa, filtra os jogos pelo título, género ou descrição */
if ($pesquisa !== '') {
  $st = $pdo->prepare("
    SELECT id, title, genre, description, price, image 
    FROM jogos 
    WHERE is_active = 1
    AND (title LIKE ? OR genre LIKE ? OR description LIKE ?)
    ORDER BY created_at DESC
  ");

  $termo = '%' . $pesquisa . '%';
  $st->execute([$termo, $termo, $termo]);

} else {
  /* se não existir pesquisa, mostra todos os jogos ativos */
  $st = $pdo->query("
    SELECT id, title, genre, description, price, image 
    FROM jogos 
    WHERE is_active = 1 
    ORDER BY created_at DESC
  ");
}

/* guarda os jogos encontrados */
$jogos = $st->fetchAll();
?>

<!-- título da página -->
<div class="text-center mb-4">
  <h2 class="page-title mb-1">
    L<span class="green-letter">O</span>J<span class="green-letter">A</span>
  </h2>

  <p class="page-subtitle mb-0">
    EXPLORA OS JOGOS DISPONÍVEIS NO DZSTORMS
  </p>
</div>

<!-- formulário de pesquisa -->
<form method="get" class="mb-4 mx-auto" style="max-width:520px;">
  <div class="input-group">
    <input 
      type="text" 
      name="q" 
      class="form-control" 
      placeholder="pesquisar jogos..."
      value="<?= e($pesquisa) ?>"
    >

    <!-- botão com ícone de lupa -->
    <button class="btn btn-success" title="pesquisar">
      <i class="bi bi-search"></i>
    </button>

    <!-- aparece apenas quando existe pesquisa -->
    <?php if ($pesquisa !== ''): ?>
      <a href="loja.php" class="btn btn-outline-light">
        limpar
      </a>
    <?php endif; ?>
  </div>
</form>

<?php if (!$jogos): ?>

  <!-- mensagem quando nenhum jogo é encontrado -->
  <div class="alert alert-warning">
    nenhum jogo encontrado.
  </div>

<?php else: ?>

  <!-- lista de jogos -->
  <div class="row g-4">
    <?php foreach ($jogos as $jogo): ?>
      <div class="col-md-6 col-lg-4">

        <!-- card do jogo -->
        <div class="card h-100 shadow border-0 text-light" style="background: rgba(20,20,28,0.92); overflow:hidden;">

          <?php if (!empty($jogo['image'])): ?>
            <!-- imagem do jogo -->
            <div style="height: 270px; overflow: hidden; background:#111;">
              <img 
                src="<?= e($jogo['image']) ?>" 
                alt="<?= e($jogo['title']) ?>"
                style="width: 100%; height: 100%; object-fit: cover; object-position: center top;"
              >
            </div>
          <?php else: ?>
            <!-- caso o jogo não tenha imagem -->
            <div 
              class="d-flex align-items-center justify-content-center"
              style="height: 270px; background: #1a1a22;"
            >
              <span class="text-secondary">sem imagem</span>
            </div>
          <?php endif; ?>

          <div class="card-body d-flex flex-column">

            <!-- género do jogo -->
            <span class="badge bg-secondary align-self-start mb-2">
              <?= e($jogo['genre']) ?>
            </span>

            <!-- título do jogo -->
            <h5 class="card-title fw-bold">
              <?= e($jogo['title']) ?>
            </h5>

            <!-- descrição do jogo -->
            <p class="card-text text-light-emphasis small flex-grow-1">
              <?= e($jogo['description'] ?: 'sem descrição disponível.') ?>
            </p>

            <div class="d-flex justify-content-between align-items-center mt-3">

              <!-- preço do jogo -->
              <span class="fs-5 fw-bold text-success">
                €<?= number_format((float)$jogo['price'], 2, ',', '.') ?>
              </span>

              <!-- botão para adicionar ao carrinho -->
              <form method="post" action="adicionar_carrinho.php" class="m-0">
                <input type="hidden" name="jogo_id" value="<?= (int)$jogo['id'] ?>">

                <button type="submit" class="btn btn-success btn-sm">
                  adicionar
                </button>
              </form>

            </div>
          </div>

        </div>
      </div>
    <?php endforeach; ?>
  </div>

<?php endif; ?>

<?php
/* importa o rodapé do site */
require_once __DIR__ . '/footer.php';
?>