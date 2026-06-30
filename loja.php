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

  /* prepara o termo de pesquisa */
  $termo = '%' . $pesquisa . '%';

  /* executa a pesquisa */
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

<!-- Estilos da página da loja -->
<style>
  /* importa fontes para deixar o visual mais bonito */
  @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&family=Poppins:wght@300;400;600;700&display=swap');

  /* área geral da loja */
  .loja-page {
    min-height: 100vh;
    padding: 40px 0 60px;
    font-family: 'Poppins', Arial, sans-serif;
  }

  /* área do título da loja sem fundo */
  .loja-hero {
    text-align: center;
    margin-bottom: 35px;
    padding: 20px 0;
    background: transparent;
    border: none;
    box-shadow: none;
  }

  /* título principal da loja */
  .loja-title {
    font-family: 'Orbitron', Arial, sans-serif;
    text-transform: uppercase;
    letter-spacing: 5px;
    font-size: 48px;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 10px;
    text-shadow: 0 0 15px rgba(0, 255, 128, 0.45);
  }

  /* letras verdes do título */
  .green-letter {
    color: #22d474;
  }

  /* subtítulo da loja */
  .loja-subtitle {
    color: rgba(255, 255, 255, 0.70);
    font-size: 14px;
    letter-spacing: 3px;
    text-transform: uppercase;
    margin-bottom: 0;
  }

  /* caixa da pesquisa */
  .search-box {
    max-width: 560px;
    margin: 0 auto 40px;
  }

  /* input da pesquisa */
  .search-input {
    height: 48px;
    border-radius: 14px 0 0 14px;
    border: 1px solid rgba(255,255,255,0.12);
    font-size: 15px;
    padding-left: 18px;
  }

  /* botão da pesquisa */
  .search-btn {
    border-radius: 0 14px 14px 0;
    padding: 0 20px;
    background: #14b86a;
    border: none;
  }

  /* efeito no botão da pesquisa */
  .search-btn:hover {
    background: #18d47c;
  }

  /* botão limpar pesquisa */
  .clear-btn {
    border-radius: 14px;
    margin-left: 10px;
  }

  /* card de cada jogo */
  .game-card {
    background: rgba(18, 18, 28, 0.94);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 20px;
    overflow: hidden;
    color: #fff;
    height: 100%;
    box-shadow: 0 10px 30px rgba(0,0,0,0.35);
    transition: transform 0.25s ease, box-shadow 0.25s ease, border 0.25s ease;
  }

  /* efeito ao passar o rato por cima do card */
  .game-card:hover {
    transform: translateY(-8px);
    border: 1px solid rgba(0, 255, 128, 0.35);
    box-shadow: 0 18px 40px rgba(0, 255, 128, 0.14);
  }

  /* caixa onde fica a imagem do jogo */
  .game-cover-box {
    height: 230px;
    background: #05080c;
    overflow: hidden;
  }

  /* imagem do jogo */
  .game-cover-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center top;
    display: block;
    filter: brightness(0.9);
  }

  /* caso algum jogo não tenha imagem */
  .game-cover-empty {
    height: 230px;
    background: #1a1a22;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #777;
  }

  /* corpo do card */
  .game-body {
    padding: 22px;
    text-align: center;
  }

  /* género do jogo */
  .game-badge {
    display: inline-block;
    background: rgba(0, 180, 95, 0.95);
    color: white;
    padding: 6px 14px;
    border-radius: 30px;
    font-size: 12px;
    font-weight: 700;
    margin-bottom: 14px;
  }

  /* título do jogo */
  .game-title {
    font-family: 'Orbitron', Arial, sans-serif;
    font-size: 21px;
    font-weight: 700;
    margin-bottom: 12px;
    color: #fff;
  }

  /* descrição do jogo */
  .game-description {
    color: rgba(255,255,255,0.65);
    font-size: 14px;
    min-height: 55px;
    line-height: 1.6;
  }

  /* zona do preço e botão */
  .game-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    margin-top: 18px;
  }

  /* preço do jogo */
  .game-price {
    font-size: 18px;
    font-weight: 700;
    color: #20d982;
  }

  /* botão adicionar */
  .btn-add {
    background: #14b86a;
    color: white;
    border: none;
    padding: 8px 18px;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: 0.25s ease;
  }

  /* efeito do botão adicionar */
  .btn-add:hover {
    background: #18d47c;
    color: white;
    box-shadow: 0 0 15px rgba(0,255,128,0.35);
    transform: scale(1.04);
  }

  /* caixa quando nenhum jogo é encontrado */
  .empty-search {
    max-width: 560px;
    margin: 60px auto;
    padding: 40px 30px;
    text-align: center;
    border-radius: 22px;
    background: rgba(18, 18, 28, 0.95);
    border: 1px solid rgba(0, 255, 128, 0.20);
    box-shadow: 0 0 25px rgba(0,255,128,0.10);
  }

  /* título da caixa de nenhum jogo encontrado */
  .empty-search h2 {
    font-family: 'Orbitron', Arial, sans-serif;
    color: white;
    margin-bottom: 15px;
  }

  /* texto da caixa de nenhum jogo encontrado */
  .empty-search p {
    color: rgba(255,255,255,0.65);
    margin-bottom: 0;
  }

  /* adaptação para telemóvel */
  @media (max-width: 768px) {
    .loja-title {
      font-size: 34px;
      letter-spacing: 3px;
    }

    .loja-subtitle {
      font-size: 12px;
      letter-spacing: 2px;
    }

    .game-footer {
      flex-direction: column;
    }

    .btn-add {
      width: 100%;
    }
  }
