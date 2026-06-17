<?php
/*
  Esta página é exclusiva para administradores.
  O ficheiro admin.php verifica se o utilizador está logado
  e se tem permissão de administrador.
*/
require_once __DIR__ . '/admin.php';

/*
  Ficheiro responsável pela ligação à base de dados.
*/
require_once __DIR__ . '/db.php';

/*
  Ficheiro com funções auxiliares, como:
  - flash_set()
  - redirect()
  - e()
*/
require_once __DIR__ . '/functions.php';

/*
  Cria a ligação com a base de dados.
*/
$pdo = db();

/*
  Lista dos ficheiros de jogos que já existem no projeto.

  Estes nomes devem corresponder aos nomes usados no ficheiro jogar.php.
  Por exemplo:
  - snake
  - pacman
  - block_breaker

  Se criares um novo jogo no código, tens de adicionar o nome aqui também.
*/
$ficheirosPermitidos = ['snake', 'pacman', 'block_breaker'];

/*
  Função para verificar se um ficheiro de jogo já está a ser usado
  por outro jogo na base de dados.

  Exemplo:
  Se o jogo "Pac-Man" já usa o ficheiro "pacman",
  outro jogo não pode usar também "pacman".

  Parâmetros:
  - $pdo: ligação à base de dados
  - $game_file: nome do ficheiro do jogo
  - $ignore_id: usado quando estamos a editar um jogo.
    Assim, o sistema ignora o próprio jogo atual e só verifica os outros.

  Retorna:
  - true se o ficheiro já estiver usado
  - false se estiver livre
*/
function ficheiro_ja_usado($pdo, $game_file, $ignore_id = null) {
  /*
    Se o campo estiver vazio, não é considerado repetido.
    O jogo simplesmente ficará inativo.
  */
  if ($game_file === '') {
    return false;
  }

  /*
    Se não estamos a editar nenhum jogo específico,
    verifica se já existe qualquer jogo com esse ficheiro.
  */
  if ($ignore_id === null) {
    $st = $pdo->prepare("
      SELECT id
      FROM jogos
      WHERE game_file = ?
      LIMIT 1
    ");

    $st->execute([$game_file]);
  } else {
    /*
      Quando estamos a atualizar um jogo,
      o sistema ignora o ID do próprio jogo.
      Isso evita bloquear quando o jogo mantém o mesmo ficheiro.
    */
    $st = $pdo->prepare("
      SELECT id
      FROM jogos
      WHERE game_file = ?
      AND id <> ?
      LIMIT 1
    ");

    $st->execute([$game_file, $ignore_id]);
  }

  /*
    Se encontrar algum resultado, significa que o ficheiro já está usado.
  */
  return (bool)$st->fetch();
}

/*
  CRIAR NOVO JOGO
  Este bloco é executado quando o formulário de adicionar jogo é enviado.
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['criar_jogo'])) {
  /*
    Recebe e limpa os dados enviados pelo formulário.
  */
  $title = trim($_POST['title'] ?? '');
  $genre = trim($_POST['genre'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $price = (float)($_POST['price'] ?? 0);
  $image = trim($_POST['image'] ?? '');
  $game_file = trim($_POST['game_file'] ?? '');

  /*
    Validação básica:
    título, género e preço são obrigatórios.
  */
  if ($title === '' || $genre === '' || $price <= 0) {
    flash_set('danger', 'preenche título, género e preço.');
    redirect('games.php');
  }

  /*
    Verifica se o ficheiro informado já está associado a outro jogo.
    Isto impede, por exemplo, criar outro jogo usando o ficheiro "pacman".
  */
  if (ficheiro_ja_usado($pdo, $game_file)) {
    flash_set('danger', 'este ficheiro de jogo já está associado a outro jogo.');
    redirect('games.php');
  }

  /*
    Define se o jogo será criado ativo ou inativo.

    O jogo só fica ativo se o ficheiro existir na lista de ficheiros permitidos.
    Se o campo estiver vazio ou tiver um nome inválido, fica inativo.
  */
  $is_active = in_array($game_file, $ficheirosPermitidos, true) ? 1 : 0;

  /*
    Insere o novo jogo na base de dados.
  */
  $st = $pdo->prepare("
    INSERT INTO jogos (title, genre, description, price, image, is_active, game_file)
    VALUES (?, ?, ?, ?, ?, ?, ?)
  ");

  $st->execute([
    $title,
    $genre,
    $description,
    $price,
    $image,
    $is_active,
    $game_file
  ]);

  /*
    Mensagem diferente dependendo se o jogo ficou ativo ou inativo.
  */
  if ($is_active === 1) {
    flash_set('success', 'jogo adicionado e ativado com sucesso.');
  } else {
    flash_set('warning', 'jogo adicionado como inativo porque não tem ficheiro válido.');
  }

  redirect('games.php');
}

/*
  ATUALIZAR FICHEIRO DO JOGO
  Este bloco permite mudar o campo game_file diretamente na tabela.
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atualizar_ficheiro'])) {
  /*
    Recebe o ID do jogo e o novo ficheiro.
  */
  $id = (int)($_POST['jogo_id'] ?? 0);
  $game_file = trim($_POST['game_file'] ?? '');

  /*
    Verifica se o ficheiro já está a ser usado por outro jogo.
    O próprio jogo atual é ignorado por causa do $id.
  */
  if (ficheiro_ja_usado($pdo, $game_file, $id)) {
    flash_set('danger', 'este ficheiro de jogo já está associado a outro jogo.');
    redirect('games.php');
  }

  /*
    Se o ficheiro for válido, o jogo fica ativo.
    Se estiver vazio ou inválido, o jogo fica inativo.
  */
  $is_active = in_array($game_file, $ficheirosPermitidos, true) ? 1 : 0;

  /*
    Atualiza o ficheiro do jogo e o estado ativo/inativo.
  */
  $st = $pdo->prepare("
    UPDATE jogos
    SET game_file = ?, is_active = ?
    WHERE id = ?
  ");

  $st->execute([
    $game_file,
    $is_active,
    $id
  ]);

  /*
    Mostra mensagem de acordo com o resultado.
  */
  if ($is_active === 1) {
    flash_set('success', 'ficheiro atualizado e jogo ativado.');
  } else {
    flash_set('warning', 'ficheiro atualizado, mas o jogo ficou inativo porque o ficheiro não é válido.');
  }

  redirect('games.php');
}

/*
  ATIVAR OU DESATIVAR JOGO
  Este bloco é executado quando o admin clica no botão ativar/desativar.
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_id'])) {
  $id = (int)$_POST['toggle_id'];

  /*
    Busca o jogo na base de dados para verificar o estado atual
    e o ficheiro associado.
  */
  $st = $pdo->prepare("
    SELECT game_file, is_active
    FROM jogos
    WHERE id = ?
  ");

  $st->execute([$id]);
  $jogo = $st->fetch();

  /*
    Se o jogo não existir, mostra erro.
  */
  if (!$jogo) {
    flash_set('danger', 'jogo não encontrado.');
    redirect('games.php');
  }

  /*
    Se o jogo estiver inativo e o admin tentar ativar,
    o sistema verifica se existe um ficheiro válido.

    Se não tiver ficheiro válido, não deixa ativar.
  */
  if ((int)$jogo['is_active'] === 0 && !in_array($jogo['game_file'], $ficheirosPermitidos, true)) {
    flash_set('danger', 'não é possível ativar este jogo porque ele não tem ficheiro válido.');
    redirect('games.php');
  }

  /*
    Alterna o estado:
    se está ativo, fica inativo.
    se está inativo, fica ativo.
  */
  $st = $pdo->prepare("
    UPDATE jogos
    SET is_active = 1 - is_active
    WHERE id = ?
  ");

  $st->execute([$id]);

  flash_set('success', 'estado do jogo atualizado.');
  redirect('games.php');
}

/*
  APAGAR JOGO
  Este bloco remove um jogo da base de dados.
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
  $id = (int)$_POST['delete_id'];

  /*
    Apaga o jogo pelo ID.
  */
  $st = $pdo->prepare("
    DELETE FROM jogos
    WHERE id = ?
  ");

  $st->execute([$id]);

  flash_set('success', 'jogo removido com sucesso.');
  redirect('games.php');
}

/*
  BUSCAR TODOS OS JOGOS
  Lista os jogos cadastrados, dos mais recentes para os mais antigos.
*/
$st = $pdo->query("
  SELECT id, title, genre, description, price, image, is_active, game_file, created_at
  FROM jogos
  ORDER BY created_at DESC
");

$jogos = $st->fetchAll();

/*
  Carrega o cabeçalho do site.
*/
require_once __DIR__ . '/header.php';
?>

<div class="container py-4">

  <!-- topo da página -->
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

            <!-- identifica que este formulário é para criar jogo -->
            <input type="hidden" name="criar_jogo" value="1">

            <!-- título do jogo -->
            <div class="mb-3">
              <label class="form-label">título</label>
              <input type="text" name="title" class="form-control" required>
            </div>

            <!-- género do jogo -->
            <div class="mb-3">
              <label class="form-label">género</label>
              <input type="text" name="genre" class="form-control" required>
            </div>

            <!-- descrição do jogo -->
            <div class="mb-3">
              <label class="form-label">descrição</label>
              <textarea name="description" class="form-control" rows="3"></textarea>
            </div>

            <!-- preço do jogo -->
            <div class="mb-3">
              <label class="form-label">preço</label>
              <input type="number" step="0.01" min="0" name="price" class="form-control" required>
            </div>

            <!-- caminho da imagem do jogo -->
            <div class="mb-3">
              <label class="form-label">imagem</label>
              <input type="text" name="image" class="form-control" placeholder="img/bg.png">
            </div>

            <!-- nome do ficheiro/código do jogo -->
            <div class="mb-3">
              <label class="form-label">ficheiro do jogo</label>
              <input
                type="text"
                name="game_file"
                class="form-control"
                placeholder="snake, pacman ou block_breaker"
              >

              <small class="text-light-emphasis">
                se ficar vazio, inválido ou repetido, o jogo não será ativado.
              </small>
            </div>

            <button class="btn btn-success w-100">
              adicionar
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- lista de jogos cadastrados -->
    <div class="col-lg-8">
      <div class="card card-dark shadow">
        <div class="card-body p-4">
          <h4 class="fw-bold mb-3">jogos cadastrados</h4>

          <?php if (!$jogos): ?>

            <!-- mensagem caso não exista nenhum jogo -->
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

                      <!-- ID do jogo -->
                      <td><?= (int)$jogo['id'] ?></td>

                      <!-- título -->
                      <td class="fw-semibold">
                        <?= e($jogo['title']) ?>
                      </td>

                      <!-- género -->
                      <td>
                        <?= e($jogo['genre']) ?>
                      </td>

                      <!-- preço formatado em euros -->
                      <td>
                        €<?= number_format((float)$jogo['price'], 2, ',', '.') ?>
                      </td>

                      <!-- campo para atualizar o ficheiro do jogo -->
                      <td style="min-width: 250px;">
                        <form method="post" class="d-flex gap-2">

                          <!-- identifica que este formulário atualiza ficheiro -->
                          <input type="hidden" name="atualizar_ficheiro" value="1">

                          <!-- ID do jogo que será atualizado -->
                          <input type="hidden" name="jogo_id" value="<?= (int)$jogo['id'] ?>">

                          <input
                            type="text"
                            name="game_file"
                            class="form-control form-control-sm"
                            value="<?= e($jogo['game_file'] ?? '') ?>"
                            placeholder="snake, pacman ou block_breaker"
                          >

                          <button class="btn btn-outline-success btn-sm">
                            guardar
                          </button>
                        </form>
                      </td>

                      <!-- estado ativo/inativo -->
                      <td>
                        <?php if ((int)$jogo['is_active'] === 1): ?>
                          <span class="badge bg-success">sim</span>
                        <?php else: ?>
                          <span class="badge bg-secondary">não</span>
                        <?php endif; ?>
                      </td>

                      <!-- ações do admin -->
                      <td class="text-end">

                        <!-- botão ativar/desativar -->
                        <form method="post" class="d-inline">
                          <input type="hidden" name="toggle_id" value="<?= (int)$jogo['id'] ?>">

                          <button class="btn btn-outline-warning btn-sm">
                            <?= ((int)$jogo['is_active'] === 1) ? 'desativar' : 'ativar' ?>
                          </button>
                        </form>

                        <!-- botão apagar -->
                        <form
                          method="post"
                          class="d-inline"
                          onsubmit="return confirm('tens certeza que queres apagar este jogo?');"
                        >
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

<?php
/*
  Carrega o rodapé do site.
*/
require_once __DIR__ . '/footer.php';
?>