<?php
/*
  Este ficheiro é responsável por remover um jogo do carrinho.

  Ele recebe o ID do jogo através de um formulário POST,
  remove esse jogo da sessão do carrinho e depois volta
  para a página carrinho.php.
*/

/* carrega as configurações principais do projeto */
require_once __DIR__ . '/config.php';

/* carrega funções auxiliares, como redirect(), flash_set() e cart_remove() */
require_once __DIR__ . '/functions.php';

/*
  Verifica se o acesso foi feito por POST.

  Isto evita que alguém remova jogos do carrinho apenas entrando
  diretamente no link pelo navegador.
*/
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect('carrinho.php');
}

/*
  Recebe o ID do jogo enviado pelo formulário.

  O (int) transforma o valor em número inteiro,
  evitando problemas com valores inválidos.
*/
$jogoId = (int)($_POST['jogo_id'] ?? 0);

/*
  Se o ID for menor ou igual a zero,
  significa que o jogo enviado é inválido.
*/
if ($jogoId <= 0) {
  flash_set('danger', 'jogo inválido.');
  redirect('carrinho.php');
}

/*
  Remove o jogo do carrinho.

  A função cart_remove() está no functions.php
  e remove o jogo guardado na sessão.
*/
cart_remove($jogoId);

/* mostra uma mensagem de sucesso para o utilizador */
flash_set('success', 'jogo removido do carrinho.');

/* volta para a página do carrinho */
redirect('carrinho.php');