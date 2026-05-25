<?php
/* carrega o cabeçalho do site */
require_once __DIR__ . '/header.php';
?>

<div class="container py-5">
  <div class="card card-dark shadow mx-auto" style="max-width: 850px;">
    <div class="card-body p-5 text-center">

      <h1 class="fw-bold mb-3">Memory Storm</h1>

      <p class="text-light-emphasis mb-4">
        Encontra todos os pares para vencer o jogo.
      </p>

      <div class="mb-3">
        <span class="fw-bold">Tentativas:</span>
        <span id="tentativas" class="text-success fw-bold">0</span>
      </div>

      <div id="mensagem" class="alert alert-success d-none">
        parabéns, encontraste todos os pares!
      </div>

      <div id="tabuleiro" class="memory-board mx-auto mb-4"></div>

      <a href="biblioteca.php" class="btn btn-outline-light">
        voltar à biblioteca
      </a>

    </div>
  </div>
</div>

<style>
  .memory-board {
    display: grid;
    grid-template-columns: repeat(4, 90px);
    gap: 15px;
    justify-content: center;
  }

  .memory-card {
    height: 90px;
    background: rgba(37, 196, 90, 0.18);
    border: 1px solid rgba(37, 196, 90, 0.6);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 34px;
    cursor: pointer;
    user-select: none;
  }

  .memory-card.aberta,
  .memory-card.encontrada {
    background: rgba(37, 196, 90, 0.85);
    color: #06140b;
  }

  .memory-card.encontrada {
    cursor: default;
  }
</style>

<script>
/* símbolos usados nas cartas */
const simbolos = ["🎮", "⚡", "🔥", "🕹️", "💎", "🚀"];

/* cria os pares */
let cartas = [...simbolos, ...simbolos];

/* mistura as cartas */
cartas.sort(() => Math.random() - 0.5);

/* variáveis do jogo */
let primeiraCarta = null;
let segundaCarta = null;
let bloqueado = false;
let tentativas = 0;
let paresEncontrados = 0;

/* elementos da página */
const tabuleiro = document.getElementById("tabuleiro");
const tentativasEl = document.getElementById("tentativas");
const mensagemEl = document.getElementById("mensagem");

/* cria as cartas no ecrã */
cartas.forEach(function(simbolo) {
  const carta = document.createElement("div");
  carta.className = "memory-card";
  carta.dataset.valor = simbolo;
  carta.textContent = "?";

  carta.addEventListener("click", function() {
    virarCarta(carta);
  });

  tabuleiro.appendChild(carta);
});

/* vira uma carta */
function virarCarta(carta) {
  if (bloqueado || carta.classList.contains("aberta") || carta.classList.contains("encontrada")) {
    return;
  }

  carta.textContent = carta.dataset.valor;
  carta.classList.add("aberta");

  if (!primeiraCarta) {
    primeiraCarta = carta;
    return;
  }

  segundaCarta = carta;
  tentativas++;
  tentativasEl.textContent = tentativas;

  verificarPar();
}

/* verifica se as duas cartas são iguais */
function verificarPar() {
  if (primeiraCarta.dataset.valor === segundaCarta.dataset.valor) {
    primeiraCarta.classList.remove("aberta");
    segundaCarta.classList.remove("aberta");

    primeiraCarta.classList.add("encontrada");
    segundaCarta.classList.add("encontrada");

    paresEncontrados++;

    limparSelecao();

    if (paresEncontrados === simbolos.length) {
      mensagemEl.classList.remove("d-none");
    }
  } else {
    bloqueado = true;

    setTimeout(function() {
      primeiraCarta.textContent = "?";
      segundaCarta.textContent = "?";

      primeiraCarta.classList.remove("aberta");
      segundaCarta.classList.remove("aberta");

      limparSelecao();
      bloqueado = false;
    }, 800);
  }
}

/* limpa as cartas selecionadas */
function limparSelecao() {
  primeiraCarta = null;
  segundaCarta = null;
}
</script>

<?php
/* carrega o rodapé */
require_once __DIR__ . '/footer.php';
?>