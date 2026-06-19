<?php require_once __DIR__ . '/header.php'; ?>

<style>
  .breaker-page {
    min-height: calc(100vh - 160px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 45px 15px;
  }

  .breaker-card {
    width: 100%;
    max-width: 960px;
    background: rgba(10, 12, 18, 0.94);
    border: 1px solid rgba(37,196,90,0.45);
    border-radius: 18px;
    padding: 28px;
    box-shadow: 0 0 30px rgba(37,196,90,0.18);
    text-align: center;
  }

  .breaker-title {
    font-family: 'Orbitron', sans-serif;
    font-size: 42px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 3px;
    margin-bottom: 8px;
  }

  .breaker-title span {
    color: #25c45a;
  }

  .breaker-subtitle {
    color: rgba(255,255,255,0.65);
    font-size: 18px;
    margin-bottom: 20px;
  }

  .breaker-info {
    display: flex;
    justify-content: center;
    gap: 14px;
    flex-wrap: wrap;
    margin-bottom: 18px;
  }

  .breaker-badge {
    background: rgba(37,196,90,0.12);
    border: 1px solid rgba(37,196,90,0.45);
    color: #fff;
    border-radius: 10px;
    padding: 10px 16px;
    font-size: 18px;
    font-weight: 600;
  }

  .breaker-badge span {
    color: #25c45a;
    font-weight: 700;
  }

  .canvas-wrap {
    display: inline-block;
    padding: 12px;
    border-radius: 18px;
    background: rgba(0,0,0,0.55);
    border: 1px solid rgba(255,255,255,0.08);
    box-shadow: inset 0 0 18px rgba(0,0,0,0.7);
  }

  canvas {
    display: block;
    max-width: 100%;
    height: auto;
    border-radius: 12px;
    background: #060912;
    border: 3px solid #25c45a;
    box-shadow: 0 0 22px rgba(37,196,90,0.25);
  }

  .breaker-controls {
    margin-top: 18px;
    display: flex;
    justify-content: center;
    gap: 12px;
    flex-wrap: wrap;
  }

  .btn-breaker {
    background: linear-gradient(90deg, #2ed46b, #15984a);
    border: none;
    color: #06140b;
    font-weight: 700;
    font-size: 18px;
    padding: 10px 24px;
    border-radius: 10px;
    text-transform: uppercase;
  }

  .btn-breaker:hover {
    background: linear-gradient(90deg, #40ec7d, #19aa53);
    color: #000;
  }

  .breaker-help {
    margin-top: 16px;
    color: rgba(255,255,255,0.55);
    font-size: 16px;
  }

  @media (max-width: 700px) {
    .breaker-title {
      font-size: 32px;
    }
  }
</style>

<div class="breaker-page">
  <div class="breaker-card">

    <h1 class="breaker-title">Block <span>Breaker</span></h1>

    <p class="breaker-subtitle">
      destrói os blocos, apanha poderes e tenta chegar ao nível mais alto
    </p>

    <div class="breaker-info">
      <div class="breaker-badge">
        Pontos: <span id="score">0</span>
      </div>

      <div class="breaker-badge">
        Vidas: <span id="lives">3</span>
      </div>

      <div class="breaker-badge">
        Nível: <span id="level">1</span>
      </div>

      <div class="breaker-badge">
        Recorde: <span id="best">0</span>
      </div>

      <div class="breaker-badge">
        Estado: <span id="status">pronto</span>
      </div>
    </div>

    <div class="canvas-wrap">
      <canvas id="game" width="700" height="460"></canvas>
    </div>

    <div class="breaker-controls">
      <button class="btn btn-breaker" onclick="restartGame()">
        <i class="bi bi-arrow-clockwise me-2"></i>
        reiniciar
      </button>

      <button class="btn btn-outline-light" onclick="togglePause()">
        <i class="bi bi-pause-circle me-2"></i>
        pausar
      </button>

      <a href="<?= BASE_URL ?>/biblioteca.php" class="btn btn-outline-success">
        voltar à biblioteca
      </a>
    </div>

    <div class="breaker-help">
      Usa as setas ou o rato para mover a barra. Pressiona espaço para lançar ou pausar.
    </div>

  </div>
</div>

<script>
/* pega o canvas */
const canvas = document.getElementById("game");
const ctx = canvas.getContext("2d");

/* elementos da interface */
const scoreEl = document.getElementById("score");
const livesEl = document.getElementById("lives");
const levelEl = document.getElementById("level");
const bestEl = document.getElementById("best");
const statusEl = document.getElementById("status");

/* recorde guardado no navegador */
let bestScore = Number(localStorage.getItem("dzstorms_block_best") || 0);
bestEl.textContent = bestScore;

/* variáveis principais */
let score = 0;
let lives = 3;
let level = 1;
let paused = false;
let gameOver = false;
let launched = false;
let animationId;

/* teclado */
let leftPressed = false;
let rightPressed = false;

/* barra */
let paddle = {
  width: 125,
  height: 15,
  x: canvas.width / 2 - 62.5,
  y: canvas.height - 38,
  speed: 8,
  normalWidth: 125
};

/* bola */
let ball = {
  x: canvas.width / 2,
  y: paddle.y - 14,
  radius: 9,
  dx: 4,
  dy: -4,
  speed: 4.8
};

/* blocos */
let bricks = [];
let brickRows = 5;
let brickCols = 9;
let brickWidth = 62;
let brickHeight = 22;
let brickPadding = 10;
let brickOffsetTop = 70;
let brickOffsetLeft = 43;

/* efeitos */
let particles = [];
let powerUps = [];
let wideTimer = null;
let slowTimer = null;

/* cores dos blocos */
const brickColors = [
  "#25c45a",
  "#2ed46b",
  "#00b4d8",
  "#ffbe0b",
  "#ff4b5c",
  "#9b5de5"
];

/* cria os blocos */
function createBricks() {
  bricks = [];

  brickRows = Math.min(5 + Math.floor(level / 2), 7);

  for (let r = 0; r < brickRows; r++) {
    for (let c = 0; c < brickCols; c++) {
      const strength = level >= 3 && r < 2 ? 2 : 1;

      bricks.push({
        x: brickOffsetLeft + c * (brickWidth + brickPadding),
        y: brickOffsetTop + r * (brickHeight + brickPadding),
        width: brickWidth,
        height: brickHeight,
        active: true,
        strength: strength,
        maxStrength: strength,
        color: brickColors[(r + c + level) % brickColors.length]
      });
    }
  }
}

/* inicia o jogo */
function startGame() {
  score = 0;
  lives = 3;
  level = 1;
  paused = false;
  gameOver = false;
  launched = false;

  paddle.width = paddle.normalWidth;
  resetBall();
  createBricks();

  updateInterface();
  statusEl.textContent = "pronto";

  cancelAnimationFrame(animationId);
  gameLoop();
}

/* reinicia tudo */
function restartGame() {
  clearPowerTimers();
  particles = [];
  powerUps = [];
  startGame();
}

/* atualiza os textos */
function updateInterface() {
  scoreEl.textContent = score;
  livesEl.textContent = lives;
  levelEl.textContent = level;
  bestEl.textContent = bestScore;
}

/* limpa poderes ativos */
function clearPowerTimers() {
  if (wideTimer) clearTimeout(wideTimer);
  if (slowTimer) clearTimeout(slowTimer);
  wideTimer = null;
  slowTimer = null;
}

/* coloca a bola em cima da barra */
function resetBall() {
  paddle.x = canvas.width / 2 - paddle.width / 2;

  ball.x = canvas.width / 2;
  ball.y = paddle.y - 14;
  ball.radius = 9;

  const direction = Math.random() > 0.5 ? 1 : -1;

  ball.speed = 4.8 + level * 0.35;
  ball.dx = ball.speed * direction;
  ball.dy = -ball.speed;

  launched = false;
}

/* pausa ou continua */
function togglePause() {
  if (gameOver) return;

  if (!launched) {
    launchBall();
    return;
  }

  paused = !paused;
  statusEl.textContent = paused ? "pausado" : "jogando";
}

/* lança a bola */
function launchBall() {
  if (gameOver) return;

  launched = true;
  paused = false;
  statusEl.textContent = "jogando";
}

/* desenha o fundo */
function drawBackground() {
  ctx.fillStyle = "#060912";
  ctx.fillRect(0, 0, canvas.width, canvas.height);

  /* grelha */
  ctx.strokeStyle = "rgba(255,255,255,0.035)";
  ctx.lineWidth = 1;

  for (let x = 0; x <= canvas.width; x += 35) {
    ctx.beginPath();
    ctx.moveTo(x, 0);
    ctx.lineTo(x, canvas.height);
    ctx.stroke();
  }

  for (let y = 0; y <= canvas.height; y += 35) {
    ctx.beginPath();
    ctx.moveTo(0, y);
    ctx.lineTo(canvas.width, y);
    ctx.stroke();
  }

  /* brilho verde */
  const glow = ctx.createRadialGradient(
    canvas.width / 2,
    canvas.height / 2,
    80,
    canvas.width / 2,
    canvas.height / 2,
    430
  );

  glow.addColorStop(0, "rgba(37,196,90,0.09)");
  glow.addColorStop(1, "rgba(37,196,90,0)");

  ctx.fillStyle = glow;
  ctx.fillRect(0, 0, canvas.width, canvas.height);
}

/* desenha os blocos */
function drawBricks() {
  for (let brick of bricks) {
    if (!brick.active) continue;

    const alpha = brick.strength / brick.maxStrength;

    ctx.save();

    ctx.shadowColor = brick.color;
    ctx.shadowBlur = 12;

    const gradient = ctx.createLinearGradient(
      brick.x,
      brick.y,
      brick.x,
      brick.y + brick.height
    );

    gradient.addColorStop(0, brick.color);
    gradient.addColorStop(1, "rgba(255,255,255,0.12)");

    ctx.fillStyle = gradient;
    roundedRect(brick.x, brick.y, brick.width, brick.height, 7);
    ctx.fill();

    ctx.shadowBlur = 0;
    ctx.strokeStyle = `rgba(255,255,255,${0.25 * alpha})`;
    ctx.lineWidth = 1.5;
    roundedRect(brick.x, brick.y, brick.width, brick.height, 7);
    ctx.stroke();

    /* marca de bloco resistente */
    if (brick.strength > 1) {
      ctx.fillStyle = "rgba(0,0,0,0.35)";
      ctx.font = "700 14px Rajdhani";
      ctx.textAlign = "center";
      ctx.fillText("x2", brick.x + brick.width / 2, brick.y + 15);
    }

    ctx.restore();
  }
}

/* desenha retângulo arredondado */
function roundedRect(x, y, w, h, r) {
  ctx.beginPath();
  ctx.roundRect(x, y, w, h, r);
}

/* desenha a barra */
function drawPaddle() {
  ctx.save();

  ctx.shadowColor = "#25c45a";
  ctx.shadowBlur = 18;

  const gradient = ctx.createLinearGradient(
    paddle.x,
    paddle.y,
    paddle.x + paddle.width,
    paddle.y
  );

  gradient.addColorStop(0, "#15984a");
  gradient.addColorStop(0.5, "#40ec7d");
  gradient.addColorStop(1, "#15984a");

  ctx.fillStyle = gradient;
  roundedRect(paddle.x, paddle.y, paddle.width, paddle.height, 10);
  ctx.fill();

  ctx.shadowBlur = 0;
  ctx.fillStyle = "rgba(255,255,255,0.35)";
  roundedRect(paddle.x + 12, paddle.y + 3, paddle.width - 24, 3, 4);
  ctx.fill();

  ctx.restore();
}

/* desenha a bola */
function drawBall() {
  ctx.save();

  ctx.shadowColor = "#ffffff";
  ctx.shadowBlur = 18;

  const gradient = ctx.createRadialGradient(
    ball.x - 3,
    ball.y - 4,
    2,
    ball.x,
    ball.y,
    ball.radius + 5
  );

  gradient.addColorStop(0, "#ffffff");
  gradient.addColorStop(0.45, "#a7f3c3");
  gradient.addColorStop(1, "#25c45a");

  ctx.fillStyle = gradient;
  ctx.beginPath();
  ctx.arc(ball.x, ball.y, ball.radius, 0, Math.PI * 2);
  ctx.fill();

  ctx.restore();
}

/* cria partículas ao destruir bloco */
function createParticles(x, y, color) {
  for (let i = 0; i < 14; i++) {
    particles.push({
      x: x,
      y: y,
      dx: (Math.random() - 0.5) * 5,
      dy: (Math.random() - 0.5) * 5,
      life: 35,
      size: Math.random() * 3 + 2,
      color: color
    });
  }
}

/* desenha partículas */
function drawParticles() {
  for (let p of particles) {
    ctx.globalAlpha = p.life / 35;
    ctx.fillStyle = p.color;
    ctx.beginPath();
    ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
    ctx.fill();
    ctx.globalAlpha = 1;
  }
}

/* atualiza partículas */
function updateParticles() {
  particles = particles.filter(p => p.life > 0);

  for (let p of particles) {
    p.x += p.dx;
    p.y += p.dy;
    p.life--;
  }
}

/* cria power-up aleatório */
function maybeCreatePowerUp(x, y) {
  if (Math.random() > 0.18) return;

  const types = ["wide", "slow", "life"];
  const type = types[Math.floor(Math.random() * types.length)];

  powerUps.push({
    x: x,
    y: y,
    width: 28,
    height: 28,
    dy: 2,
    type: type
  });
}

/* desenha power-ups */
function drawPowerUps() {
  for (let p of powerUps) {
    ctx.save();

    if (p.type === "wide") ctx.fillStyle = "#25c45a";
    if (p.type === "slow") ctx.fillStyle = "#00b4d8";
    if (p.type === "life") ctx.fillStyle = "#ff4b5c";

    ctx.shadowColor = ctx.fillStyle;
    ctx.shadowBlur = 14;

    roundedRect(p.x, p.y, p.width, p.height, 8);
    ctx.fill();

    ctx.shadowBlur = 0;
    ctx.fillStyle = "#06140b";
    ctx.font = "900 18px Rajdhani";
    ctx.textAlign = "center";

    let symbol = "+";
    if (p.type === "slow") symbol = "S";
    if (p.type === "life") symbol = "♥";

    ctx.fillText(symbol, p.x + p.width / 2, p.y + 20);

    ctx.restore();
  }
}

/* atualiza power-ups */
function updatePowerUps() {
  for (let p of powerUps) {
    p.y += p.dy;

    if (
      p.y + p.height >= paddle.y &&
      p.y <= paddle.y + paddle.height &&
      p.x + p.width >= paddle.x &&
      p.x <= paddle.x + paddle.width
    ) {
      activatePowerUp(p.type);
      p.caught = true;
    }
  }

  powerUps = powerUps.filter(p => !p.caught && p.y < canvas.height + 40);
}

/* ativa power-up */
function activatePowerUp(type) {
  if (type === "wide") {
    paddle.width = 170;

    if (wideTimer) clearTimeout(wideTimer);

    wideTimer = setTimeout(() => {
      paddle.width = paddle.normalWidth;
    }, 7000);
  }

  if (type === "slow") {
    ball.dx *= 0.75;
    ball.dy *= 0.75;

    if (slowTimer) clearTimeout(slowTimer);

    slowTimer = setTimeout(() => {
      const sx = ball.dx > 0 ? 1 : -1;
      const sy = ball.dy > 0 ? 1 : -1;

      ball.dx = Math.abs(ball.dx) * 1.25 * sx;
      ball.dy = Math.abs(ball.dy) * 1.25 * sy;
    }, 5000);
  }

  if (type === "life") {
    lives++;
    updateInterface();
  }
}

/* move a barra */
function movePaddle() {
  if (leftPressed) {
    paddle.x -= paddle.speed;
  }

  if (rightPressed) {
    paddle.x += paddle.speed;
  }

  if (paddle.x < 0) paddle.x = 0;

  if (paddle.x + paddle.width > canvas.width) {
    paddle.x = canvas.width - paddle.width;
  }

  if (!launched) {
    ball.x = paddle.x + paddle.width / 2;
    ball.y = paddle.y - 14;
  }
}

/* move a bola */
function moveBall() {
  if (!launched) return;

  ball.x += ball.dx;
  ball.y += ball.dy;

  /* parede esquerda e direita */
  if (ball.x - ball.radius < 0 || ball.x + ball.radius > canvas.width) {
    ball.dx *= -1;
  }

  /* parede superior */
  if (ball.y - ball.radius < 0) {
    ball.dy *= -1;
  }

  /* perdeu a bola */
  if (ball.y - ball.radius > canvas.height) {
    lives--;
    updateInterface();

    if (lives <= 0) {
      endGame();
    } else {
      resetBall();
      statusEl.textContent = "pronto";
    }
  }

  /* colisão com a barra */
  if (
    ball.y + ball.radius >= paddle.y &&
    ball.y - ball.radius <= paddle.y + paddle.height &&
    ball.x >= paddle.x &&
    ball.x <= paddle.x + paddle.width &&
    ball.dy > 0
  ) {
    const hitPoint = (ball.x - (paddle.x + paddle.width / 2)) / (paddle.width / 2);

    ball.dx = hitPoint * (ball.speed + level * 0.35);
    ball.dy = -Math.abs(ball.dy);

    ball.y = paddle.y - ball.radius;
  }
}

/* colisão da bola com blocos */
function checkBrickCollision() {
  for (let brick of bricks) {
    if (!brick.active) continue;

    if (
      ball.x + ball.radius > brick.x &&
      ball.x - ball.radius < brick.x + brick.width &&
      ball.y + ball.radius > brick.y &&
      ball.y - ball.radius < brick.y + brick.height
    ) {
      ball.dy *= -1;

      brick.strength--;

      if (brick.strength <= 0) {
        brick.active = false;
        score += 10 * level;

        createParticles(
          brick.x + brick.width / 2,
          brick.y + brick.height / 2,
          brick.color
        );

        maybeCreatePowerUp(
          brick.x + brick.width / 2 - 14,
          brick.y + brick.height / 2 - 14
        );

      } else {
        score += 5;
      }

      if (score > bestScore) {
        bestScore = score;
        localStorage.setItem("dzstorms_block_best", bestScore);
      }

      updateInterface();

      if (bricks.every(b => !b.active)) {
        nextLevel();
      }

      break;
    }
  }
}

/* passa para o próximo nível */
function nextLevel() {
  level++;
  statusEl.textContent = "nível " + level;

  particles = [];
  powerUps = [];

  paddle.width = paddle.normalWidth;

  createBricks();
  resetBall();
  updateInterface();
}

/* termina o jogo */
function endGame() {
  gameOver = true;
  launched = false;
  statusEl.textContent = "perdeste";
}

/* desenha mensagens no centro */
function drawCenterMessage() {
  if (!launched && !gameOver) {
    ctx.fillStyle = "rgba(0,0,0,0.45)";
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    ctx.fillStyle = "#25c45a";
    ctx.font = "700 30px Orbitron";
    ctx.textAlign = "center";
    ctx.fillText("BLOCK BREAKER", canvas.width / 2, canvas.height / 2 - 20);

    ctx.fillStyle = "#ffffff";
    ctx.font = "500 21px Rajdhani";
    ctx.fillText("pressiona espaço para começar", canvas.width / 2, canvas.height / 2 + 22);
  }

  if (paused) {
    ctx.fillStyle = "rgba(0,0,0,0.55)";
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    ctx.fillStyle = "#25c45a";
    ctx.font = "700 32px Orbitron";
    ctx.textAlign = "center";
    ctx.fillText("PAUSADO", canvas.width / 2, canvas.height / 2);
  }

  if (gameOver) {
    ctx.fillStyle = "rgba(0,0,0,0.72)";
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    ctx.fillStyle = "#ff4b5c";
    ctx.font = "700 34px Orbitron";
    ctx.textAlign = "center";
    ctx.fillText("GAME OVER", canvas.width / 2, canvas.height / 2 - 20);

    ctx.fillStyle = "#ffffff";
    ctx.font = "500 21px Rajdhani";
    ctx.fillText("clica em reiniciar para jogar novamente", canvas.width / 2, canvas.height / 2 + 25);
  }
}

/* loop principal */
function gameLoop() {
  animationId = requestAnimationFrame(gameLoop);

  drawBackground();
  drawBricks();
  drawPaddle();
  drawBall();
  drawParticles();
  drawPowerUps();

  if (!paused && !gameOver) {
    movePaddle();
    moveBall();
    checkBrickCollision();
    updateParticles();
    updatePowerUps();
  }

  drawCenterMessage();
}

/* teclado */
document.addEventListener("keydown", function(event) {
  const keys = ["ArrowLeft", "ArrowRight", "a", "A", "d", "D", " "];

  if (keys.includes(event.key)) {
    event.preventDefault();
  }

  if (event.key === "ArrowLeft" || event.key === "a" || event.key === "A") {
    leftPressed = true;
  }

  if (event.key === "ArrowRight" || event.key === "d" || event.key === "D") {
    rightPressed = true;
  }

  if (event.key === " ") {
    if (!launched) {
      launchBall();
    } else {
      togglePause();
    }
  }
});

document.addEventListener("keyup", function(event) {
  if (event.key === "ArrowLeft" || event.key === "a" || event.key === "A") {
    leftPressed = false;
  }

  if (event.key === "ArrowRight" || event.key === "d" || event.key === "D") {
    rightPressed = false;
  }
});

/* controlo pelo rato */
canvas.addEventListener("mousemove", function(event) {
  const rect = canvas.getBoundingClientRect();
  const scaleX = canvas.width / rect.width;
  const mouseX = (event.clientX - rect.left) * scaleX;

  paddle.x = mouseX - paddle.width / 2;

  if (paddle.x < 0) paddle.x = 0;

  if (paddle.x + paddle.width > canvas.width) {
    paddle.x = canvas.width - paddle.width;
  }

  if (!launched) {
    ball.x = paddle.x + paddle.width / 2;
    ball.y = paddle.y - 14;
  }
});

/* clique no canvas também começa */
canvas.addEventListener("click", function() {
  if (!launched && !gameOver) {
    launchBall();
  }
});

/* inicia o jogo */
startGame();
</script>

<?php
/*
  Fecha a div aberta no header.php.
  Como é uma página de jogo, usa scripts.php para não mostrar o rodapé.
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