<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

/* carrega os jogos que estão no carrinho */
$cart = cart_items();
$ids = array_keys($cart);
$jogos = [];
$total = 0;

/* se tiver jogos no carrinho, procura na base de dados */
if (!empty($ids)) {
  $pdo = db();

  $placeholders = implode(',', array_fill(0, count($ids), '?'));

  $st = $pdo->prepare("
    SELECT id, title, genre, description, price, image
    FROM jogos
    WHERE id IN ($placeholders) AND is_active = 1
  ");
  $st->execute($ids);
  $jogos = $st->fetchAll();

  foreach ($jogos as $jogo) {
    $total += (float)$jogo['price'];
  }
}

require_once __DIR__ . '/header.php';
?>

<style>
  .cart-page {
    max-width: 1100px;
    margin: 0 auto;
    padding: 50px 20px;
  }

  .cart-header {
    text-align: center;
    margin-bottom: 35px;
  }

  .cart-title {
    font-family: 'Orbitron', sans-serif;
    font-size: 44px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 3px;
    color: #fff;
    margin-bottom: 8px;
  }

  .cart-subtitle {
    font-size: 20px;
    color: rgba(255,255,255,0.65);
  }

  .empty-cart-box {
    max-width: 620px;
    margin: 0 auto;
    background: rgba(20,20,28,0.94);
    border: 1px solid rgba(37,196,90,0.45);
    border-radius: 18px;
    padding: 45px 30px;
    text-align: center;
    box-shadow: 0 0 28px rgba(37,196,90,0.18);
  }

  .empty-cart-icon {
    width: 90px;
    height: 90px;
    margin: 0 auto 20px;
    border-radius: 50%;
    background: rgba(37,196,90,0.12);
    border: 1px solid rgba(37,196,90,0.45);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #25c45a;
    font-size: 42px;
  }

  .empty-cart-title {
    font-family: 'Orbitron', sans-serif;
    font-size: 26px;
    font-weight: 700;
    text-transform: uppercase;
    margin-bottom: 12px;
    color: #fff;
  }

  .empty-cart-text {
    font-size: 19px;
    color: rgba(255,255,255,0.7);
    margin-bottom: 28px;
  }

  .cart-item {
    background: rgba(20,20,28,0.94);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 16px;
    padding: 18px;
    margin-bottom: 18px;
    display: flex;
    gap: 18px;
    align-items: center;
  }

  .cart-item-img {
    width: 130px;
    height: 95px;
    object-fit: cover;
    border-radius: 12px;
    background: #000;
  }

  .cart-item-title {
    font-family: 'Orbitron', sans-serif;
    font-size: 21px;
    font-weight: 700;
    color: #fff;
    margin-bottom: 5px;
  }

  .cart-item-genre {
    color: #25c45a;
    font-size: 16px;
    text-transform: uppercase;
    letter-spacing: 1px;
  }

  .cart-item-price {
    font-size: 22px;
    font-weight: 700;
    color: #25c45a;
    white-space: nowrap;
  }

  .cart-summary {
    background: rgba(20,20,28,0.96);
    border: 1px solid rgba(37,196,90,0.45);
    border-radius: 16px;
    padding: 25px;
    box-shadow: 0 0 22px rgba(37,196,90,0.16);
  }

  .cart-total {
    font-size: 28px;
    font-weight: 700;
    color: #25c45a;
  }

  .btn-cart-main {
    background: linear-gradient(90deg, #2ed46b, #15984a);
    border: none;
    color: #06140b;
    font-weight: 700;
    font-size: 19px;
    padding: 12px 28px;
    border-radius: 10px;
    text-transform: uppercase;
  }

  .btn-cart-main:hover {
    background: linear-gradient(90deg, #40ec7d, #19aa53);
    color: #000;
  }

  @media (max-width: 768px) {
    .cart-item {
      flex-direction: column;
      text-align: center;
    }

    .cart-item-img {
      width: 100%;
      height: 180px;
    }

    .cart-title {
      font-size: 34px;
    }
  }
</style>

<div class="cart-page">

  <div class="cart-header">
    <h1 class="cart-title">carrinho</h1>
  </div>

  <?php if (empty($jogos)): ?>

    <!-- Carrinho vazio -->
    <div class="empty-cart-box">

      <div class="empty-cart-icon">
        <i class="bi bi-cart-x"></i>
      </div>

      <h2 class="empty-cart-title">o carrinho está vazio</h2>

      <p class="empty-cart-text">
        ainda não adicionaste nenhum jogo ao carrinho.
        visita a loja e escolhe os teus jogos favoritos.
      </p>

      <a href="<?= BASE_URL ?>/loja.php" class="btn btn-cart-main">
        <i class="bi bi-controller me-2"></i>
        ir para a loja
      </a>

    </div>

  <?php else: ?>

    <div class="row g-4">

      <div class="col-lg-8">

        <?php foreach ($jogos as $jogo): ?>
          <div class="cart-item">

            <?php if (!empty($jogo['image'])): ?>
              <img 
                src="<?= BASE_URL ?>/<?= e($jogo['image']) ?>" 
                class="cart-item-img" 
                alt="<?= e($jogo['title']) ?>"
              >
            <?php else: ?>
              <div class="cart-item-img d-flex align-items-center justify-content-center">
                <i class="bi bi-controller fs-1 text-success"></i>
              </div>
            <?php endif; ?>

            <div class="flex-grow-1">
              <div class="cart-item-title"><?= e($jogo['title']) ?></div>
              <div class="cart-item-genre"><?= e($jogo['genre']) ?></div>
              <p class="text-light-emphasis mb-0 mt-2">
                <?= e(substr($jogo['description'], 0, 90)) ?>...
              </p>
            </div>

            <div class="text-center">
              <div class="cart-item-price mb-3">
                <?= number_format((float)$jogo['price'], 2, ',', '.') ?> €
              </div>

              <a 
                href="<?= BASE_URL ?>/remover_carrinho.php?id=<?= (int)$jogo['id'] ?>" 
                class="btn btn-outline-danger btn-sm"
              >
                <i class="bi bi-trash me-1"></i>
                remover
              </a>
            </div>

          </div>
        <?php endforeach; ?>

      </div>

      <div class="col-lg-4">

        <div class="cart-summary">

          <h4 class="fw-bold mb-3">
            <i class="bi bi-receipt me-2 text-success"></i>
            resumo
          </h4>

          <div class="d-flex justify-content-between mb-2">
            <span>Jogos:</span>
            <strong><?= count($jogos) ?></strong>
          </div>

          <hr class="border-secondary">

          <div class="d-flex justify-content-between align-items-center mb-4">
            <span class="fs-5">Total:</span>
            <span class="cart-total">
              <?= number_format($total, 2, ',', '.') ?> €
            </span>
          </div>

          <a href="<?= BASE_URL ?>/pagamento.php" class="btn btn-cart-main w-100 mb-3">
            <i class="bi bi-credit-card me-2"></i>
            finalizar compra
          </a>

          <a href="<?= BASE_URL ?>/loja.php" class="btn btn-outline-light w-100">
            continuar a comprar
          </a>

        </div>

      </div>

    </div>

  <?php endif; ?>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>