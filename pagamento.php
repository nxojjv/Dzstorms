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
      <!-- onsubmit chama a função validarPagamento antes de enviar -->
      <form method="post" action="finalizar_compra.php" onsubmit="return validarPagamento()">

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

          <!-- número do cartão demo -->
          <div class="mb-3">
            <label class="form-label">número do cartão</label>
            <input 
              type="text" 
              name="numero_cartao"
              id="numero_cartao"
              class="form-control" 
              placeholder="4242 4242 4242 4242"
              minlength="13"
              maxlength="19"
            >
          </div>

          <div class="row">

            <!-- validade do cartão demo -->
            <div class="col-md-6 mb-3">
              <label class="form-label">validade</label>
              <input 
                type="text" 
                name="validade_cartao"
                id="validade_cartao"
                class="form-control" 
                placeholder="12/28"
                maxlength="5"
              >
            </div>

            <!-- CVV do cartão demo -->
            <div class="col-md-6 mb-3">
              <label class="form-label">cvv</label>
              <input 
                type="text" 
                name="cvv_cartao"
                id="cvv_cartao"
                class="form-control" 
                placeholder="123"
                minlength="3"
                maxlength="4"
              >
            </div>
          </div>
        </div>

        <!-- campo do MB WAY demo -->
        <div id="mbwayBox" class="d-none">

          <!-- número de telemóvel -->
          <div class="mb-3">
            <label class="form-label">número de telemóvel</label>
            <input 
              type="text" 
              name="telefone_mbway"
              id="telefone_mbway"
              class="form-control" 
              placeholder="912 345 678"
              minlength="9"
              maxlength="11"
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

  const numeroCartao = document.getElementById('numero_cartao');
  const validadeCartao = document.getElementById('validade_cartao');
  const cvvCartao = document.getElementById('cvv_cartao');
  const telefoneMbway = document.getElementById('telefone_mbway');

  /* primeiro esconde tudo */
  cartaoBox.classList.add('d-none');
  mbwayBox.classList.add('d-none');

  /* remove obrigatoriedade de todos os campos */
  numeroCartao.required = false;
  validadeCartao.required = false;
  cvvCartao.required = false;
  telefoneMbway.required = false;

  /* se escolher cartão, mostra e obriga os campos do cartão */
  if (metodo === 'cartao_demo') {
    cartaoBox.classList.remove('d-none');

    numeroCartao.required = true;
    validadeCartao.required = true;
    cvvCartao.required = true;
  }

  /* se escolher MB WAY, mostra e obriga o telemóvel */
  if (metodo === 'mbway_demo') {
    mbwayBox.classList.remove('d-none');

    telefoneMbway.required = true;
  }
}

/* valida o formulário antes de enviar para finalizar_compra.php */
function validarPagamento() {
  const metodo = document.getElementById('metodo_pagamento').value;

  const numeroCartao = document.getElementById('numero_cartao').value.trim();
  const validadeCartao = document.getElementById('validade_cartao').value.trim();
  const cvvCartao = document.getElementById('cvv_cartao').value.trim();
  const telefoneMbway = document.getElementById('telefone_mbway').value.trim();

  /* se não escolher método, não deixa avançar */
  if (metodo === '') {
    alert('Escolhe um método de pagamento.');
    return false;
  }

  /* valida cartão demo */
  if (metodo === 'cartao_demo') {
    if (numeroCartao === '' || validadeCartao === '' || cvvCartao === '') {
      alert('Preenche os dados do cartão demo.');
      return false;
    }
  }

  /* valida MB WAY demo */
  if (metodo === 'mbway_demo') {
    if (telefoneMbway === '') {
      alert('Preenche o número de telemóvel do MB WAY demo.');
      return false;
    }
  }

  /* se estiver tudo preenchido, deixa finalizar */
  return true;
}

/* chama a função ao carregar a página para garantir o estado correto */
mostrarCampos();
</script>

<?php
/* carrega o rodapé do site */
require_once __DIR__ . '/footer.php';
?>