<?php
// carrega o cabeçalho do site
require_once __DIR__ . '/header.php';
?>

<div class="container py-5">
  <div class="card card-dark shadow mx-auto text-center" style="max-width: 780px;">
    <div class="card-body p-5">

      <!-- título do jogo -->
      <h1 class="fw-bold mb-2">BLOCK BREAKER</h1>

      <!-- descrição do jogo -->
      <p class="text-light-emphasis mb-3">
        move a barra, rebate a bola e destrói todos os blocos.
      </p>

      <!-- painel com informações do jogo -->
      <div class="d-flex justify-content-center gap-4 mb-3 flex-wrap">
        <div>pontos: <strong id="score" class="text-success">0</strong></div>
        <div>vidas: <strong id="vidas" class="text-danger">3</strong></div>
        <div>nível: <strong id="nivel" class="text-info">1</strong></div>
        <div>recorde: <strong id="recorde" class="text-warning">0</strong></div>
      </div>

      <!-- canvas onde o jogo é desenhado -->
      <canvas 
        id="game" 
        width="520" 
        height="420"
        style="
          background:#061006; 
          border:3px solid #25c45a; 
          border-radius:18px; 
          box-shadow:0 0 25px rgba(37,196,90,0.35);
          display:block;
          margin:0 auto;
        ">
      </canvas>

      <!-- mensagem do jogo -->
      <div id="mensagem" class="alert alert-success mt-4 d-none"></div>

      <!-- botões -->
      <div class="mt-4">

        <!-- type="button" evita comportamento de submit -->
        <button type="button" class="btn btn-success" onclick="reiniciarJogo()">
          reiniciar
        </button>

        <a href="biblioteca.php" class="btn btn-outline-light ms-2">
          voltar
        </a>
      </div>

      <!-- instrução -->
      <p class="text-light-emphasis small mt-3 mb-0">
        usa as setas esquerda e direita para mover a barra.
      </p>

    </div>
  </div>
</div>

<script>
/* pega o canvas e prepara o contexto 2D */
const canvas = document.getElementById("game");
const ctx = canvas.getContext("2d");

/* elementos do HTML */
const scoreEl = document.getElementById("score");
const vidasEl = document.getElementById("vidas");
const nivelEl = document.getElementById("nivel");
const recordeEl = document.getElementById("recorde");
const mensagemEl = document.getElementById("mensagem");

/* variáveis principais */
let score = 0;
let vidas = 3;
let nivel = 1;
let acabou = false;

/* guarda o recorde no navegador */
let recorde = localStorage.getItem("blockBreakerRecorde") || 0;
recordeEl.textContent = recorde;

/* guarda o id da animação para conseguir parar ao reiniciar */
let animacaoId = null;

/* dados da barra */
const barra = {
  w: 100,
  h: 14,
  x: canvas.width / 2 - 50,
  y: canvas.height - 35,
  velocidade: 8,
  esquerda: false,
  direita: false
};

/* dados da bola */
const bola = {
  x: canvas.width / 2,
  y: canvas.height - 60,
  r: 8,
  dx: 4,
  dy: -4
};

/* lista dos blocos */
let blocos = [];

/* configuração dos blocos */
const linhas = 5;
const colunas = 8;
const blocoW = 55;
const blocoH = 22;
const espaco = 8;
const topo = 55;
const esquerdaInicio = 20;

/* cria os blocos do jogo */
function criarBlocos() {
  blocos = [];

  for (let l = 0; l < linhas; l++) {
    for (let c = 0; c < colunas; c++) {
      blocos.push({
        x: esquerdaInicio + c * (blocoW + espaco),
        y: topo + l * (blocoH + espaco),
        w: blocoW,
        h: blocoH,
        ativo: true
      });
    }
  }
}

