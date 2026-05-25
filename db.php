<?php
// Importa o ficheiro de configuração
// É nele que estão definidos os dados da base de dados, como DB_HOST, DB_NAME, DB_USER e DB_PASS
require_once __DIR__ . '/config.php';

// Cria uma função chamada db()
// Esta função serve para fazer a ligação à base de dados e devolver essa ligação
function db(): PDO {

  // Cria uma variável estática para guardar a ligação
  // Como é static, ela mantém o valor mesmo depois da função terminar
  static $pdo = null;

  // Verifica se ainda não existe uma ligação criada
  if ($pdo === null) {

    // Monta a string de ligação à base de dados
    // Aqui é definido o servidor, o nome da base de dados e o charset
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

    // Cria a ligação à base de dados usando PDO
    // DB_USER e DB_PASS vêm do ficheiro config.php
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [

      // Faz com que erros da base de dados sejam mostrados como exceções
      // Isto ajuda a encontrar problemas no código mais facilmente
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

      // Faz com que os resultados venham em formato de array associativo
      // Assim podemos usar, por exemplo: $jogo['title']
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
  }

  // Devolve a ligação à base de dados
  return $pdo;
}