<?php
// Importa o ficheiro de configuração do projeto
// Aqui normalmente ficam dados importantes, como BASE_URL e ligação/configurações gerais
require_once __DIR__ . '/config.php';

// Importa o ficheiro com funções auxiliares usadas no projeto
// Por exemplo: is_logged_in(), is_admin(), flash_set() e redirect()
require_once __DIR__ . '/functions.php';

// Verifica se o utilizador está autenticado e se tem permissões de administrador
// Se não estiver logado ou não for admin, o acesso à página é bloqueado
if (!is_logged_in() || !is_admin()) {

  // Guarda uma mensagem de erro para ser mostrada ao utilizador
  flash_set('danger', 'acesso negado.');

  // Redireciona o utilizador para a página inicial do site
  redirect(BASE_URL . '/index.php');
}