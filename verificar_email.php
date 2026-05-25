<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

/* pega o token que veio no link */
$token = $_GET['token'] ?? '';

if ($token === '') {
  flash_set('danger', 'token inválido.');
  redirect('entrar.php');
}

$pdo = db();

/* procura o utilizador com esse token */
$st = $pdo->prepare("SELECT id FROM users WHERE email_verify_token = ?");
$st->execute([$token]);
$user = $st->fetch();

if (!$user) {
  flash_set('danger', 'token inválido ou já utilizado.');
  redirect('entrar.php');
}

/* marca o email como verificado */
$st = $pdo->prepare("
  UPDATE users
  SET email_verified = 1, email_verify_token = NULL
  WHERE id = ?
");
$st->execute([$user['id']]);

flash_set('success', 'email verificado com sucesso. agora já podes entrar.');
redirect('entrar.php');