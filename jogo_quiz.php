<?php
require_once __DIR__ . '/header.php';
?>

<div class="container py-5">
  <div class="card card-dark shadow mx-auto text-center" style="max-width: 620px;">
    <div class="card-body p-5">
      <h1 class="fw-bold mb-3">Jogo da Velha</h1>

      <p id="mensagem" class="text-light-emphasis mb-4">
        tua vez: joga com X
      </p>

      <div class="velha-board mx-auto mb-4">
        <?php for ($i = 0; $i < 9; $i++): ?>
          <button class="velha-cell" onclick="jogadaHumano(<?= $i ?>)"></button>
        <?php endfor; ?>
      </div>

      <button class="btn btn-success" onclick="reiniciar()">
        reiniciar
      </button>

      <a href="biblioteca.php" class="btn btn-outline-light ms-2">
        voltar
      </a>
    </div>
  </div>
</div>

<style>
  .velha-board {
    display: grid;
    grid-template-columns: repeat(3, 100px);
    gap: 10px;
    justify-content: center;
  }

  .velha-cell {
    width: 100px;
    height: 100px;
    font-size: 42px;
    font-weight: bold;
    background: rgba(0,0,0,0.45);
    color: #25c45a;
    border: 2px solid rgba(37,196,90,0.7);
    border-radius: 12px;
  }

  .velha-cell:hover {
    background: rgba(37,196,90,0.15);
  }
</style>

<script>
let tabuleiro = ["", "", "", "", "", "", "", "", ""];
let acabou = false;

const celulas = document.querySelectorAll(".velha-cell");
const mensagem = document.getElementById("mensagem");

function jogadaHumano(posicao) {
  if (acabou || tabuleiro[posicao] !== "") return;

  tabuleiro[posicao] = "X";
  celulas[posicao].textContent = "X";

  if (verificarFim("X")) return;

  mensagem.textContent = "vez da IA...";

  setTimeout(jogadaIA, 500);
}

function jogadaIA() {
  if (acabou) return;

  let posicao = melhorJogada();

  if (posicao !== null) {
    tabuleiro[posicao] = "O";
    celulas[posicao].textContent = "O";
  }

  if (verificarFim("O")) return;

  mensagem.textContent = "tua vez: joga com X";
}

function melhorJogada() {
  let vazias = tabuleiro
    .map((valor, index) => valor === "" ? index : null)
    .filter(index => index !== null);

  if (vazias.length === 0) return null;

  // 1. tenta ganhar
  for (let posicao of vazias) {
    tabuleiro[posicao] = "O";
    if (venceu("O")) {
      tabuleiro[posicao] = "";
      return posicao;
    }
    tabuleiro[posicao] = "";
  }

  // 2. tenta bloquear o jogador
  for (let posicao of vazias) {
    tabuleiro[posicao] = "X";
    if (venceu("X")) {
      tabuleiro[posicao] = "";
      return posicao;
    }
    tabuleiro[posicao] = "";
  }

  // 3. tenta jogar no centro
  if (tabuleiro[4] === "") return 4;

  // 4. tenta jogar nos cantos
  const cantos = [0, 2, 6, 8].filter(posicao => tabuleiro[posicao] === "");
  if (cantos.length > 0) {
    return cantos[Math.floor(Math.random() * cantos.length)];
  }

  // 5. joga em qualquer posição livre
  return vazias[Math.floor(Math.random() * vazias.length)];
}

function verificarFim(jogador) {
  if (venceu(jogador)) {
    if (jogador === "X") {
      mensagem.textContent = "ganhaste!";
    } else {
      mensagem.textContent = "a IA venceu!";
    }

    acabou = true;
    return true;
  }

  if (!tabuleiro.includes("")) {
    mensagem.textContent = "empate!";
    acabou = true;
    return true;
  }

  return false;
}

function venceu(jogador) {
  const combinacoes = [
    [0,1,2], [3,4,5], [6,7,8],
    [0,3,6], [1,4,7], [2,5,8],
    [0,4,8], [2,4,6]
  ];

  return combinacoes.some(function(combo) {
    return combo.every(function(posicao) {
      return tabuleiro[posicao] === jogador;
    });
  });
}

function reiniciar() {
  tabuleiro = ["", "", "", "", "", "", "", "", ""];
  acabou = false;
  mensagem.textContent = "tua vez: joga com X";

  celulas.forEach(function(celula) {
    celula.textContent = "";
  });
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>