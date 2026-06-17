<?php
/* importa a ligação com a base de dados */
require_once __DIR__ . '/db.php';

/* importa funções auxiliares */
require_once __DIR__ . '/functions.php';

/* importa a função de envio de email */
require_once __DIR__ . '/mail.php';

/* verifica se o formulário foi enviado */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  /* recebe os dados enviados pelo formulário */
  $name  = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $pass  = $_POST['password'] ?? '';
  $confirmPass = $_POST['confirm_password'] ?? '';

  /* verifica se algum campo está vazio */
  if ($name === '' || $email === '' || $pass === '' || $confirmPass === '') {
    flash_set('danger', 'preenche todos os campos.');
    redirect('registrar.php');
  }

  /* verifica se o email é válido */
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash_set('danger', 'email inválido.');
    redirect('registrar.php');
  }

  /* verifica se as duas senhas são iguais */
  if ($pass !== $confirmPass) {
    flash_set('danger', 'as senhas não coincidem.');
    redirect('registrar.php');
  }

  /* cria a ligação com a base de dados */
  $pdo = db();

  /* verifica se o email já existe na base de dados */
  $st = $pdo->prepare("SELECT id FROM users WHERE email = ?");
  $st->execute([$email]);

  if ($st->fetch()) {
    flash_set('danger', 'este email já está registado.');
    redirect('registrar.php');
  }

  /* cria a senha encriptada */
  $hash = password_hash($pass, PASSWORD_DEFAULT);

  /* cria um token para verificar o email */
  $token = bin2hex(random_bytes(16));

  /* cria o utilizador com o email ainda não verificado */
  $st = $pdo->prepare("
    INSERT INTO users (name, email, password_hash, email_verified, email_verify_token)
    VALUES (?, ?, ?, 0, ?)
  ");
  $st->execute([$name, $email, $hash, $token]);

  /* cria o link de verificação do email */
  $verifyLink = BASE_URL . "/verificar_email.php?token=" . urlencode($token);

  /* assunto do email */
  $assunto = "Verificação de email - DZStorms";

  /* mensagem do email */
  $mensagem = "
    <h2>Bem-vindo ao DZStorms!</h2>

    <p>Olá, " . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . ".</p>

    <p>A tua conta foi criada com sucesso.</p>

    <p>Para ativares a conta, clica no botão abaixo:</p>

    <p>
      <a 
        href='" . htmlspecialchars($verifyLink, ENT_QUOTES, 'UTF-8') . "'
        style='
          background:#198754;
          color:white;
          padding:10px 15px;
          text-decoration:none;
          border-radius:5px;
          display:inline-block;
        '
      >
        Verificar email
      </a>
    </p>

    <p>Se o botão não funcionar, copia e cola este link no navegador:</p>

    <p>" . htmlspecialchars($verifyLink, ENT_QUOTES, 'UTF-8') . "</p>
  ";

  /* envia o email real */
  if (enviar_email($email, $assunto, $mensagem)) {
    flash_set('success', 'conta criada com sucesso. verifica o teu email para ativar a conta.');
  } else {
    flash_set('warning', 'conta criada, mas houve erro ao enviar o email de verificação.');
  }

  /* depois do registo, manda para o login */
  redirect('entrar.php');
}

/* carrega o cabeçalho do site */
require_once __DIR__ . '/header.php';
?>

<div class="d-flex justify-content-center align-items-center" style="min-height:70vh;">
  <div class="card card-dark shadow" style="max-width: 460px; width:100%;">
    <div class="card-body p-4">

      <!-- título da página -->
      <h3 class="fw-bold mb-3 text-center">registar</h3>

      <!-- formulário de registo -->
      <form method="post">

        <!-- campo do nome -->
        <div class="mb-3">
          <label class="form-label">nome</label>
          <input class="form-control" name="name" required>
        </div>

        <!-- campo do email -->
        <div class="mb-3">
          <label class="form-label">email</label>
          <input class="form-control" type="email" name="email" required>
        </div>

        <!-- campo da senha -->
        <div class="mb-3">
          <label class="form-label">senha</label>

          <div class="input-group">
            <input 
              class="form-control" 
              type="password" 
              name="password" 
              id="password" 
              required
            >

            <!-- botão com ícone de olho -->
            <button 
              class="btn btn-outline-light" 
              type="button" 
              onclick="mostrarSenha('password', 'iconePassword')"
            >
              <i id="iconePassword" class="bi bi-eye"></i>
            </button>
          </div>
        </div>

        <!-- campo para confirmar a senha -->
        <div class="mb-3">
          <label class="form-label">confirmar senha</label>

          <div class="input-group">
            <input 
              class="form-control" 
              type="password" 
              name="confirm_password" 
              id="confirm_password" 
              required
            >

            <!-- botão com ícone de olho -->
            <button 
              class="btn btn-outline-light" 
              type="button" 
              onclick="mostrarSenha('confirm_password', 'iconeConfirmPassword')"
            >
              <i id="iconeConfirmPassword" class="bi bi-eye"></i>
            </button>
          </div>
        </div>

        <!-- botão para registar -->
        <button class="btn btn-success w-100">
          registar
        </button>
      </form>

      <!-- link para login caso já tenha conta -->
      <div class="text-center mt-3">
        <small>
          já tens conta?
          <a href="entrar.php" class="text-success">login</a>
        </small>
      </div>

    </div>
  </div>
</div>

<script>
/* função para mostrar ou esconder a senha */
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

<?php
/* carrega o rodapé do site */
require_once __DIR__ . '/footer.php';
?>