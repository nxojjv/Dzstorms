<?php
// protege a página: só utilizador logado consegue acessar
require_once __DIR__ . '/auth.php';

// liga à base de dados
require_once __DIR__ . '/db.php';

// carrega funções auxiliares
require_once __DIR__ . '/functions.php';

// carrega o topo do site
require_once __DIR__ . '/header.php';

// cria a ligação com a base de dados
$pdo = db();

// busca os jogos comprados pelo utilizador logado
// liga as tabelas pedidos, itens_pedido e jogos
// GROUP BY evita que o mesmo jogo apareça repetido na biblioteca
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

// executa a consulta usando o id do utilizador logado
$st->execute([current_user_id()]);

// guarda todos os jogos comprados num array
$jogos = $st->fetchAll();
?>

<!-- Estilos da página biblioteca -->
<style>
  /* importa fontes para deixar o visual mais bonito */
  @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&family=Poppins:wght@300;400;600;700&display=swap');

  /* área geral da biblioteca */
  .biblioteca-page {
    min-height: 100vh;
    padding: 40px 0 60px;
    font-family: 'Poppins', Arial, sans-serif;
  }

  /* área do título da biblioteca sem fundo */
  .biblioteca-hero {
    text-align: center;
    margin-bottom: 45px;
    padding: 20px 0;
    background: transparent;
    border: none;
    box-shadow: none;
  }

  /* título principal da biblioteca */
  .biblioteca-title {
    font-family: 'Orbitron', Arial, sans-serif;
    text-transform: uppercase;
    letter-spacing: 5px;
    font-size: 48px;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 0;
    text-shadow: 0 0 15px rgba(0, 255, 128, 0.45);
  }

  /* letras verdes do título */
  .green-letter {
    color: #22d474;
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

  /* imagem de cada jogo */
  .game-img {
    height: 230px;
    width: 100%;
    object-fit: cover;
    filter: brightness(0.9);
  }

  /* espaço usado quando o jogo não tem imagem */
  .game-no-img {
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

  /* data da compra */
  .game-date {
    color: rgba(255,255,255,0.48);
    font-size: 13px;
    margin-top: 18px;
    margin-bottom: 10px;
  }

  /* zona do preço e botão */
  .game-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    margin-top: 15px;
  }

  /* preço do jogo */
  .game-price {
    font-size: 18px;
    font-weight: 700;
    color: #20d982;
  }

  /* botão jogar */
  .btn-play {
    background: #14b86a;
    color: white;
    border: none;
    padding: 8px 18px;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: 0.25s ease;
  }

  /* efeito do botão jogar */
  .btn-play:hover {
    background: #18d47c;
    color: white;
    box-shadow: 0 0 15px rgba(0,255,128,0.35);
    transform: scale(1.04);
  }

  /* caixa mostrada quando a biblioteca está vazia */
  .empty-library {
    max-width: 560px;
    margin: 70px auto;
    padding: 45px 30px;
    text-align: center;
    border-radius: 22px;
    background: rgba(18, 18, 28, 0.95);
    border: 1px solid rgba(0, 255, 128, 0.20);
    box-shadow: 0 0 25px rgba(0,255,128,0.10);
  }

  /* título da biblioteca vazia */
  .empty-library h2 {
    font-family: 'Orbitron', Arial, sans-serif;
    color: white;
    margin-bottom: 15px;
  }

  /* texto da biblioteca vazia */
  .empty-library p {
    color: rgba(255,255,255,0.65);
    margin-bottom: 25px;
  }

  /* adaptação para telemóvel */
  @media (max-width: 768px) {
    .biblioteca-title {
      font-size: 32px;
      letter-spacing: 3px;
    }

    .game-footer {
      flex-direction: column;
    }

    .btn-play {
      width: 100%;
    }
  }
</style>

<!-- conteúdo principal da biblioteca -->
<div class="biblioteca-page">

  <!-- título centralizado da página sem fundo -->
  <div class="biblioteca-hero">
    <h2 class="biblioteca-title">
      BIBLI<span class="green-letter">O</span>TE<span class="green-letter">C</span>A
    </h2>
  </div>

  <?php if (empty($jogos)): ?>

    <!-- aparece quando o utilizador ainda não comprou jogos -->
    <div class="empty-library">
      <h2>Biblioteca vazia</h2>

      <p>
        Ainda não tens jogos comprados. Vai até à loja e faz uma compra demonstrativa.
      </p>

      <!-- botão para ir para a loja -->
      <a href="loja.php" class="btn-play">
        Ir para a loja
      </a>
    </div>

  <?php else: ?>

    <!-- lista dos jogos comprados -->
    <div class="row g-4">

      <!-- percorre todos os jogos comprados -->
      <?php foreach ($jogos as $jogo): ?>

        <!-- coluna de cada jogo -->
        <div class="col-md-6 col-lg-4">

          <!-- card do jogo -->
          <div class="game-card">

            <?php if (!empty($jogo['image'])): ?>

              <!-- imagem do jogo -->
              <img 
                src="<?= e($jogo['image']) ?>" 
                class="game-img" 
                alt="<?= e($jogo['title']) ?>"
              >

            <?php else: ?>

              <!-- aparece se o jogo não tiver imagem -->
              <div class="game-no-img">
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

              <!-- data em que o jogo foi comprado -->
              <div class="game-date">
                comprado em <?= date('d/m/Y H:i', strtotime($jogo['created_at'])) ?>
              </div>

              <!-- preço e botão jogar -->
              <div class="game-footer">

                <!-- preço guardado no momento da compra -->
                <span class="game-price">
                  €<?= number_format((float)$jogo['price'], 2, ',', '.') ?>
                </span>

                <!-- botão que abre o jogo comprado -->
                <a href="jogar.php?id=<?= (int)$jogo['id'] ?>" class="btn-play">
                  Jogar
                </a>

              </div>
            </div>

          </div>
        </div>

      <?php endforeach; ?>
    </div>

  <?php endif; ?>

</div>

<?php
// carrega o rodapé do site
require_once __DIR__ . '/footer.php';
?>