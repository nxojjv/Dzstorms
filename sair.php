<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

/* apaga os dados do utilizador logado */
unset($_SESSION['user']);

/* limpa também o carrinho */
cart_clear();

/* mensagem de sucesso */
flash_set('success', 'sessão terminada com sucesso.');

/* volta para o login */
redirect('entrar.php');