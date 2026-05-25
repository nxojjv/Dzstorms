<?php
// Inicia a sessão do utilizador
// A sessão permite guardar informações temporárias, como login e carrinho
session_start();

// Define o endereço do servidor da base de dados
// Neste caso, está a usar o servidor local do computador
define('DB_HOST', '127.0.0.1');

// Define o nome da base de dados usada no projeto
define('DB_NAME', 'dzstorms');

// Define o utilizador da base de dados
// Em ambiente local, normalmente é root
define('DB_USER', 'root');

// Define a palavra-passe da base de dados
// Neste caso está vazia porque provavelmente está a usar XAMPP/local
define('DB_PASS', '');

// Define o caminho base do projeto no navegador
// Ajuda a criar links corretos dentro do site
define('BASE_URL', '/dzstorms');