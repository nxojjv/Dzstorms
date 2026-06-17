<?php

/* endereço base do projeto */
if (!defined('BASE_URL')) {
  define('BASE_URL', 'http://localhost/dzstorms');
}

/* evita problemas ao mostrar textos na página */
function e(string $value): string {
  return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/* redireciona para outra página */
function redirect(string $path): void {
  header("Location: $path");
  exit;
}

/* verifica se o utilizador está logado */
function is_logged_in(): bool {
  return !empty($_SESSION['user']);
}

/* verifica se o utilizador é admin */
function is_admin(): bool {
  return !empty($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'admin';
}

/* pega o id do utilizador logado */
function current_user_id(): ?int {
  return $_SESSION['user']['id'] ?? null;
}

/* guarda uma mensagem temporária */
function flash_set(string $type, string $message): void {
  $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/* mostra a mensagem temporária e depois apaga */
function flash_get(): ?array {
  if (empty($_SESSION['flash'])) return null;
  $f = $_SESSION['flash'];
  unset($_SESSION['flash']);
  return $f;
}

/* adiciona um jogo ao carrinho */
function cart_add(int $gameId): void {
  if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
  $_SESSION['cart'][$gameId] = 1;
}

/* remove um jogo do carrinho */
function cart_remove(int $gameId): void {
  if (isset($_SESSION['cart'][$gameId])) {
    unset($_SESSION['cart'][$gameId]);
  }
}

/* mostra os jogos que estão no carrinho */
function cart_items(): array {
  return $_SESSION['cart'] ?? [];
}

/* limpa o carrinho */
function cart_clear(): void {
  unset($_SESSION['cart']);
}