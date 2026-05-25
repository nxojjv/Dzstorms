<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

$link = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');

  $pdo = db();

  $st = $pdo->prepare("SELECT id FROM users WHERE email = ?");
  $st->execute([$email]);
  $user = $st->fetch();

  if ($user) {
    $token = bin2hex(random_bytes(16));

    $st = $pdo->prepare("UPDATE users SET reset_token = ? WHERE id = ?");
    $st->execute([$token, $user['id']]);

    $link = BASE_URL . "/redefinir_senha.php?token=" . urlencode($token);
  } else {
    flash_set('danger', 'email não encontrado.');
    redirect('recuperar_senha.php');
  }
}

require_once __DIR__ . '/header.php';
?>

<div class="d-flex justify-content-center align-items-center" style="min-height:70vh;">
  <div class="card card-dark shadow" style="max-width: 430px; width:100%;">
    <div class="card-body p-4">
      <h3 class="fw-bold mb-3 text-center">recuperar senha</h3>

      <?php if ($link): ?>
        <div class="alert alert-success">
          modo demo: clica no botão para redefinir a senha.
        </div>

        <a href="<?= e($link) ?>" class="btn btn-success w-100">
          redefinir senha
        </a>
      <?php else: ?>
        <form method="post">
          <div class="mb-3">
            <label class="form-label">email da conta</label>
            <input type="email" name="email" class="form-control" required>
          </div>

          <button class="btn btn-success w-100">continuar</button>
        </form>
      <?php endif; ?>

      <div class="text-center mt-3">
        <a href="entrar.php" class="text-success">voltar ao login</a>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>