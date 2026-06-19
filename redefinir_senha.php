<?php
/* importa a ligação com a base de dados */
require_once __DIR__ . '/db.php';

/* importa funções auxiliares */
require_once __DIR__ . '/functions.php';

/* recebe o token enviado pelo link do email */
$token = $_GET['token'] ?? '';

/* se não existir token, volta para o login */
if ($token === '') {
  flash_set('danger', 'token inválido.');
  redirect('entrar.php');
}

/* liga à base de dados */
$pdo = db();

/* procura o utilizador que possui esse token de recuperação */
$st = $pdo->prepare("SELECT id FROM users WHERE reset_token = ?");
$st->execute([$token]);
$user = $st->fetch();

/* se não encontrar utilizador, o token é inválido ou já foi usado */
if (!$user) {
  flash_set('danger', 'token inválido ou já utilizado.');
  redirect('entrar.php');
}

/* verifica se o formulário foi enviado */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  /* recebe as novas senhas */
  $senha = $_POST['senha'] ?? '';
  $confirmar = $_POST['confirmar_senha'] ?? '';

  /* verifica se os campos estão preenchidos */
  if ($senha === '' || $confirmar === '') {
    flash_set('danger', 'preenche todos os campos.');
    redirect('redefinir_senha.php?token=' . urlencode($token));
  }

  /* verifica se as senhas coincidem */
  if ($senha !== $confirmar) {
    flash_set('danger', 'as senhas não coincidem.');
    redirect('redefinir_senha.php?token=' . urlencode($token));
  }

  /* encripta a nova senha */
  $hash = password_hash($senha, PASSWORD_DEFAULT);

  /*
    atualiza a senha do utilizador e remove o token,
    para impedir que o mesmo link seja usado novamente
  */
  $st = $pdo->prepare("
    UPDATE users
    SET password_hash = ?, reset_token = NULL
    WHERE id = ?
  ");
  $st->execute([$hash, $user['id']]);

  /* mostra mensagem de sucesso e volta para o login */
  flash_set('success', 'senha alterada com sucesso. já podes entrar com a nova senha.');
  redirect('entrar.php');
}

/* carrega o cabeçalho do site */
require_once __DIR__ . '/header.php';
?>

<div class="d-flex justify-content-center align-items-center" style="min-height:70vh;">
  <div class="card card-dark shadow" style="max-width: 430px; width:100%;">
    <div class="card-body p-4">

      <h3 class="fw-bold mb-3 text-center">redefinir senha</h3>

      <p class="text-light-emphasis text-center small mb-4">
        cria uma nova senha para aceder à tua conta.
      </p>

      <!-- formulário para criar nova senha -->
      <form method="post">

        <div class="mb-3">
          <label class="form-label">nova senha</label>
          <input 
            type="password" 
            name="senha" 
            id="senha" 
            class="form-control" 
            required
          >
        </div>

        <div class="mb-3">
          <label class="form-label">confirmar nova senha</label>
          <input 
            type="password" 
            name="confirmar_senha" 
            id="confirmar_senha" 
            class="form-control" 
            required
          >
        </div>

        <button 
          class="btn btn-outline-light w-100 mb-3" 
          type="button" 
          onclick="mostrarSenhas()"
        >
          mostrar / esconder senha
        </button>

        <button class="btn btn-success w-100">
          alterar senha
        </button>
      </form>

      <div class="text-center mt-3">
        <a href="entrar.php" class="text-success">voltar ao login</a>
      </div>

    </div>
  </div>
</div>

<script>
/* mostra ou esconde os campos de senha */
function mostrarSenhas() {
  const senha = document.getElementById('senha');
  const confirmar = document.getElementById('confirmar_senha');

  senha.type = senha.type === 'password' ? 'text' : 'password';
  confirmar.type = confirmar.type === 'password' ? 'text' : 'password';
}
</script>

<?php 
/* carrega o rodapé do site */
require_once __DIR__ . '/footer.php'; 
?>