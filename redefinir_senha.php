<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

$token = $_GET['token'] ?? '';

if ($token === '') {
  flash_set('danger', 'token inválido.');
  redirect('entrar.php');
}

$pdo = db();

$st = $pdo->prepare("SELECT id FROM users WHERE reset_token = ?");
$st->execute([$token]);
$user = $st->fetch();

if (!$user) {
  flash_set('danger', 'token inválido ou já utilizado.');
  redirect('entrar.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $senha = $_POST['senha'] ?? '';
  $confirmar = $_POST['confirmar_senha'] ?? '';

  if ($senha === '' || $confirmar === '') {
    flash_set('danger', 'preenche todos os campos.');
    redirect('redefinir_senha.php?token=' . urlencode($token));
  }

  if ($senha !== $confirmar) {
    flash_set('danger', 'as senhas não coincidem.');
    redirect('redefinir_senha.php?token=' . urlencode($token));
  }

  $hash = password_hash($senha, PASSWORD_DEFAULT);

  $st = $pdo->prepare("
    UPDATE users
    SET password_hash = ?, reset_token = NULL
    WHERE id = ?
  ");
  $st->execute([$hash, $user['id']]);

  flash_set('success', 'senha alterada com sucesso.');
  redirect('entrar.php');
}

require_once __DIR__ . '/header.php';
?>

<div class="d-flex justify-content-center align-items-center" style="min-height:70vh;">
  <div class="card card-dark shadow" style="max-width: 430px; width:100%;">
    <div class="card-body p-4">
      <h3 class="fw-bold mb-3 text-center">nova senha</h3>

      <form method="post">
        <div class="mb-3">
          <label class="form-label">nova senha</label>
          <input type="password" name="senha" id="senha" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label">confirmar senha</label>
          <input type="password" name="confirmar_senha" id="confirmar_senha" class="form-control" required>
        </div>

        <button class="btn btn-outline-light w-100 mb-3" type="button" onclick="mostrarSenhas()">
          ver senha
        </button>

        <button class="btn btn-success w-100">alterar senha</button>
      </form>
    </div>
  </div>
</div>

<script>
function mostrarSenhas() {
  const senha = document.getElementById('senha');
  const confirmar = document.getElementById('confirmar_senha');

  senha.type = senha.type === 'password' ? 'text' : 'password';
  confirmar.type = confirmar.type === 'password' ? 'text' : 'password';
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>