/* função principal do jogo */
function desenhar() {
  if (acabou) return;

  limparTela();
  desenharFundo();
  desenharBlocos();
  desenharBarra();
  desenharBola();
  moverBarra();
  moverBola();

  animacaoId = requestAnimationFrame(desenhar);
}

/* limpa o canvas */
function limparTela() {
  ctx.clearRect(0, 0, canvas.width, canvas.height);
}

/* desenha o fundo do jogo */
function desenharFundo() {
  ctx.fillStyle = "#061006";
  ctx.fillRect(0, 0, canvas.width, canvas.height);

  ctx.strokeStyle = "rgba(37,196,90,0.12)";

  for (let x = 0; x < canvas.width; x += 26) {
    ctx.beginPath();
    ctx.moveTo(x, 0);
    ctx.lineTo(x, canvas.height);
    ctx.stroke();
  }

  for (let y = 0; y < canvas.height; y += 26) {
    ctx.beginPath();
    ctx.moveTo(0, y);
    ctx.lineTo(canvas.width, y);
    ctx.stroke();
  }
}

/* desenha os blocos */
function desenharBlocos() {
  blocos.forEach((b, index) => {
    if (!b.ativo) return;

    const cores = ["#25c45a", "#39d76b", "#61e98b", "#a6ffbf", "#ffffff"];
    ctx.fillStyle = cores[index % cores.length];

    ctx.beginPath();
    ctx.roundRect(b.x, b.y, b.w, b.h, 6);
    ctx.fill();
  });
}

/* desenha a barra */
function desenharBarra() {
  ctx.fillStyle = "#25c45a";
  ctx.beginPath();
  ctx.roundRect(barra.x, barra.y, barra.w, barra.h, 8);
  ctx.fill();

  ctx.fillStyle = "rgba(255,255,255,0.4)";
  ctx.fillRect(barra.x + 15, barra.y + 3, barra.w - 30, 3);
}

/* desenha a bola */
function desenharBola() {
  ctx.fillStyle = "#ffffff";
  ctx.beginPath();
  ctx.arc(bola.x, bola.y, bola.r, 0, Math.PI * 2);
  ctx.fill();
}

/* move a barra conforme as teclas */
function moverBarra() {
  if (barra.esquerda) barra.x -= barra.velocidade;
  if (barra.direita) barra.x += barra.velocidade;

  if (barra.x < 0) barra.x = 0;
  if (barra.x + barra.w > canvas.width) barra.x = canvas.width - barra.w;
}

/* move a bola e verifica colisões */
function moverBola() {
  bola.x += bola.dx;
  bola.y += bola.dy;

  /* colisão nas laterais */
  if (bola.x + bola.r > canvas.width || bola.x - bola.r < 0) {
    bola.dx *= -1;
  }

  /* colisão no topo */
  if (bola.y - bola.r < 0) {
    bola.dy *= -1;
  }

  /* colisão com a barra */
  if (
    bola.x > barra.x &&
    bola.x < barra.x + barra.w &&
    bola.y + bola.r > barra.y &&
    bola.y - bola.r < barra.y + barra.h
  ) {
    bola.dy = -Math.abs(bola.dy);

    const centroBarra = barra.x + barra.w / 2;
    const distancia = bola.x - centroBarra;

    bola.dx = distancia * 0.08;
  }

  verificarBlocos();

  /* se a bola cair, perde vida */
  if (bola.y + bola.r > canvas.height) {
    perderVida();
  }
}

/* verifica colisão da bola com os blocos */
function verificarBlocos() {
  blocos.forEach(b => {
    if (!b.ativo) return;

    if (
      bola.x > b.x &&
      bola.x < b.x + b.w &&
      bola.y - bola.r < b.y + b.h &&
      bola.y + bola.r > b.y
    ) {
      b.ativo = false;
      bola.dy *= -1;

      score += 10;
      scoreEl.textContent = score;

      atualizarRecorde();
      verificarVitoria();
    }
  });
}

