<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $senha = $_POST['senha'] ?? '';

  $pdo = db();

  $st = $pdo->prepare("
    SELECT id, name, email, password_hash, role, email_verified
    FROM users
    WHERE email = ?
  ");
  $st->execute([$email]);
  $user = $st->fetch();

  if (!$user || !password_verify($senha, $user['password_hash'])) {
    flash_set('danger', 'email ou senha inválidos.');
    redirect('entrar.php');
  }

  if ((int)$user['email_verified'] !== 1) {
    flash_set('danger', 'verifica o email antes de entrar.');
    redirect('entrar.php');
  }

  unset($user['password_hash']);
  $_SESSION['user'] = $user;

  flash_set('success', 'login feito com sucesso.');
  redirect('index.php');
}

require_once __DIR__ . '/header.php';
?>

<div class="d-flex justify-content-center align-items-center" style="min-height:70vh;">
  <div class="card card-dark shadow" style="max-width: 430px; width:100%;">
    <div class="card-body p-4">
      <h3 class="fw-bold mb-3 text-center">login</h3>

      <form method="post">
        <div class="mb-3">
          <label class="form-label">email</label>
          <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label">senha</label>

          <div class="input-group">
            <input type="password" name="senha" id="senha" class="form-control" required>

            <button class="btn btn-outline-light" type="button" onclick="mostrarSenha('senha', 'iconeSenha')">
              <i id="iconeSenha" class="bi bi-eye"></i>
            </button>
          </div>
        </div>

        <button class="btn btn-success w-100">entrar</button>
      </form>

      <div class="text-center mt-3">
        <small>
          <a href="recuperar_senha.php" class="text-success">esqueci minha senha</a>
        </small>
      </div>

      <div class="text-center mt-2">
        <small>
          ainda não tem conta?
          <a href="registrar.php" class="text-success">registar</a>
        </small>
      </div>
    </div>
  </div>
</div>

<script>
function mostrarSenha(inputId, iconId) {
  const input = document.getElementById(inputId);
  const icon = document.getElementById(iconId);

  if (input.type === 'password') {
    input.type = 'text';
    icon.className = 'bi bi-eye-slash';
  } else {
    input.type = 'password';
    icon.className = 'bi bi-eye';
  }
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>