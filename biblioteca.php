<?php
// protege a página: só utilizador logado consegue acessar
require_once __DIR__ . '/auth.php';

// liga à base de dados
require_once __DIR__ . '/db.php';

// carrega funções auxiliares
require_once __DIR__ . '/functions.php';

// carrega o topo do site
require_once __DIR__ . '/header.php';

$pdo = db();

// busca os jogos comprados pelo utilizador logado sem repetir jogos
$st = $pdo->prepare("
  SELECT
    j.id,
    j.title,
    j.genre,
    j.description,
    j.image,
    MIN(ip.price) AS price,
    MAX(p.created_at) AS created_at
  FROM pedidos p
  INNER JOIN itens_pedido ip ON ip.pedido_id = p.id
  INNER JOIN jogos j ON j.id = ip.jogo_id
  WHERE p.user_id = ?
  GROUP BY
    j.id,
    j.title,
    j.genre,
    j.description,
    j.image
  ORDER BY created_at DESC
");

$st->execute([current_user_id()]);
$jogos = $st->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h2 class="fw-bold mb-1">biblioteca</h2>
    <p class="text-light-emphasis mb-0">os teus jogos comprados</p>
  </div>
</div>

<?php if (empty($jogos)): ?>

  <!-- mensagem quando a biblioteca está vazia -->
  <div class="d-flex justify-content-center align-items-center" style="min-height:60vh;">
    <div class="card card-dark text-center shadow" style="max-width:520px; width:100%;">
      <div class="card-body p-5">
        <h2 class="fw-bold mb-3">biblioteca vazia</h2>
        <p class="text-light-emphasis mb-4">
          ainda não tens jogos comprados. vai até à loja e faz uma compra demo.
        </p>
        <a href="loja.php" class="btn btn-success btn-lg">
          ir para a loja
        </a>
      </div>
    </div>
  </div>

<?php else: ?>

  <!-- lista os jogos comprados -->
  <div class="row g-4">
    <?php foreach ($jogos as $jogo): ?>
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow border-0 text-light" style="background: rgba(20,20,28,0.92);">

          <?php if (!empty($jogo['image'])): ?>
            <img 
              src="<?= e($jogo['image']) ?>" 
              class="card-img-top" 
              alt="<?= e($jogo['title']) ?>"
              style="height: 220px; object-fit: cover;"
            >
          <?php else: ?>
            <div class="d-flex align-items-center justify-content-center" style="height: 220px; background: #1a1a22;">
              <span class="text-secondary">sem imagem</span>
            </div>
          <?php endif; ?>

          <div class="card-body d-flex flex-column">
            <span class="badge bg-secondary align-self-start mb-2">
              <?= e($jogo['genre']) ?>
            </span>

            <h5 class="card-title fw-bold"><?= e($jogo['title']) ?></h5>

            <p class="card-text text-light-emphasis small flex-grow-1">
              <?= e($jogo['description'] ?: 'sem descrição disponível.') ?>
            </p>

            <div class="mt-3">
              <div class="small text-light-emphasis mb-2">
                comprado em <?= date('d/m/Y H:i', strtotime($jogo['created_at'])) ?>
              </div>

              <div class="d-flex justify-content-between align-items-center mt-2">
                <span class="fs-6 fw-bold text-success">
                  €<?= number_format((float)$jogo['price'], 2, ',', '.') ?>
                </span>

                <!-- botão que abre o jogo comprado -->
                <a href="jogar.php?id=<?= (int)$jogo['id'] ?>" class="btn btn-success btn-sm">
                  jogar
                </a>
              </div>
            </div>
          </div>

        </div>
      </div>
    <?php endforeach; ?>
  </div>

<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>