/* perde uma vida */
function perderVida() {
  vidas--;
  vidasEl.textContent = vidas;

  if (vidas <= 0) {
    fimDeJogo("fim do jogo!");
    return;
  }

  resetarBola();
}

/* coloca a bola e a barra na posição inicial */
function resetarBola() {
  bola.x = canvas.width / 2;
  bola.y = canvas.height - 60;
  bola.dx = 4 + nivel;
  bola.dy = -4 - nivel;

  barra.x = canvas.width / 2 - barra.w / 2;
}

/* verifica se todos os blocos foram destruídos */
function verificarVitoria() {
  const aindaTemBlocos = blocos.some(b => b.ativo);

  if (!aindaTemBlocos) {
    nivel++;
    nivelEl.textContent = nivel;

    mensagemEl.textContent = "nível concluído! novo nível iniciado.";
    mensagemEl.classList.remove("d-none");

    setTimeout(() => mensagemEl.classList.add("d-none"), 1500);

    criarBlocos();
    resetarBola();
  }
}

/* atualiza o recorde */
function atualizarRecorde() {
  if (score > recorde) {
    recorde = score;
    localStorage.setItem("blockBreakerRecorde", recorde);
    recordeEl.textContent = recorde;
  }
}

/* termina o jogo */
function fimDeJogo(texto) {
  acabou = true;

  if (animacaoId) {
    cancelAnimationFrame(animacaoId);
  }

  mensagemEl.textContent = texto;
  mensagemEl.classList.remove("d-none");

  ctx.fillStyle = "rgba(0,0,0,0.75)";
  ctx.fillRect(0, 0, canvas.width, canvas.height);

  ctx.fillStyle = "#ffffff";
  ctx.font = "28px Arial";
  ctx.textAlign = "center";
  ctx.fillText("fim do jogo", canvas.width / 2, canvas.height / 2 - 20);

  ctx.font = "18px Arial";
  ctx.fillText("pontos: " + score, canvas.width / 2, canvas.height / 2 + 15);
  ctx.fillText("recorde: " + recorde, canvas.width / 2, canvas.height / 2 + 42);
}

/* reinicia o jogo */
function reiniciarJogo() {
  if (animacaoId) {
    cancelAnimationFrame(animacaoId);
  }

  score = 0;
  vidas = 3;
  nivel = 1;
  acabou = false;

  scoreEl.textContent = score;
  vidasEl.textContent = vidas;
  nivelEl.textContent = nivel;
  mensagemEl.classList.add("d-none");

  barra.esquerda = false;
  barra.direita = false;
  barra.x = canvas.width / 2 - barra.w / 2;

  resetarBola();
  criarBlocos();
  desenhar();
}

/* teclas que não podem mexer a página */
const teclasDoJogo = ["ArrowLeft", "ArrowRight", "ArrowUp", "ArrowDown"];

/* quando uma tecla é pressionada */
document.addEventListener("keydown", function(e) {

  /* impede que as setas façam scroll na página */
  if (teclasDoJogo.includes(e.key)) {
    e.preventDefault();
  }

  /* move para a esquerda */
  if (e.key === "ArrowLeft") {
    barra.esquerda = true;
  }

  /* move para a direita */
  if (e.key === "ArrowRight") {
    barra.direita = true;
  }

}, { passive: false });

/* quando a tecla é solta */
document.addEventListener("keyup", function(e) {

  /* impede que as setas façam scroll na página */
  if (teclasDoJogo.includes(e.key)) {
    e.preventDefault();
  }

  /* para de mover para a esquerda */
  if (e.key === "ArrowLeft") {
    barra.esquerda = false;
  }

  /* para de mover para a direita */
  if (e.key === "ArrowRight") {
    barra.direita = false;
  }

}, { passive: false });

/* inicia o jogo */
criarBlocos();
desenhar();
</script>

<?php
// carrega o rodapé do site
require_once __DIR__ . '/footer.php';
?>