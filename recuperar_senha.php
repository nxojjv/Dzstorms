<?php
/* importa a ligação com a base de dados */
require_once __DIR__ . '/db.php';

/* importa funções auxiliares */
require_once __DIR__ . '/functions.php';

/* importa a função de envio de email */
require_once __DIR__ . '/mail.php';

/* verifica se o formulário foi enviado */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  /* recebe o email enviado no formulário */
  $email = trim($_POST['email'] ?? '');

  /* verifica se o email está vazio */
  if ($email === '') {
    flash_set('danger', 'preenche o email.');
    redirect('recuperar_senha.php');
  }

  /* verifica se o email é válido */
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash_set('danger', 'email inválido.');
    redirect('recuperar_senha.php');
  }

  /* liga à base de dados */
  $pdo = db();

  /* procura o utilizador pelo email */
  $st = $pdo->prepare("SELECT id, name FROM users WHERE email = ?");
  $st->execute([$email]);
  $user = $st->fetch();

  /* se o email não existir */
  if (!$user) {
    flash_set('danger', 'email não encontrado.');
    redirect('recuperar_senha.php');
  }

  /* cria um token seguro para redefinir a senha */
  $token = bin2hex(random_bytes(16));

  /* guarda o token na base de dados */
  $st = $pdo->prepare("UPDATE users SET reset_token = ? WHERE id = ?");
  $st->execute([$token, $user['id']]);

  /* cria o link real de recuperação */
  $link = "http://localhost/dzstorms/redefinir_senha.php?token=" . urlencode($token);

  /* assunto do email */
  $assunto = "Recuperação de senha - DZStorms";

  /* nome do utilizador */
  $nome = $user['name'] ?? 'utilizador';

  /* mensagem do email */
  $mensagem = "
    <h2>Recuperação de senha</h2>

    <p>Olá, " . htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') . ".</p>

    <p>Recebemos um pedido para redefinir a senha da tua conta no DZStorms.</p>

    <p>Para criares uma nova senha, clica no botão abaixo:</p>

    <p>
      <a 
        href='" . htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . "'
        style='
          background:#198754;
          color:white;
          padding:10px 15px;
          text-decoration:none;
          border-radius:5px;
          display:inline-block;
        '
      >
        Redefinir senha
      </a>
    </p>

    <p>Se o botão não funcionar, copia e cola este link no navegador:</p>

    <p>" . htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . "</p>
  ";

  /* envia o email real */
  if (enviar_email($email, $assunto, $mensagem)) {
    flash_set('success', 'enviámos um email com o link para redefinir a senha.');
  } else {
    flash_set('warning', 'houve erro ao enviar o email de recuperação.');
  }

  /* volta para o login */
  redirect('entrar.php');
}

/* carrega o cabeçalho do site */
require_once __DIR__ . '/header.php';
?>

<div class="d-flex justify-content-center align-items-center" style="min-height:70vh;">
  <div class="card card-dark shadow" style="max-width: 430px; width:100%;">
    <div class="card-body p-4">

      <h3 class="fw-bold mb-3 text-center">recuperar senha</h3>

      <!-- formulário para pedir recuperação de senha -->
      <form method="post">

        <div class="mb-3">
          <label class="form-label">email da conta</label>
          <input type="email" name="email" class="form-control" required>
        </div>

        <button class="btn btn-success w-100">
          enviar link de recuperação
        </button>
      </form>

      <div class="text-center mt-3">
        <a href="entrar.php" class="text-success">voltar ao login</a>
      </div>

    </div>
  </div>
</div>

<?php
/* carrega o rodapé do site */
require_once __DIR__ . '/footer.php';
?>