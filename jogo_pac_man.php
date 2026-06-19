<?php
/* chama o cabeçalho do site */
require_once __DIR__ . '/header.php';
?>

<style>
  /* área geral da página do jogo */
  .pacman-page {
    min-height: calc(100vh - 160px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 45px 15px;
  }

  /* caixa onde fica o jogo */
  .pacman-card {
    width: 100%;
    max-width: 900px;
    background: rgba(10, 12, 18, 0.94);
    border: 1px solid rgba(37,196,90,0.45);
    border-radius: 18px;
    padding: 28px;
    box-shadow: 0 0 30px rgba(37,196,90,0.18);
    text-align: center;
  }

  /* título PAC-MAN */
  .pacman-title {
    font-family: 'Orbitron', sans-serif;
    font-size: 42px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 3px;
    margin-bottom: 8px;
  }

  .pacman-title span {
    color: #25c45a;
  }

  .pacman-subtitle {
    color: rgba(255,255,255,0.65);
    font-size: 18px;
    margin-bottom: 20px;
  }

  /* informações: pontos, vidas, recorde e estado */
  .pacman-info {
    display: flex;
    justify-content: center;
    gap: 18px;
    flex-wrap: wrap;
    margin-bottom: 18px;
  }

  .pacman-badge {
    background: rgba(37,196,90,0.12);
    border: 1px solid rgba(37,196,90,0.45);
    color: #fff;
    border-radius: 10px;
    padding: 10px 16px;
    font-size: 18px;
    font-weight: 600;
  }

  .pacman-badge span {
    color: #25c45a;
    font-weight: 700;
  }

  /* moldura do canvas */
  .canvas-wrap {
    display: inline-block;
    padding: 12px;
    border-radius: 18px;
    background: rgba(0,0,0,0.55);
    border: 1px solid rgba(255,255,255,0.08);
    box-shadow: inset 0 0 18px rgba(0,0,0,0.7);
  }

  /* área onde o jogo é desenhado */
  canvas {
    display: block;
    max-width: 100%;
    height: auto;
    border-radius: 12px;
    background: #02040a;
    border: 3px solid #214cff;
    box-shadow: 0 0 22px rgba(33,76,255,0.35);
  }

  /* botões */
  .pacman-controls {
    margin-top: 18px;
    display: flex;
    justify-content: center;
    gap: 12px;
    flex-wrap: wrap;
  }

  .btn-pacman {
    background: linear-gradient(90deg, #2ed46b, #15984a);
    border: none;
    color: #06140b;
    font-weight: 700;
    font-size: 18px;
    padding: 10px 24px;
    border-radius: 10px;
    text-transform: uppercase;
  }

  .btn-pacman:hover {
    background: linear-gradient(90deg, #40ec7d, #19aa53);
    color: #000;
  }

  .pacman-help {
    margin-top: 16px;
    color: rgba(255,255,255,0.55);
    font-size: 16px;
  }
</style>

<div class="pacman-page">
  <div class="pacman-card">

    <h1 class="pacman-title">PAC-<span>MAN</span></h1>

    <p class="pacman-subtitle">
      foge dos fantasmas, apanha os pontos e limpa o labirinto
    </p>

    <!-- informações do jogo -->
    <div class="pacman-info">
      <div class="pacman-badge">
        Pontos: <span id="score">0</span>
      </div>

      <div class="pacman-badge">
        Vidas: <span id="lives">3</span>
      </div>

      <div class="pacman-badge">
        Recorde: <span id="best">0</span>
      </div>

      <div class="pacman-badge">
        Estado: <span id="status">jogando</span>
      </div>
    </div>

    <!-- canvas menor: 42 colunas x 18 px = 756 / 31 linhas x 18 px = 558 -->
    <div class="canvas-wrap">
      <canvas id="game" width="756" height="558"></canvas>
    </div>

    <!-- botões do jogo -->
    <div class="pacman-controls">
      <button class="btn btn-pacman" onclick="reiniciarJogo()">
        <i class="bi bi-arrow-clockwise me-2"></i>
        reiniciar
      </button>

      <button class="btn btn-outline-light" onclick="pausarJogo()">
        <i class="bi bi-pause-circle me-2"></i>
        pausar
      </button>

      <a href="<?= BASE_URL ?>/biblioteca.php" class="btn btn-outline-success">
        voltar à biblioteca
      </a>
    </div>

    <div class="pacman-help">
      Usa as setas do teclado para mover o Pac-Man. Pressiona espaço para pausar.
    </div>

  </div>
</div>

<script>
/* ==================================================
   CONFIGURAÇÃO BÁSICA DO JOGO
================================================== */

/* pega o canvas, que é onde o jogo vai ser desenhado */
const canvas = document.getElementById("game");
const ctx = canvas.getContext("2d");

/* pega os textos da página para atualizar durante o jogo */
const scoreEl = document.getElementById("score");
const livesEl = document.getElementById("lives");
const bestEl = document.getElementById("best");
const statusEl = document.getElementById("status");

/*
  tamanho de cada quadradinho do mapa.
  se aumentar, o mapa fica maior.
  se diminuir, o mapa fica menor.
*/
const tile = 18;

/* quantidade de colunas e linhas do mapa */
const cols = 42;
const rows = 31;

/*
  valores usados dentro do mapa:

  0 = espaço vazio
  1 = parede azul normal
  2 = ponto pequeno
  3 = ponto grande

  4 = bloco azul da letra P
  5 = bloco amarelo da letra L
  6 = bloco verde da letra A
  7 = bloco branco da letra Y
*/
let map = [];

/* pontuação e vidas */
let score = 0;
let lives = 3;

/* recorde fica guardado no navegador */
let best = Number(localStorage.getItem("dzstorms_pacman_best") || 0);
bestEl.textContent = best;

/* controlo geral do jogo */
let paused = false;
let gameOver = false;
let animationId;

/* usado para controlar o tempo entre movimentos */
let lastTime = 0;
let moveTimer = 0;
let moveDelay = 135;

/* usado para animar algumas partes */
let tick = 0;

/* tempo em que os fantasmas ficam vulneráveis */
let powerTimer = 0;

/* dados do Pac-Man */
const pacman = {
  x: 21,
  y: 25,
  startX: 21,
  startY: 25,
  dirX: 0,
  dirY: 0,
  nextDirX: 0,
  nextDirY: 0,
  mouth: 0.18,
  mouthDirection: 1
};

/* lista dos fantasmas */
let ghosts = [];


/* ==================================================
   CRIAÇÃO DO MAPA
================================================== */

function criarMapa() {
  map = [];

  /*
    primeiro cria o mapa inteiro com pontos.
    depois colocamos as paredes e as letras por cima.
  */
  for (let y = 0; y < rows; y++) {
    let row = [];

    for (let x = 0; x < cols; x++) {

      /* borda do mapa */
      if (x === 0 || x === cols - 1 || y === 0 || y === rows - 1) {
        row.push(1);
      } else {
        row.push(2);
      }
    }

    map.push(row);
  }

  /* algumas paredes normais para formar o labirinto */
  criarParedeH(3, 3, 7);
  criarParedeH(13, 3, 7);
  criarParedeH(23, 3, 7);
  criarParedeH(33, 3, 6);

  criarParedeV(4, 7, 6);
  criarParedeV(12, 7, 6);
  criarParedeV(20, 7, 5);
  criarParedeV(30, 7, 6);
  criarParedeV(38, 7, 6);

  criarParedeH(7, 10, 7);
  criarParedeH(17, 10, 7);
  criarParedeH(28, 10, 6);

  criarParedeH(4, 19, 9);
  criarParedeH(17, 19, 8);
  criarParedeH(29, 19, 8);

  criarParedeV(8, 16, 5);
  criarParedeV(15, 16, 5);
  criarParedeV(21, 16, 6);
  criarParedeV(28, 16, 5);
  criarParedeV(35, 16, 5);

  criarParedeH(5, 27, 7);
  criarParedeH(16, 27, 8);
  criarParedeH(27, 27, 7);
  criarParedeH(36, 27, 4);

  criarParedeH(2, 23, 6);
  criarParedeH(12, 23, 7);
  criarParedeH(23, 23, 7);
  criarParedeH(34, 23, 6);

  /*
    limpa a parte central onde vai ficar a palavra PLAY.
    assim a palavra aparece como parte do mapa e não fica misturada com pontos.
  */
  for (let y = 6; y <= 15; y++) {
    for (let x = 8; x <= 33; x++) {
      map[y][x] = 0;
    }
  }

  /* cria o PLAY usando blocos do próprio mapa */
  criarPlayNoMapa();

  /* cria a casa onde os fantasmas começam */
  criarCasaDosFantasmas();

  /* pontos grandes, estilo Pac-Man */
  map[4][2] = 3;
  map[4][39] = 3;
  map[24][2] = 3;
  map[24][39] = 3;

  /* tira ponto da posição inicial do Pac-Man */
  map[pacman.startY][pacman.startX] = 0;

  /*
    limpa a área perto da casa dos fantasmas,
    para eles conseguirem sair e se mover.
  */
  limparArea(17, 16, 8, 4);

  /* recria a casa depois de limpar a área */
  criarCasaDosFantasmas();
}

/* cria parede horizontal normal */
function criarParedeH(x, y, tamanho) {
  for (let i = x; i < x + tamanho; i++) {
    if (i >= 0 && i < cols && y >= 0 && y < rows) {
      map[y][i] = 1;
    }
  }
}

/* cria parede vertical normal */
function criarParedeV(x, y, tamanho) {
  for (let i = y; i < y + tamanho; i++) {
    if (x >= 0 && x < cols && i >= 0 && i < rows) {
      map[i][x] = 1;
    }
  }
}

/* limpa uma parte do mapa */
function limparArea(x, y, w, h) {
  for (let linha = y; linha < y + h; linha++) {
    for (let coluna = x; coluna < x + w; coluna++) {
      if (coluna >= 0 && coluna < cols && linha >= 0 && linha < rows) {
        map[linha][coluna] = 0;
      }
    }
  }
}

/* cria um bloco especial, usado nas letras PLAY */
function criarBlocoEspecial(x, y, cor) {
  if (x >= 0 && x < cols && y >= 0 && y < rows) {
    map[y][x] = cor;
  }
}

/* cria uma linha horizontal com bloco especial */
function criarEspecialH(x, y, tamanho, cor) {
  for (let i = x; i < x + tamanho; i++) {
    criarBlocoEspecial(i, y, cor);
  }
}

/* cria uma linha vertical com bloco especial */
function criarEspecialV(x, y, tamanho, cor) {
  for (let i = y; i < y + tamanho; i++) {
    criarBlocoEspecial(x, i, cor);
  }
}

/*
  aqui é onde o PLAY é feito.
  não é texto normal.
  são blocos do mapa, igual parede.
*/
function criarPlayNoMapa() {
  /*
    P azul = 4
    L amarelo = 5
    A verde = 6
    Y branco = 7
  */

  /* letra P */
  criarEspecialV(10, 7, 7, 4);
  criarEspecialH(10, 7, 5, 4);
  criarEspecialH(10, 10, 5, 4);
  criarEspecialV(14, 7, 4, 4);

  /* letra L */
  criarEspecialV(17, 7, 7, 5);
  criarEspecialH(17, 13, 5, 5);

  /* letra A */
  criarEspecialV(24, 8, 6, 6);
  criarEspecialV(29, 8, 6, 6);
  criarEspecialH(25, 7, 4, 6);
  criarEspecialH(24, 10, 6, 6);

  /* letra Y */
  criarEspecialV(33, 7, 3, 7);
  criarEspecialV(38, 7, 3, 7);
  criarEspecialH(34, 10, 4, 7);
  criarEspecialV(36, 10, 4, 7);

  /* uma pecinha avulsa decorativa */
  criarEspecialH(31, 6, 2, 7);
}

/* casa dos fantasmas */
function criarCasaDosFantasmas() {
  /*
    esta casa fica abaixo do PLAY.
    os fantasmas começam aqui.
  */
  limparArea(17, 16, 8, 4);

  criarParedeH(17, 16, 8);
  criarParedeH(17, 20, 8);
  criarParedeV(17, 16, 5);
  criarParedeV(24, 16, 5);

  /* abertura da casa */
  map[16][20] = 0;
  map[16][21] = 0;
}


/* ==================================================
   FANTASMAS
================================================== */

function criarFantasmas() {
  ghosts = [
    {
      x: 20,
      y: 18,
      startX: 20,
      startY: 18,
      color: "#ff2a2a",
      dirX: 1,
      dirY: 0
    },
    {
      x: 21,
      y: 18,
      startX: 21,
      startY: 18,
      color: "#30d5ff",
      dirX: -1,
      dirY: 0
    },
    {
      x: 20,
      y: 19,
      startX: 20,
      startY: 19,
      color: "#ff92d0",
      dirX: 0,
      dirY: -1
    },
    {
      x: 21,
      y: 19,
      startX: 21,
      startY: 19,
      color: "#ffd53a",
      dirX: 0,
      dirY: -1
    }
  ];
}


/* ==================================================
   DESENHO DO JOGO
================================================== */

function desenharTudo() {
  ctx.clearRect(0, 0, canvas.width, canvas.height);

  desenharFundo();
  desenharMapa();
  desenharReady();
  desenharPacman();
  desenharFantasmas();

  if (paused) {
    desenharPausa();
  }

  if (gameOver) {
    desenharGameOver();
  }
}

/* fundo preto com um brilho leve */
function desenharFundo() {
  ctx.fillStyle = "#000";
  ctx.fillRect(0, 0, canvas.width, canvas.height);

  const glow = ctx.createRadialGradient(
    canvas.width / 2,
    canvas.height / 2,
    70,
    canvas.width / 2,
    canvas.height / 2,
    380
  );

  glow.addColorStop(0, "rgba(37,196,90,0.06)");
  glow.addColorStop(1, "rgba(37,196,90,0)");

  ctx.fillStyle = glow;
  ctx.fillRect(0, 0, canvas.width, canvas.height);
}

/* desenha mapa, paredes, PLAY e pontos */
function desenharMapa() {
  for (let y = 0; y < rows; y++) {
    for (let x = 0; x < cols; x++) {
      const val = map[y][x];
      const px = x * tile;
      const py = y * tile;

      /* parede azul normal */
      if (val === 1) {
        desenharBlocoMapa(px, py, "#214cff");
      }

      /* P azul */
      if (val === 4) {
        desenharBlocoMapa(px, py, "#3ea6ff");
      }

      /* L amarelo */
      if (val === 5) {
        desenharBlocoMapa(px, py, "#ffd53a");
      }

      /* A verde */
      if (val === 6) {
        desenharBlocoMapa(px, py, "#39ff88");
      }

      /* Y branco */
      if (val === 7) {
        desenharBlocoMapa(px, py, "#ffffff");
      }

      /* ponto pequeno */
      if (val === 2) {
        ctx.fillStyle = "#ffd1b3";
        ctx.beginPath();
        ctx.arc(px + tile / 2, py + tile / 2, 2.3, 0, Math.PI * 2);
        ctx.fill();
      }

      /* ponto grande */
      if (val === 3) {
        ctx.save();

        ctx.shadowColor = "#ffd1b3";
        ctx.shadowBlur = 10;

        ctx.fillStyle = "#ffd1b3";
        ctx.beginPath();
        ctx.arc(px + tile / 2, py + tile / 2, 6.4, 0, Math.PI * 2);
        ctx.fill();

        ctx.restore();
      }
    }
  }
}

/* desenha cada bloco do mapa com brilho */
function desenharBlocoMapa(px, py, cor) {
  ctx.save();

  ctx.shadowColor = cor;
  ctx.shadowBlur = 7;

  ctx.strokeStyle = cor;
  ctx.lineWidth = 3;
  ctx.strokeRect(px + 2, py + 2, tile - 4, tile - 4);

  ctx.shadowBlur = 0;

  ctx.strokeStyle = "rgba(255,255,255,0.16)";
  ctx.lineWidth = 1;
  ctx.strokeRect(px + 5, py + 5, tile - 10, tile - 10);

  ctx.restore();
}

/* mostra READY antes do Pac-Man começar a andar */
function desenharReady() {
  if (pacman.dirX !== 0 || pacman.dirY !== 0 || paused || gameOver) {
    return;
  }

  ctx.save();

  ctx.font = "bold 22px Orbitron";
  ctx.textAlign = "center";
  ctx.fillStyle = "#ffee32";
  ctx.shadowColor = "#ffee32";
  ctx.shadowBlur = 10;
  ctx.fillText("READY!", canvas.width / 2, 410);

  ctx.restore();
}

/* desenha o Pac-Man com boca animada */
function desenharPacman() {
  const px = pacman.x * tile + tile / 2;
  const py = pacman.y * tile + tile / 2;
  const r = tile / 2 - 2;

  let angle = 0;

  if (pacman.dirX === 1) angle = 0;
  if (pacman.dirX === -1) angle = Math.PI;
  if (pacman.dirY === -1) angle = -Math.PI / 2;
  if (pacman.dirY === 1) angle = Math.PI / 2;

  /* animação da boca abrindo e fechando */
  pacman.mouth += 0.035 * pacman.mouthDirection;

  if (pacman.mouth > 0.45 || pacman.mouth < 0.08) {
    pacman.mouthDirection *= -1;
  }

  ctx.save();

  ctx.shadowColor = "#ffd800";
  ctx.shadowBlur = 12;

  ctx.fillStyle = "#ffd800";
  ctx.beginPath();
  ctx.moveTo(px, py);
  ctx.arc(
    px,
    py,
    r,
    angle + pacman.mouth,
    angle + Math.PI * 2 - pacman.mouth
  );
  ctx.closePath();
  ctx.fill();

  ctx.restore();
}

/* desenha todos os fantasmas */
function desenharFantasmas() {
  ghosts.forEach(g => {
    desenharFantasma(g);
  });
}

/* desenha um fantasma */
function desenharFantasma(g) {
  const px = g.x * tile;
  const py = g.y * tile;

  /* se o poder estiver ativo, o fantasma fica azul */
  const color = powerTimer > 0 ? "#214cff" : g.color;

  ctx.save();

  ctx.shadowColor = color;
  ctx.shadowBlur = 12;

  /* corpo do fantasma */
  ctx.fillStyle = color;
  ctx.beginPath();
  ctx.arc(px + tile / 2, py + tile / 2 - 1, 8, Math.PI, 0);
  ctx.lineTo(px + tile - 1, py + tile - 2);
  ctx.lineTo(px + 14, py + 15);
  ctx.lineTo(px + 9, py + tile - 2);
  ctx.lineTo(px + 5, py + 15);
  ctx.lineTo(px + 1, py + tile - 2);
  ctx.closePath();
  ctx.fill();

  ctx.shadowBlur = 0;

  /* olhos */
  ctx.fillStyle = "#fff";
  ctx.beginPath();
  ctx.arc(px + 6, py + 7, 2.8, 0, Math.PI * 2);
  ctx.arc(px + 12, py + 7, 2.8, 0, Math.PI * 2);
  ctx.fill();

  /* pupilas olhando para a direção em que o fantasma anda */
  ctx.fillStyle = "#214cff";
  ctx.beginPath();
  ctx.arc(px + 6 + g.dirX, py + 7 + g.dirY, 1.2, 0, Math.PI * 2);
  ctx.arc(px + 12 + g.dirX, py + 7 + g.dirY, 1.2, 0, Math.PI * 2);
  ctx.fill();

  ctx.restore();
}

/* tela de pausa */
function desenharPausa() {
  ctx.fillStyle = "rgba(0,0,0,0.65)";
  ctx.fillRect(0, 0, canvas.width, canvas.height);

  ctx.fillStyle = "#25c45a";
  ctx.font = "bold 38px Orbitron";
  ctx.textAlign = "center";
  ctx.fillText("PAUSADO", canvas.width / 2, canvas.height / 2);
}

/* tela de game over */
function desenharGameOver() {
  ctx.fillStyle = "rgba(0,0,0,0.75)";
  ctx.fillRect(0, 0, canvas.width, canvas.height);

  ctx.fillStyle = "#ff4b5c";
  ctx.font = "bold 38px Orbitron";
  ctx.textAlign = "center";
  ctx.fillText("GAME OVER", canvas.width / 2, canvas.height / 2 - 20);

  ctx.fillStyle = "#ffffff";
  ctx.font = "21px Rajdhani";
  ctx.fillText("pontuação: " + score, canvas.width / 2, canvas.height / 2 + 25);
}

/* tela de vitória */
function desenharVitoria() {
  ctx.fillStyle = "rgba(0,0,0,0.75)";
  ctx.fillRect(0, 0, canvas.width, canvas.height);

  ctx.fillStyle = "#ffee32";
  ctx.font = "bold 38px Orbitron";
  ctx.textAlign = "center";
  ctx.fillText("VENCERSTE!", canvas.width / 2, canvas.height / 2 - 20);

  ctx.fillStyle = "#ffffff";
  ctx.font = "21px Rajdhani";
  ctx.fillText("pontuação: " + score, canvas.width / 2, canvas.height / 2 + 25);
}


/* ==================================================
   MOVIMENTO E REGRAS DO JOGO
================================================== */

/* diz quais valores são paredes */
function valorParede(valor) {
  return valor === 1 || valor === 4 || valor === 5 || valor === 6 || valor === 7;
}

/* verifica se uma posição é parede */
function parede(x, y) {
  if (x < 0 || x >= cols || y < 0 || y >= rows) {
    return true;
  }

  return valorParede(map[y][x]);
}

/* move o Pac-Man */
function moverPacman() {
  /*
    primeiro tenta mudar para a direção que o jogador pediu.
    se não tiver parede, muda.
  */
  const testeX = pacman.x + pacman.nextDirX;
  const testeY = pacman.y + pacman.nextDirY;

  if (!parede(testeX, testeY)) {
    pacman.dirX = pacman.nextDirX;
    pacman.dirY = pacman.nextDirY;
  }

  /* depois anda na direção atual */
  const newX = pacman.x + pacman.dirX;
  const newY = pacman.y + pacman.dirY;

  if (!parede(newX, newY)) {
    pacman.x = newX;
    pacman.y = newY;
  }

  comerPonto();
}

/* come os pontos do mapa */
function comerPonto() {
  const valor = map[pacman.y][pacman.x];

  /* ponto pequeno */
  if (valor === 2) {
    map[pacman.y][pacman.x] = 0;
    score += 10;
  }

  /* ponto grande */
  if (valor === 3) {
    map[pacman.y][pacman.x] = 0;
    score += 50;

    /* ativa poder contra os fantasmas */
    powerTimer = 90;
    statusEl.textContent = "poder";
  }

  atualizarPontuacao();

  /* verifica se acabou os pontos */
  if (verificarVitoria()) {
    gameOver = true;
    cancelAnimationFrame(animationId);
    desenharTudo();
    desenharVitoria();
  }
}

/* move os fantasmas */
function moverFantasmas() {
  ghosts.forEach(g => {
    const opcoes = [];

    const dirs = [
      { x: 1, y: 0 },
      { x: -1, y: 0 },
      { x: 0, y: 1 },
      { x: 0, y: -1 }
    ];

    /*
      vê para onde o fantasma pode andar.
      ele não pode passar por paredes.
    */
    dirs.forEach(d => {
      const nx = g.x + d.x;
      const ny = g.y + d.y;

      if (!parede(nx, ny)) {
        opcoes.push(d);
      }
    });

    if (opcoes.length > 0) {
      /*
        se o poder estiver ativo, o fantasma tenta fugir.
        se não estiver, ele tenta chegar mais perto do Pac-Man.
      */
      opcoes.sort((a, b) => {
        const distA = Math.abs((g.x + a.x) - pacman.x) + Math.abs((g.y + a.y) - pacman.y);
        const distB = Math.abs((g.x + b.x) - pacman.x) + Math.abs((g.y + b.y) - pacman.y);

        if (powerTimer > 0) {
          return distB - distA;
        }

        return distA - distB;
      });

      /*
        75% das vezes ele escolhe o melhor caminho.
        o resto é aleatório, para não ficar robótico demais.
      */
      const escolha = Math.random() < 0.75
        ? opcoes[0]
        : opcoes[Math.floor(Math.random() * opcoes.length)];

      g.dirX = escolha.x;
      g.dirY = escolha.y;
    }

    g.x += g.dirX;
    g.y += g.dirY;
  });
}

/* verifica colisão entre Pac-Man e fantasmas */
function verificarColisao() {
  ghosts.forEach(g => {
    if (g.x === pacman.x && g.y === pacman.y) {

      /* com poder, Pac-Man come o fantasma */
      if (powerTimer > 0) {
        score += 200;
        atualizarPontuacao();

        g.x = g.startX;
        g.y = g.startY;
      } else {
        perderVida();
      }
    }
  });
}

/* perde uma vida */
function perderVida() {
  lives--;
  livesEl.textContent = lives;

  if (lives <= 0) {
    gameOver = true;
    statusEl.textContent = "perdeste";
    return;
  }

  /* volta Pac-Man para o começo */
  pacman.x = pacman.startX;
  pacman.y = pacman.startY;
  pacman.dirX = 0;
  pacman.dirY = 0;
  pacman.nextDirX = 0;
  pacman.nextDirY = 0;

  /* tira poder se tiver */
  powerTimer = 0;
  statusEl.textContent = "jogando";

  /* volta fantasmas para a casa */
  criarFantasmas();
}

/* atualiza pontos e recorde */
function atualizarPontuacao() {
  scoreEl.textContent = score;

  if (score > best) {
    best = score;
    localStorage.setItem("dzstorms_pacman_best", best);
    bestEl.textContent = best;
  }
}

/* vê se ainda existem pontos no mapa */
function verificarVitoria() {
  for (let y = 0; y < rows; y++) {
    for (let x = 0; x < cols; x++) {
      if (map[y][x] === 2 || map[y][x] === 3) {
        return false;
      }
    }
  }

  return true;
}

/* pausa e continua o jogo */
function pausarJogo() {
  if (gameOver) return;

  paused = !paused;
  statusEl.textContent = paused ? "pausado" : "jogando";

  desenharTudo();
}

/* reinicia tudo */
function reiniciarJogo() {
  cancelAnimationFrame(animationId);

  score = 0;
  lives = 3;
  paused = false;
  gameOver = false;
  powerTimer = 0;

  pacman.x = pacman.startX;
  pacman.y = pacman.startY;
  pacman.dirX = 0;
  pacman.dirY = 0;
  pacman.nextDirX = 0;
  pacman.nextDirY = 0;

  scoreEl.textContent = score;
  livesEl.textContent = lives;
  statusEl.textContent = "jogando";

  criarMapa();
  criarFantasmas();

  lastTime = 0;
  moveTimer = 0;

  loop();
}


/* ==================================================
   LOOP PRINCIPAL
================================================== */

function loop(timestamp = 0) {
  if (gameOver) {
    desenharTudo();
    return;
  }

  const delta = timestamp - lastTime;
  lastTime = timestamp;
  moveTimer += delta;
  tick++;

  if (!paused) {
    /* diminui o tempo do poder */
    if (powerTimer > 0) {
      powerTimer--;

      if (powerTimer === 0) {
        statusEl.textContent = "jogando";
      }
    }

    /*
      o jogo só move depois de um intervalo.
      isso deixa o movimento em quadradinhos, estilo Pac-Man.
    */
    if (moveTimer >= moveDelay) {
      moverPacman();
      moverFantasmas();
      verificarColisao();

      moveTimer = 0;
    }
  }

  desenharTudo();

  animationId = requestAnimationFrame(loop);
}


/* ==================================================
   CONTROLOS DO TECLADO
================================================== */

document.addEventListener("keydown", function(e) {
  const keys = ["ArrowUp", "ArrowDown", "ArrowLeft", "ArrowRight", " "];

  /*
    impede a página de subir/descer quando usa as setas.
    isso era importante porque as setas são usadas no jogo.
  */
  if (keys.includes(e.key)) {
    e.preventDefault();
  }

  if (e.key === "ArrowUp") {
    pacman.nextDirX = 0;
    pacman.nextDirY = -1;
  }

  if (e.key === "ArrowDown") {
    pacman.nextDirX = 0;
    pacman.nextDirY = 1;
  }

  if (e.key === "ArrowLeft") {
    pacman.nextDirX = -1;
    pacman.nextDirY = 0;
  }

  if (e.key === "ArrowRight") {
    pacman.nextDirX = 1;
    pacman.nextDirY = 0;
  }

  if (e.key === " ") {
    pausarJogo();
  }
});


/* ==================================================
   INICIA O JOGO
================================================== */

criarMapa();
criarFantasmas();
desenharTudo();
loop();
</script>

<?php
/*
  Fecha a div que foi aberta no header.php.
  Como é página de jogo, usamos scripts.php para não mostrar o rodapé.
*/
echo '</div>';

if (file_exists(__DIR__ . '/scripts.php')) {
  require_once __DIR__ . '/scripts.php';
} else {
  echo '
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
  </html>
  ';
}
?>