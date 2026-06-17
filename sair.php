<?php
/*
  Este ficheiro faz o logout do utilizador.

  Ele limpa os dados da sessão, termina a sessão atual
  e envia o utilizador de volta para a página inicial.
*/

/* carrega as configurações principais do projeto e inicia a sessão */
require_once __DIR__ . '/config.php';

/* limpa todas as variáveis guardadas na sessão */
$_SESSION = [];

/* destrói a sessão atual */
session_destroy();

/* redireciona o utilizador para a página inicial */
header('Location: index.php');

/* termina a execução do ficheiro */
exit;