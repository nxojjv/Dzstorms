<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

/* só permite finalizar se vier da página pagamento.php */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect('carrinho.php');
}

/* verifica o método de pagamento demo */
$metodoPagamento = $_POST['metodo_pagamento'] ?? '';

if (!in_array($metodoPagamento, ['cartao_demo', 'mbway_demo'])) {
  flash_set('danger', 'método de pagamento inválido.');
  redirect('pagamento.php');
}

$idsCarrinho = array_keys(cart_items());

if (empty($idsCarrinho)) {
  flash_set('danger', 'o carrinho está vazio.');
  redirect('carrinho.php');
}

$pdo = db();

/* verifica se o email do utilizador está confirmado */
$st = $pdo->prepare("SELECT email_verified FROM users WHERE id = ?");
$st->execute([current_user_id()]);
$user = $st->fetch();

if (!$user || (int)$user['email_verified'] !== 1) {
  flash_set('danger', 'verifica o email antes de finalizar a compra.');
  redirect('carrinho.php');
}

$placeholders = implode(',', array_fill(0, count($idsCarrinho), '?'));

$st = $pdo->prepare("
  SELECT id, title, price 
  FROM jogos 
  WHERE id IN ($placeholders) AND is_active = 1
");
$st->execute($idsCarrinho);
$jogos = $st->fetchAll();

if (empty($jogos)) {
  flash_set('danger', 'não foi possível finalizar a compra.');
  redirect('carrinho.php');
}

/* impede comprar jogo repetido */
foreach ($jogos as $jogo) {
  $stCheck = $pdo->prepare("
    SELECT ip.id
    FROM pedidos p
    INNER JOIN itens_pedido ip ON ip.pedido_id = p.id
    WHERE p.user_id = ? AND ip.jogo_id = ?
    LIMIT 1
  ");
  $stCheck->execute([current_user_id(), $jogo['id']]);

  if ($stCheck->fetch()) {
    flash_set('warning', 'já compraste um dos jogos do carrinho.');
    redirect('carrinho.php');
  }
}

$total = 0;

foreach ($jogos as $jogo) {
  $total += (float)$jogo['price'];
}

try {
  $pdo->beginTransaction();

  /* cria o pedido */
  $st = $pdo->prepare("
    INSERT INTO pedidos (user_id, total, status)
    VALUES (?, ?, 'concluido')
  ");
  $st->execute([current_user_id(), $total]);

  $pedidoId = $pdo->lastInsertId();

  /* cria os itens do pedido */
  $stItem = $pdo->prepare("
    INSERT INTO itens_pedido (pedido_id, jogo_id, price)
    VALUES (?, ?, ?)
  ");

  foreach ($jogos as $jogo) {
    $stItem->execute([
      $pedidoId,
      $jogo['id'],
      $jogo['price']
    ]);
  }

  $pdo->commit();

  cart_clear();

  if ($metodoPagamento === 'cartao_demo') {
    flash_set('success', 'pagamento demo por cartão aprovado. compra realizada com sucesso!');
  } else {
    flash_set('success', 'pagamento demo por MB WAY aprovado. compra realizada com sucesso!');
  }

  redirect('biblioteca.php');

} catch (Exception $e) {
  $pdo->rollBack();

  flash_set('danger', 'erro ao finalizar a compra.');
  redirect('carrinho.php');
}