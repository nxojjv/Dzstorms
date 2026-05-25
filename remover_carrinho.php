<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect('carrinho.php');
}

$jogoId = (int)($_POST['jogo_id'] ?? 0);

if ($jogoId <= 0) {
  flash_set('danger', 'jogo inválido.');
  redirect('carrinho.php');
}

cart_remove($jogoId);

flash_set('success', 'jogo removido do carrinho.');
redirect('carrinho.php');