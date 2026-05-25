<?php
/* importa a ligação à base de dados */
require_once __DIR__ . '/db.php';

/* importa funções auxiliares */
require_once __DIR__ . '/functions.php';

/* se não vier por post, volta para a loja */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect('loja.php');
}

/* pega o id do jogo enviado pelo formulário */
$jogoId = (int)($_POST['jogo_id'] ?? 0);

/* valida o id do jogo */
if ($jogoId <= 0) {
  flash_set('danger', 'jogo inválido.');
  redirect('loja.php');
}

/* liga à base de dados */
$pdo = db();

/* verifica se o jogo existe e está ativo */
$st = $pdo->prepare("SELECT id FROM jogos WHERE id = ? AND is_active = 1");
$st->execute([$jogoId]);
$jogo = $st->fetch();

if (!$jogo) {
  flash_set('danger', 'jogo não encontrado.');
  redirect('loja.php');
}

/* se o utilizador estiver logado, verifica se ele já comprou o jogo */
if (is_logged_in()) {
  $st = $pdo->prepare("
    SELECT ip.id
    FROM pedidos p
    INNER JOIN itens_pedido ip ON ip.pedido_id = p.id
    WHERE p.user_id = ? AND ip.jogo_id = ?
    LIMIT 1
  ");
  $st->execute([current_user_id(), $jogoId]);

  if ($st->fetch()) {
    flash_set('warning', 'esse jogo ja se encontra na sua biblioteca.');
    redirect('biblioteca.php');
  }
}

/* adiciona o jogo ao carrinho */
cart_add($jogoId);

/* mostra mensagem de sucesso */
flash_set('success', 'jogo adicionado ao carrinho.');

/* manda para o carrinho */
redirect('carrinho.php');