<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$flash = flash_get();
?>
<!doctype html>
<html lang="pt">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>dzstorms</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Ícones do Bootstrap -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  <!-- Fontes -->
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;700;800&display=swap" rel="stylesheet">

  <style>
    body {
      background: url('<?= BASE_URL ?>/img/bg.png') no-repeat center top fixed;
      background-size: cover;
      font-family: Arial, Helvetica, sans-serif;
    }

    body::before {
      content: "";
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.68);
      z-index: -1;
    }

    .navbar {
      background-color: rgba(0,0,0,0.92) !important;
      border-bottom: 1px solid rgba(37,196,90,0.6);
      min-height: 160px;
    }

    .brand-logo {
      height: 145px;
      width: auto;
    }

    .navbar .btn {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 22px;
      padding: 10px 24px;
      border-radius: 8px;
      text-transform: lowercase;
    }

    .cart-btn {
      font-size: 26px !important;
      padding: 9px 18px !important;
      line-height: 1;
    }

    .hero-section {
      min-height: calc(100vh - 160px);
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      padding: 40px 15px;
    }

    .hero-title {
      font-family: 'Orbitron', sans-serif;
      font-size: clamp(46px, 7vw, 96px);
      font-weight: 800;
      font-style: italic;
      text-transform: uppercase;
      letter-spacing: 2px;
      line-height: 1.05;
    }

    .hero-title .green {
      color: #25c45a;
    }

    .hero-subtitle {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 28px;
      letter-spacing: 5px;
      text-transform: uppercase;
      color: rgba(255,255,255,0.75);
    }

    .btn-main {
      background: linear-gradient(90deg, #2ed46b, #15984a);
      border: none;
      color: #06140b;
      font-weight: 700;
      font-size: 22px;
      padding: 14px 42px;
      border-radius: 8px;
      text-transform: uppercase;
      box-shadow: 0 0 22px rgba(37,196,90,0.35);
    }

    .btn-main:hover {
      background: linear-gradient(90deg, #40ec7d, #19aa53);
      color: #000;
    }

    .card-dark {
      background: rgba(20,20,28,0.92);
      border: 1px solid rgba(255,255,255,0.08);
      color: white;
    }

    .footer-text {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 16px;
      letter-spacing: 0;
      color: rgba(255,255,255,0.75);
    }
   .page-title {
  font-family: 'Orbitron', sans-serif;
  font-size: 42px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 3px;
  color: #ffffff;
}

.green-letter {
  color: #25c45a;
}

.page-subtitle {
  font-family: Arial, Helvetica, sans-serif;
  font-size: 15px;
  text-transform: uppercase;
  letter-spacing: 2px;
  color: rgba(255,255,255,0.65);
}
  </style>
</head>

<body class="text-light">

<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container-fluid px-5">
    <a class="navbar-brand d-flex align-items-center" href="<?= BASE_URL ?>/index.php">
      <img src="<?= BASE_URL ?>/img/logo.png" class="brand-logo" alt="logo dzstorms">
    </a>

    <div class="d-flex gap-3 align-items-center">

      <!-- Botão do carrinho -->
      <a class="btn btn-outline-light cart-btn position-relative"
         href="<?= BASE_URL ?>/carrinho.php"
         title="carrinho">
        <i class="bi bi-cart3"></i>

        <?php if (!empty($_SESSION['cart'])): ?>
          <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success">
            <?= count($_SESSION['cart']) ?>
          </span>
        <?php endif; ?>
      </a>

      <?php if (is_logged_in()): ?>
        <a class="btn btn-success" href="<?= BASE_URL ?>/biblioteca.php">biblioteca</a>

        <?php if (is_admin()): ?>
          <a class="btn btn-warning" href="<?= BASE_URL ?>/dashboard.php">admin</a>
        <?php endif; ?>

        <a class="btn btn-outline-danger" href="<?= BASE_URL ?>/sair.php">sair</a>
      <?php else: ?>
        <a class="btn btn-success" href="<?= BASE_URL ?>/entrar.php">login</a>
      <?php endif; ?>

    </div>
  </div>
</nav>

<div class="container-fluid px-0">
  <?php if ($flash): ?>
    <div class="container mt-3">
      <div class="alert alert-<?= e($flash['type']) ?>">
        <?= e($flash['message']) ?>
      </div>
    </div>
  <?php endif; ?>