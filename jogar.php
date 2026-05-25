<?php
/* protege a página: só utilizador logado consegue acessar */
require_once __DIR__ . '/auth.php';

/* liga à base de dados */
require_once __DIR__ . '/db.php';

/* carrega funções auxiliares */
require_once __DIR__ . '/functions.php';

/* pega o id do jogo pela url */
$jogoId = (int)($_GET['id'] ?? 0);

if ($jogoId <= 0) {
  flash_set('danger', 'jogo inválido.');
  redirect('biblioteca.php');
}

$pdo = db();

/* verifica se o utilizador comprou esse jogo */
$st = $pdo->prepare("
  SELECT j.id, j.title, j.genre, j.game_file
  FROM pedidos p
  INNER JOIN itens_pedido ip ON ip.pedido_id = p.id
  INNER JOIN jogos j ON j.id = ip.jogo_id
  WHERE p.user_id = ? AND j.id = ?
  LIMIT 1
");

$st->execute([current_user_id(), $jogoId]);
$jogo = $st->fetch();

/* se não comprou, bloqueia o acesso */
if (!$jogo) {
  flash_set('danger', 'não tens acesso a este jogo.');
  redirect('biblioteca.php');
}

/* verifica qual jogo deve abrir */
$gameFile = $jogo['game_file'] ?? '';

/* abre o jogo de clique */
if ($gameFile === 'click') {
  require __DIR__ . '/jogo_click.php';
  exit;
}

/* abre o jogo de quiz */
if ($gameFile === 'quiz') {
  require __DIR__ . '/jogo_quiz.php';
  exit;
}

/* abre o jogo da memória */
if ($gameFile === 'memory') {
  require __DIR__ . '/jogo_memory.php';
  exit;
}

/* se o jogo ainda não tiver ficheiro definido */
flash_set('danger', 'este jogo ainda não está disponível.');
redirect('biblioteca.php');