</style>

<!-- conteúdo principal da loja -->
<div class="loja-page">

  <!-- título centralizado da loja sem fundo -->
  <div class="loja-hero">
    <h2 class="loja-title">
      L<span class="green-letter">O</span>J<span class="green-letter">A</span>
    </h2>

    <p class="loja-subtitle">
      Explora os jogos disponíveis no DZSTORMS
    </p>
  </div>

  <!-- formulário de pesquisa -->
  <form method="get" class="search-box">
    <div class="input-group">

      <!-- campo onde o utilizador escreve a pesquisa -->
      <input 
        type="text" 
        name="q" 
        class="form-control search-input" 
        placeholder="pesquisar jogos..."
        value="<?= e($pesquisa) ?>"
      >

      <!-- botão para pesquisar -->
      <button class="btn btn-success search-btn" title="pesquisar">
        <i class="bi bi-search"></i>
      </button>

      <!-- aparece apenas quando existe pesquisa -->
      <?php if ($pesquisa !== ''): ?>
        <a href="loja.php" class="btn btn-outline-light clear-btn">
          limpar
        </a>
      <?php endif; ?>

    </div>
  </form>

  <?php if (!$jogos): ?>

    <!-- mensagem quando nenhum jogo é encontrado -->
    <div class="empty-search">
      <h2>Nenhum jogo encontrado</h2>

      <p>
        Não existem jogos com esse termo de pesquisa.
      </p>
    </div>

  <?php else: ?>

    <!-- lista de jogos -->
    <div class="row g-4">

      <!-- percorre todos os jogos encontrados -->
      <?php foreach ($jogos as $jogo): ?>

        <!-- coluna de cada jogo -->
        <div class="col-md-6 col-lg-4">

          <!-- card do jogo -->
          <div class="game-card">

            <?php if (!empty($jogo['image'])): ?>

              <!-- imagem do jogo -->
              <div class="game-cover-box">
                <img 
                  src="<?= e($jogo['image']) ?>" 
                  alt="<?= e($jogo['title']) ?>"
                  class="game-cover-img"
                >
              </div>

            <?php else: ?>

              <!-- aparece se o jogo não tiver imagem -->
              <div class="game-cover-empty">
                <span>sem imagem</span>
              </div>

            <?php endif; ?>

            <!-- informações do jogo -->
            <div class="game-body">

              <!-- género do jogo -->
              <span class="game-badge">
                <?= e($jogo['genre']) ?>
              </span>

              <!-- título do jogo -->
              <h5 class="game-title">
                <?= e($jogo['title']) ?>
              </h5>

              <!-- descrição do jogo -->
              <p class="game-description">
                <?= e($jogo['description'] ?: 'sem descrição disponível.') ?>
              </p>

              <!-- preço e botão adicionar -->
              <div class="game-footer">

                <!-- preço do jogo -->
                <span class="game-price">
                  €<?= number_format((float)$jogo['price'], 2, ',', '.') ?>
                </span>

                <!-- formulário para adicionar o jogo ao carrinho -->
                <form method="post" action="adicionar_carrinho.php" class="m-0">

                  <!-- envia o id do jogo escolhido -->
                  <input type="hidden" name="jogo_id" value="<?= (int)$jogo['id'] ?>">

                  <!-- botão para adicionar ao carrinho -->
                  <button type="submit" class="btn-add">
                    Adicionar
                  </button>

                </form>

              </div>
            </div>

          </div>
        </div>

      <?php endforeach; ?>
    </div>

  <?php endif; ?>

</div>

<?php
/* importa o rodapé do site */
require_once __DIR__ . '/footer.php';
?>