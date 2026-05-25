<?php
// Importa o ficheiro de configuração do projeto
// Normalmente contém informações importantes como BASE_URL e outras definições gerais
require_once __DIR__ . '/config.php';

// Importa o ficheiro com funções auxiliares do projeto
// Por exemplo: is_logged_in(), flash_set() e redirect()
require_once __DIR__ . '/functions.php';

// Verifica se o utilizador iniciou sessão
// Se não estiver logado, não pode aceder a esta página
if (!is_logged_in()) {

  // Guarda uma mensagem de erro para avisar o utilizador
  flash_set('danger', 'precisas iniciar sessão.');

  // Redireciona o utilizador para a página de login
  redirect(BASE_URL . '/entrar.php');
}