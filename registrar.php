<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

$verifyLink = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name  = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $pass  = $_POST['password'] ?? '';
  $confirmPass = $_POST['confirm_password'] ?? '';

  if ($name === '' || $email === '' || $pass === '' || $confirmPass === '') {
    flash_set('danger', 'preenche todos os campos.');
    redirect('registrar.php');
  }

  if ($pass !== $confirmPass) {
    flash_set('danger', 'as senhas não coincidem.');
    redirect('registrar.php');
  }

  $pdo = db();

  $st = $pdo->prepare("SELECT id FROM users WHERE email = ?");
  $st->execute([$email]);

  if ($st->fetch()) {
    flash_set('danger', 'este email já está registado.');
    redirect('registrar.php');
  }

  $hash = password_hash($pass, PASSWORD_DEFAULT);
  $token = bin2hex(random_bytes(16));

  $st = $pdo->prepare("
    INSERT INTO users (name, email, password_hash, email_verified, email_verify_token)
    VALUES (?, ?, ?, 0, ?)
  ");
  $st->execute([$name, $email, $hash, $token]);

  $verifyLink = BASE_URL . "/verificar_email.php?token=" . urlencode($token);
}

require_once __DIR__ . '/header.php';
?>

<div class="d-flex justify-content-center align-items-center" style="min-height:70vh;">
  <div class="card card-dark shadow" style="max-width: 460px; width:100%;">
    <div class="card-body p-4">
      <h3 class="fw-bold mb-3 text-center">registar</h3>

      <?php if ($verifyLink): ?>
        <div class="alert alert-success">
          conta criada com sucesso.<br>
          <strong>modo demo:</strong> clica abaixo para verificar o email.
        </div>

        <a href="<?= e($verifyLink) ?>" class="btn btn-success w-100">
          verificar email
        </a>

        <div class="text-center mt-3">
          <a href="entrar.php" class="text-success">ir para login</a>
        </div>

      <?php else: ?>
        <form method="post">
          <div class="mb-3">
            <label class="form-label">nome</label>
            <input class="form-control" name="name" required>
          </div>

          <div class="mb-3">
            <label class="form-label">email</label>
            <input class="form-control" type="email" name="email" required>
          </div>

          <div class="mb-3">
            <label class="form-label">senha</label>
            <div class="input-group">
              <input class="form-control" type="password" name="password" id="password" required>
              <button class="btn btn-outline-light" type="button" onclick="mostrarSenha('password')">
                ver
              </button>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">confirmar senha</label>
            <div class="input-group">
              <input class="form-control" type="password" name="confirm_password" id="confirm_password" required>
              <button class="btn btn-outline-light" type="button" onclick="mostrarSenha('confirm_password')">
                ver
              </button>
            </div>
          </div>

          <button class="btn btn-success w-100">registar</button>
        </form>

        <div class="text-center mt-3">
          <small>
            já tens conta?
            <a href="entrar.php" class="text-success">login</a>
          </small>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
function mostrarSenha(id) {
  const input = document.getElementById(id);
  input.type = input.type === 'password' ? 'text' : 'password';
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>