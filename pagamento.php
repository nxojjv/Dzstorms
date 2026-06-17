<?php
/* protege a página: só utilizadores logados conseguem aceder ao pagamento */
require_once __DIR__ . '/auth.php';

/* liga à base de dados */
require_once __DIR__ . '/db.php';

/* carrega funções auxiliares, como cart_items(), flash_set() e redirect() */
require_once __DIR__ . '/functions.php';

/* pega os jogos que estão no carrinho */
$cart = cart_items();

/* se o carrinho estiver vazio, o utilizador volta para o carrinho */
if (empty($cart)) {
  flash_set('warning', 'o carrinho está vazio.');
  redirect('carrinho.php');
}

/* cria a ligação com a base de dados */
$pdo = db();

/* verifica se o email do utilizador está confirmado */
$st = $pdo->prepare("SELECT email_verified FROM users WHERE id = ?");
$st->execute([current_user_id()]);
$user = $st->fetch();

/* se o email não estiver verificado, bloqueia a compra */
if (!$user || (int)$user['email_verified'] !== 1) {
  flash_set('danger', 'verifica o email antes de finalizar a compra.');
  redirect('biblioteca.php');
}

/* carrega o cabeçalho do site */
require_once __DIR__ . '/header.php';
?>

<div class="container py-5">
  <div class="card card-dark shadow mx-auto" style="max-width: 650px;">
    <div class="card-body p-5">

      <!-- título da página -->
      <h2 class="fw-bold mb-3 text-center">pagamento demo</h2>

      <!-- explicação para o utilizador -->
      <p class="text-light-emphasis text-center mb-4">
        escolhe um método de pagamento para simular a compra.
      </p>

      <!-- formulário que envia os dados para finalizar_compra.php -->
      <form method="post" action="finalizar_compra.php">

        <!-- escolha do método de pagamento -->
        <div class="mb-3">
          <label class="form-label">método de pagamento</label>

          <select 
            name="metodo_pagamento" 
            id="metodo_pagamento" 
            class="form-control" 
            required 
            onchange="mostrarCampos()"
          >
            <option value="">selecionar...</option>
            <option value="cartao_demo">cartão demo</option>
            <option value="mbway_demo">mb way demo</option>
          </select>
        </div>

        <!-- campos do cartão demo -->
        <div id="cartaoBox" class="d-none">
          <div class="mb-3">
            <label class="form-label">número do cartão</label>
            <input 
              type="text" 
              name="numero_cartao"
              class="form-control" 
              placeholder="4242 4242 4242 4242"
            >
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">validade</label>
              <input 
                type="text" 
                name="validade_cartao"
                class="form-control" 
                placeholder="12/28"
              >
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">cvv</label>
              <input 
                type="text" 
                name="cvv_cartao"
                class="form-control" 
                placeholder="123"
              >
            </div>
          </div>
        </div>

        <!-- campo do MB WAY demo -->
        <div id="mbwayBox" class="d-none">
          <div class="mb-3">
            <label class="form-label">número de telemóvel</label>
            <input 
              type="text" 
              name="telefone_mbway"
              class="form-control" 
              placeholder="912 345 678"
            >
          </div>
        </div>

        <!-- aviso de que não existe pagamento real -->
        <div class="alert alert-info">
          isto é apenas uma simulação. nenhum pagamento real será feito.
        </div>

        <!-- botão para confirmar o pagamento demo -->
        <button class="btn btn-success w-100">
          confirmar pagamento demo
        </button>
      </form>

      <!-- link para voltar ao carrinho -->
      <div class="text-center mt-3">
        <a href="carrinho.php" class="text-light">
          voltar ao carrinho
        </a>
      </div>

    </div>
  </div>
</div>

<script>
/* mostra os campos corretos conforme o método escolhido */
function mostrarCampos() {
  const metodo = document.getElementById('metodo_pagamento').value;
  const cartaoBox = document.getElementById('cartaoBox');
  const mbwayBox = document.getElementById('mbwayBox');

  /* primeiro esconde tudo */
  cartaoBox.classList.add('d-none');
  mbwayBox.classList.add('d-none');

  /* se escolher cartão, mostra os campos do cartão */
  if (metodo === 'cartao_demo') {
    cartaoBox.classList.remove('d-none');
  }

  /* se escolher MB WAY, mostra o campo do telemóvel */
  if (metodo === 'mbway_demo') {
    mbwayBox.classList.remove('d-none');
  }
}
</script>

<?php
/* carrega o rodapé do site */
require_once __DIR__ . '/footer.php';
?>