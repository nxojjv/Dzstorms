<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

$idsCarrinho = array_keys(cart_items());

if (empty($idsCarrinho)) {
  flash_set('danger', 'o carrinho está vazio.');
  redirect('carrinho.php');
}

$pdo = db();

$placeholders = implode(',', array_fill(0, count($idsCarrinho), '?'));
$st = $pdo->prepare("SELECT id, title, price FROM jogos WHERE id IN ($placeholders) AND is_active = 1");
$st->execute($idsCarrinho);
$jogos = $st->fetchAll();

if (empty($jogos)) {
  flash_set('danger', 'não foi possível finalizar a compra.');
  redirect('carrinho.php');
}

$total = 0;
foreach ($jogos as $jogo) {
  $total += (float)$jogo['price'];
}

try {
  $pdo->beginTransaction();

  /* cria o pedido */
  $st = $pdo->prepare("INSERT INTO pedidos (user_id, total, status) VALUES (?, ?, 'concluido')");
  $st->execute([current_user_id(), $total]);

  $pedidoId = $pdo->lastInsertId();

  /* cria os itens do pedido */
  $stItem = $pdo->prepare("INSERT INTO itens_pedido (pedido_id, jogo_id, price) VALUES (?, ?, ?)");

  foreach ($jogos as $jogo) {
    $stItem->execute([
      $pedidoId,
      $jogo['id'],
      $jogo['price']
    ]);
  }

  $pdo->commit();

  cart_clear();

  flash_set('success', 'compra realizada com sucesso!');
  redirect('biblioteca.php');

} catch (Exception $e) {
  $pdo->rollBack();
  flash_set('danger', 'erro ao finalizar a compra.');
  redirect('carrinho.php');
}