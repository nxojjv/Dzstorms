<?php
/* protege a página para apenas admin */
require_once __DIR__ . '/admin.php';

/* liga à base de dados */
require_once __DIR__ . '/db.php';

/* carrega funções */
require_once __DIR__ . '/functions.php';

$pdo = db();

/* criar novo jogo */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['criar_jogo'])) {
  $title = trim($_POST['title'] ?? '');
  $genre = trim($_POST['genre'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $price = (float)($_POST['price'] ?? 0);
  $image = trim($_POST['image'] ?? '');
  $game_file = trim($_POST['game_file'] ?? '');

  if ($title === '' || $genre === '' || $price <= 0) {
    flash_set('danger', 'preenche título, género e preço.');
    redirect('games.php');
  }

  $st = $pdo->prepare("
    INSERT INTO jogos (title, genre, description, price, image, is_active, game_file)
    VALUES (?, ?, ?, ?, ?, 1, ?)
  ");
  $st->execute([$title, $genre, $description, $price, $image, $game_file]);

  flash_set('success', 'jogo adicionado com sucesso.');
  redirect('games.php');
}

/* ativar ou desativar jogo */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_id'])) {
  $id = (int)$_POST['toggle_id'];

  $st = $pdo->prepare("UPDATE jogos SET is_active = 1 - is_active WHERE id = ?");
  $st->execute([$id]);

  flash_set('success', 'estado do jogo atualizado.');
  redirect('games.php');
}

/* apagar jogo */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
  $id = (int)$_POST['delete_id'];

  $st = $pdo->prepare("DELETE FROM jogos WHERE id = ?");
  $st->execute([$id]);

  flash_set('success', 'jogo removido com sucesso.');
  redirect('games.php');
}

/* buscar jogos */
$st = $pdo->query("
  SELECT id, title, genre, description, price, image, is_active, game_file, created_at
  FROM jogos
  ORDER BY created_at DESC
");
$jogos = $st->fetchAll();

require_once __DIR__ . '/header.php';
?>

<div class="container py-4">

  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h2 class="fw-bold mb-1">gestão de jogos</h2>
      <p class="text-light-emphasis mb-0">adicionar, remover e ativar jogos</p>
    </div>

    <a href="dashboard.php" class="btn btn-outline-light">
      voltar ao painel
    </a>
  </div>

  <div class="row g-4">

    <!-- formulário para adicionar jogo -->
    <div class="col-lg-4">
      <div class="card card-dark shadow">
        <div class="card-body p-4">
          <h4 class="fw-bold mb-3">adicionar jogo</h4>

          <form method="post">
            <input type="hidden" name="criar_jogo" value="1">

            <div class="mb-3">
              <label class="form-label">título</label>
              <input type="text" name="title" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label">género</label>
              <input type="text" name="genre" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label">descrição</label>
              <textarea name="description" class="form-control" rows="3"></textarea>
            </div>

            <div class="mb-3">
              <label class="form-label">preço</label>
              <input type="number" step="0.01" min="0" name="price" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label">imagem</label>
              <input type="text" name="image" class="form-control" placeholder="img/bg.png">
            </div>

            <div class="mb-3">
              <label class="form-label">ficheiro do jogo</label>
              <input type="text" name="game_file" class="form-control" placeholder="click, quiz ou memory">
            </div>

            <button class="btn btn-success w-100">
              adicionar
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- lista de jogos -->
    <div class="col-lg-8">
      <div class="card card-dark shadow">
        <div class="card-body p-4">
          <h4 class="fw-bold mb-3">jogos cadastrados</h4>

          <?php if (!$jogos): ?>
            <div class="alert alert-warning mb-0">
              ainda não existem jogos.
            </div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-dark table-hover align-middle">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>título</th>
                    <th>género</th>
                    <th>preço</th>
                    <th>ficheiro</th>
                    <th>ativo</th>
                    <th class="text-end">ações</th>
                  </tr>
                </thead>

                <tbody>
                  <?php foreach ($jogos as $jogo): ?>
                    <tr>
                      <td><?= (int)$jogo['id'] ?></td>

                      <td class="fw-semibold">
                        <?= e($jogo['title']) ?>
                      </td>

                      <td>
                        <?= e($jogo['genre']) ?>
                      </td>

                      <td>
                        €<?= number_format((float)$jogo['price'], 2, ',', '.') ?>
                      </td>

                      <td>
                        <?= e($jogo['game_file'] ?: '-') ?>
                      </td>

                      <td>
                        <?php if ((int)$jogo['is_active'] === 1): ?>
                          <span class="badge bg-success">sim</span>
                        <?php else: ?>
                          <span class="badge bg-secondary">não</span>
                        <?php endif; ?>
                      </td>

                      <td class="text-end">
                        <!-- botão ativar/desativar -->
                        <form method="post" class="d-inline">
                          <input type="hidden" name="toggle_id" value="<?= (int)$jogo['id'] ?>">
                          <button class="btn btn-outline-warning btn-sm">
                            <?= ((int)$jogo['is_active'] === 1) ? 'desativar' : 'ativar' ?>
                          </button>
                        </form>

                        <!-- botão apagar -->
                        <form method="post" class="d-inline" onsubmit="return confirm('tens certeza que queres apagar este jogo?');">
                          <input type="hidden" name="delete_id" value="<?= (int)$jogo['id'] ?>">
                          <button class="btn btn-outline-danger btn-sm">
                            apagar
                          </button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>

        </div>
      </div>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>