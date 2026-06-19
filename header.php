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

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Ícones do Bootstrap -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  <!-- Fontes do Google -->
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;700;800&family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    body {
      background: url('<?= BASE_URL ?>/img/bg.png') no-repeat center top fixed;
      background-size: cover;
      font-family: 'Rajdhani', Arial, Helvetica, sans-serif;
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
      font-family: 'Rajdhani', Arial, Helvetica, sans-serif;
      font-size: 22px;
      font-weight: 600;
      padding: 10px 24px;
      border-radius: 8px;
      text-transform: lowercase;
    }

    .cart-btn {
      font-size: 26px !important;
      padding: 9px 18px !important;
      line-height: 1;
    }

    .login-btn {
      font-weight: 700;
    }

    .account-menu {
      background: rgba(20,20,28,0.98);
      border: 1px solid rgba(37,196,90,0.45);
      border-radius: 10px;
      padding: 8px;
      min-width: 220px;
      box-shadow: 0 0 18px rgba(37,196,90,0.25);
    }

    .account-menu .dropdown-item {
      color: #fff;
      font-family: 'Rajdhani', Arial, Helvetica, sans-serif;
      font-size: 18px;
      font-weight: 500;
      border-radius: 8px;
      padding: 10px 12px;
      text-transform: lowercase;
    }

    .account-menu .dropdown-item:hover {
      background: rgba(37,196,90,0.18);
      color: #25c45a;
    }

    .account-menu .logout-item {
      color: #ff4b5c !important;
      font-weight: 700;
    }

    .account-menu .logout-item:hover {
      background: rgba(220,53,69,0.18);
      color: #ff6b79 !important;
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
      font-family: 'Rajdhani', Arial, Helvetica, sans-serif;
      font-size: 28px;
      font-weight: 500;
      letter-spacing: 5px;
      text-transform: uppercase;
      color: rgba(255,255,255,0.75);
    }

    .btn-main {
      background: linear-gradient(90deg, #2ed46b, #15984a);
      border: none;
      color: #06140b;
      font-family: 'Rajdhani', Arial, Helvetica, sans-serif;
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
      font-family: 'Rajdhani', Arial, Helvetica, sans-serif;
    }

    .footer-text {
      font-family: 'Rajdhani', Arial, Helvetica, sans-serif;
      font-size: 17px;
      font-weight: 500;
      letter-spacing: 0.5px;
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
      font-family: 'Rajdhani', Arial, Helvetica, sans-serif;
      font-size: 15px;
      font-weight: 500;
      text-transform: uppercase;
      letter-spacing: 2px;
      color: rgba(255,255,255,0.65);
    }

    input,
    textarea,
    select,
    button {
      font-family: 'Rajdhani', Arial, Helvetica, sans-serif;
    }

    .alert {
      font-family: 'Rajdhani', Arial, Helvetica, sans-serif;
      font-size: 18px;
      font-weight: 500;
    }
  </style>
</head>

<body class="text-light">

<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container-fluid px-5">

    <!-- Logo -->
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

        <!-- Menu da conta -->
        <div class="dropdown">
          <button 
            class="btn btn-success dropdown-toggle login-btn" 
            type="button" 
            data-bs-toggle="dropdown" 
            aria-expanded="false"
          >
            <i class="bi bi-person-circle me-2"></i>
            conta
          </button>

          <ul class="dropdown-menu dropdown-menu-end account-menu">

            <li>
              <a class="dropdown-item" href="<?= BASE_URL ?>/biblioteca.php">
                <i class="bi bi-collection-play me-2"></i>
                biblioteca
              </a>
            </li>

            <?php if (is_admin()): ?>
              <li>
                <a class="dropdown-item" href="<?= BASE_URL ?>/dashboard.php">
                  <i class="bi bi-shield-lock-fill me-2"></i>
                  admin
                </a>
              </li>
            <?php endif; ?>

            <li><hr class="dropdown-divider border-secondary"></li>

            <li>
              <a class="dropdown-item logout-item" href="<?= BASE_URL ?>/sair.php">
                <i class="bi bi-box-arrow-right me-2"></i>
                sair
              </a>
            </li>

          </ul>
        </div>

      <?php else: ?>

        <!-- Botão de login -->
        <a class="btn btn-success login-btn" href="<?= BASE_URL ?>/entrar.php">
          <i class="bi bi-person-circle me-2"></i>
          login
        </a>

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