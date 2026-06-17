<?php
// liga à base de dados
require_once __DIR__ . '/db.php';

// carrega funções auxiliares, como flash_set(), redirect() e e()
require_once __DIR__ . '/functions.php';

// verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // recebe o email e a senha enviados pelo formulário
  $email = trim($_POST['email'] ?? '');
  $senha = $_POST['senha'] ?? '';

  // cria a ligação com a base de dados
  $pdo = db();

  // procura o utilizador pelo email
  $st = $pdo->prepare("
    SELECT id, name, email, password_hash, role, email_verified
    FROM users
    WHERE email = ?
  ");
  $st->execute([$email]);
  $user = $st->fetch();

  // verifica se o utilizador existe e se a senha está correta
  if (!$user || !password_verify($senha, $user['password_hash'])) {
    flash_set('danger', 'email ou senha inválidos.');
    redirect('entrar.php');
  }

  // impede login se o email ainda não estiver verificado
  if ((int)$user['email_verified'] !== 1) {
    flash_set('danger', 'verifica o email antes de entrar.');
    redirect('entrar.php');
  }

  // remove a senha da sessão por segurança
  unset($user['password_hash']);

  // guarda os dados do utilizador na sessão
  $_SESSION['user'] = $user;

  // mostra mensagem de sucesso e envia para a página inicial
  flash_set('success', 'login feito com sucesso.');
  redirect('index.php');
}

// carrega o cabeçalho do site
require_once __DIR__ . '/header.php';
?>

<div class="d-flex justify-content-center align-items-center" style="min-height:70vh;">
  <div class="card card-dark shadow" style="max-width: 430px; width:100%;">
    <div class="card-body p-4">

      <!-- título da página -->
      <h3 class="fw-bold mb-3 text-center">login</h3>

      <!-- formulário de login -->
      <form method="post">

        <!-- campo do email -->
        <div class="mb-3">
          <label class="form-label">email</label>
          <input type="email" name="email" class="form-control" required>
        </div>

        <!-- campo da senha -->
        <div class="mb-3">
          <label class="form-label">senha</label>

          <div class="input-group">
            <input type="password" name="senha" id="senha" class="form-control" required>

            <!-- botão para mostrar ou esconder a senha -->
            <button class="btn btn-outline-light" type="button" onclick="mostrarSenha('senha', 'iconeSenha')">
              <i id="iconeSenha" class="bi bi-eye"></i>
            </button>
          </div>
        </div>

        <!-- botão de entrada -->
        <button class="btn btn-success w-100">entrar</button>
      </form>

      <!-- link para recuperação de senha -->
      <div class="text-center mt-3">
        <small>
          <a href="recuperar_senha.php" class="text-success">esqueci minha senha</a>
        </small>
      </div>

      <!-- link para criar conta -->
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
// função para mostrar ou esconder a senha
function mostrarSenha(inputId, iconId) {
  const input = document.getElementById(inputId);
  const icon = document.getElementById(iconId);

  // se a senha estiver escondida, mostra
  if (input.type === 'password') {
    input.type = 'text';
    icon.className = 'bi bi-eye-slash';
  } else {
    // se a senha estiver visível, esconde novamente
    input.type = 'password';
    icon.className = 'bi bi-eye';
  }
}
</script>

<?php
// carrega o rodapé do site
require_once __DIR__ . '/footer.php';
?>