<?php require_once __DIR__ . '/header.php'; ?>

<style>
  .snake-page {
    min-height: calc(100vh - 160px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 45px 15px;
  }

  .snake-card {
    width: 100%;
    max-width: 760px;
    background: rgba(10, 12, 18, 0.94);
    border: 1px solid rgba(37,196,90,0.45);
    border-radius: 18px;
    padding: 28px;
    box-shadow: 0 0 30px rgba(37,196,90,0.18);
    text-align: center;
  }

  .snake-title {
    font-family: 'Orbitron', sans-serif;
    font-size: 42px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 3px;
    margin-bottom: 8px;
  }

  .snake-title span {
    color: #25c45a;
  }

  .snake-subtitle {
    color: rgba(255,255,255,0.65);
    font-size: 18px;
    margin-bottom: 20px;
  }

  .snake-info {
    display: flex;
    justify-content: center;
    gap: 18px;
    flex-wrap: wrap;
    margin-bottom: 18px;
  }

  .snake-badge {
    background: rgba(37,196,90,0.12);
    border: 1px solid rgba(37,196,90,0.45);
    color: #fff;
    border-radius: 10px;
    padding: 10px 18px;
    font-size: 18px;
    font-weight: 600;
  }

  .snake-badge span {
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
    border-radius: 12px;
    background: #071108;
    border: 3px solid #25c45a;
    box-shadow: 0 0 20px rgba(37,196,90,0.25);
  }

  .snake-controls {
    margin-top: 18px;
    display: flex;
    justify-content: center;
    gap: 12px;
    flex-wrap: wrap;
  }

  .btn-snake {
    background: linear-gradient(90deg, #2ed46b, #15984a);
    border: none;
    color: #06140b;
    font-weight: 700;
    font-size: 18px;
    padding: 10px 24px;
    border-radius: 10px;
    text-transform: uppercase;
  }

  .btn-snake:hover {
    background: linear-gradient(90deg, #40ec7d, #19aa53);
    color: #000;
  }

  .snake-help {
    margin-top: 16px;
    color: rgba(255,255,255,0.55);
    font-size: 16px;
  }

  @media (max-width: 600px) {
    .snake-title {
      font-size: 32px;
    }

    canvas {
      width: 320px;
      height: 320px;
    }
  }
</style>

<div class="snake-page">
  <div class="snake-card">

    <h1 class="snake-title">Sna<span>ke</span></h1>

    <p class="snake-subtitle">
      apanha as maçãs, aumenta a pontuação e tenta bater o teu recorde
    </p>

    <div class="snake-info">
      <div class="snake-badge">
        Pontos: <span id="score">0</span>
      </div>

      <div class="snake-badge">
        Recorde: <span id="best">0</span>
      </div>

      <div class="snake-badge">
        Estado: <span id="status">jogando</span>
      </div>
    </div>

    <div class="canvas-wrap">
      <canvas id="game" width="440" height="440"></canvas>
    </div>

    <div class="snake-controls">
      <button class="btn btn-snake" onclick="restartGame()">
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

    <div class="snake-help">
      Usa as setas do teclado para jogar. Pressiona espaço para pausar.
    </div>

  </div>
</div>

<script>
/* pega o canvas do jogo */
const canvas = document.getElementById("game");
const ctx = canvas.getContext("2d");

/* tamanho de cada posição no tabuleiro */
const box = 22;

/* quantidade de casas no tabuleiro */
const cols = canvas.width / box;
const rows = canvas.height / box;

/* elementos da interface */
const scoreEl = document.getElementById("score");
const bestEl = document.getElementById("best");
const statusEl = document.getElementById("status");

/* guarda o recorde no navegador */
let bestScore = Number(localStorage.getItem("dzstorms_snake_best") || 0);
bestEl.textContent = bestScore;

/* variáveis principais do jogo */
let snake;
let food;
let direction;
let nextDirection;
let score;
let gameOver;
let paused;
let speed;
let gameLoop;

/* inicia o jogo */
function startGame() {
  snake = [
    { x: 9, y: 10 },
    { x: 8, y: 10 },
    { x: 7, y: 10 },
    { x: 6, y: 10 }
  ];

  direction = "RIGHT";
  nextDirection = "RIGHT";
  score = 0;
  gameOver = false;
  paused = false;
  speed = 120;

  scoreEl.textContent = score;
  statusEl.textContent = "jogando";

  createFood();

  clearInterval(gameLoop);
  gameLoop = setInterval(updateGame, speed);
}

/* reinicia o jogo */
function restartGame() {
  startGame();
}

/* pausa ou continua o jogo */
function togglePause() {
  if (gameOver) return;

  paused = !paused;
  statusEl.textContent = paused ? "pausado" : "jogando";
}

/* cria a comida em posição aleatória */
function createFood() {
  food = {
    x: Math.floor(Math.random() * cols),
    y: Math.floor(Math.random() * rows)
  };

  /* impede a comida de aparecer dentro da cobra */
  for (let part of snake) {
    if (part.x === food.x && part.y === food.y) {
      createFood();
      return;
    }
  }
}

/* desenha o fundo do tabuleiro */
function drawBackground() {
  ctx.fillStyle = "#071108";
  ctx.fillRect(0, 0, canvas.width, canvas.height);

  /* grelha do fundo */
  ctx.strokeStyle = "rgba(255,255,255,0.035)";
  ctx.lineWidth = 1;

  for (let x = 0; x <= canvas.width; x += box) {
    ctx.beginPath();
    ctx.moveTo(x, 0);
    ctx.lineTo(x, canvas.height);
    ctx.stroke();
  }

  for (let y = 0; y <= canvas.height; y += box) {
    ctx.beginPath();
    ctx.moveTo(0, y);
    ctx.lineTo(canvas.width, y);
    ctx.stroke();
  }

  /* brilho verde suave no fundo */
  const glow = ctx.createRadialGradient(
    canvas.width / 2,
    canvas.height / 2,
    40,
    canvas.width / 2,
    canvas.height / 2,
    280
  );

  glow.addColorStop(0, "rgba(37,196,90,0.08)");
  glow.addColorStop(1, "rgba(37,196,90,0)");

  ctx.fillStyle = glow;
  ctx.fillRect(0, 0, canvas.width, canvas.height);
}

/* desenha a maçã */
function drawFood() {
  const centerX = food.x * box + box / 2;
  const centerY = food.y * box + box / 2;

  /* corpo da maçã */
  const gradient = ctx.createRadialGradient(
    centerX - 4,
    centerY - 5,
    2,
    centerX,
    centerY,
    13
  );

  gradient.addColorStop(0, "#ff9b9b");
  gradient.addColorStop(1, "#d90429");

  ctx.fillStyle = gradient;
  ctx.beginPath();
  ctx.arc(centerX, centerY + 1, box * 0.38, 0, Math.PI * 2);
  ctx.fill();

  /* brilho da maçã */
  ctx.fillStyle = "rgba(255,255,255,0.65)";
  ctx.beginPath();
  ctx.arc(centerX - 4, centerY - 4, 3, 0, Math.PI * 2);
  ctx.fill();

  /* folha */
  ctx.fillStyle = "#25c45a";
  ctx.beginPath();
  ctx.ellipse(centerX + 4, centerY - 10, 5, 3, -0.5, 0, Math.PI * 2);
  ctx.fill();

  /* cabo */
  ctx.strokeStyle = "#5c3a16";
  ctx.lineWidth = 2;
  ctx.beginPath();
  ctx.moveTo(centerX, centerY - 9);
  ctx.lineTo(centerX + 2, centerY - 14);
  ctx.stroke();
}

/* pega o centro de uma parte da cobra */
function getCenter(part) {
  return {
    x: part.x * box + box / 2,
    y: part.y * box + box / 2
  };
}

/* define a rotação da cabeça */
function getHeadAngle() {
  if (direction === "RIGHT") return 0;
  if (direction === "LEFT") return Math.PI;
  if (direction === "UP") return -Math.PI / 2;
  if (direction === "DOWN") return Math.PI / 2;

  return 0;
}

/* desenha a cobra como uma cobra real, com corpo ligado */
function drawSnake() {
  if (snake.length === 0) return;

  ctx.save();

  /*
    Primeiro desenha a sombra do corpo.
    Isso dá profundidade e deixa a cobra menos plana.
  */
  ctx.beginPath();

  for (let i = snake.length - 1; i >= 0; i--) {
    const p = getCenter(snake[i]);

    if (i === snake.length - 1) {
      ctx.moveTo(p.x, p.y);
    } else {
      ctx.lineTo(p.x, p.y);
    }
  }

  ctx.lineCap = "round";
  ctx.lineJoin = "round";
  ctx.strokeStyle = "rgba(0, 0, 0, 0.55)";
  ctx.lineWidth = 26;
  ctx.stroke();

  /*
    Corpo principal da cobra.
    Ele é uma linha grossa com pontas arredondadas.
  */
  ctx.beginPath();

  for (let i = snake.length - 1; i >= 0; i--) {
    const p = getCenter(snake[i]);

    if (i === snake.length - 1) {
      ctx.moveTo(p.x, p.y);
    } else {
      ctx.lineTo(p.x, p.y);
    }
  }

  const bodyGradient = ctx.createLinearGradient(0, 0, canvas.width, canvas.height);
  bodyGradient.addColorStop(0, "#8cff8c");
  bodyGradient.addColorStop(0.45, "#25c45a");
  bodyGradient.addColorStop(1, "#0b7a35");

  ctx.strokeStyle = bodyGradient;
  ctx.lineWidth = 20;
  ctx.stroke();

  /*
    Linha de brilho no corpo.
    Faz a cobra parecer mais viva.
  */
  ctx.beginPath();

  for (let i = snake.length - 1; i >= 0; i--) {
    const p = getCenter(snake[i]);

    if (i === snake.length - 1) {
      ctx.moveTo(p.x, p.y);
    } else {
      ctx.lineTo(p.x, p.y);
    }
  }

  ctx.strokeStyle = "rgba(255,255,255,0.20)";
  ctx.lineWidth = 6;
  ctx.stroke();

  /*
    Manchas pequenas no corpo.
    Dá mais aparência de pele de cobra.
  */
  for (let i = 2; i < snake.length; i += 2) {
    const p = getCenter(snake[i]);

    ctx.fillStyle = "rgba(0, 70, 30, 0.42)";
    ctx.beginPath();
    ctx.arc(p.x, p.y, 3.2, 0, Math.PI * 2);
    ctx.fill();
  }

  /*
    Desenha a cauda mais fina.
  */
  drawSnakeTail();

  /*
    Desenha a cabeça por cima do corpo.
  */
  drawSnakeHead();

  ctx.restore();
}

/* desenha a cauda da cobra */
function drawSnakeTail() {
  const tail = getCenter(snake[snake.length - 1]);

  ctx.fillStyle = "#0b7a35";
  ctx.beginPath();
  ctx.arc(tail.x, tail.y, 7, 0, Math.PI * 2);
  ctx.fill();
}

/* desenha a cabeça com olhos e língua */
function drawSnakeHead() {
  const head = getCenter(snake[0]);
  const angle = getHeadAngle();

  ctx.save();

  ctx.translate(head.x, head.y);
  ctx.rotate(angle);

  /*
    Língua bifurcada.
    Ela é desenhada antes da cabeça para parecer que sai da boca.
  */
  ctx.strokeStyle = "#ff2f5f";
  ctx.lineWidth = 2;
  ctx.lineCap = "round";

  ctx.beginPath();
  ctx.moveTo(12, 0);
  ctx.lineTo(23, 0);
  ctx.stroke();

  ctx.beginPath();
  ctx.moveTo(23, 0);
  ctx.lineTo(29, -4);
  ctx.stroke();

  ctx.beginPath();
  ctx.moveTo(23, 0);
  ctx.lineTo(29, 4);
  ctx.stroke();

  /*
    Cabeça oval.
  */
  const headGradient = ctx.createRadialGradient(-4, -4, 2, 0, 0, 18);
  headGradient.addColorStop(0, "#a6ffa6");
  headGradient.addColorStop(0.5, "#35d66b");
  headGradient.addColorStop(1, "#11843d");

  ctx.fillStyle = headGradient;
  ctx.beginPath();
  ctx.ellipse(0, 0, 15.5, 12.5, 0, 0, Math.PI * 2);
  ctx.fill();

  ctx.strokeStyle = "rgba(255,255,255,0.28)";
  ctx.lineWidth = 2;
  ctx.stroke();

  /*
    Olhos.
  */
  ctx.fillStyle = "#ffffff";

  ctx.beginPath();
  ctx.arc(5, -6, 3.6, 0, Math.PI * 2);
  ctx.fill();

  ctx.beginPath();
  ctx.arc(5, 6, 3.6, 0, Math.PI * 2);
  ctx.fill();

  /*
    Pupilas.
  */
  ctx.fillStyle = "#000000";

  ctx.beginPath();
  ctx.arc(6.2, -6, 1.6, 0, Math.PI * 2);
  ctx.fill();

  ctx.beginPath();
  ctx.arc(6.2, 6, 1.6, 0, Math.PI * 2);
  ctx.fill();

  /*
    Pequena boca.
  */
  ctx.strokeStyle = "rgba(0,0,0,0.45)";
  ctx.lineWidth = 1.5;

  ctx.beginPath();
  ctx.moveTo(10, -2);
  ctx.quadraticCurveTo(13, 0, 10, 2);
  ctx.stroke();

  ctx.restore();
}

/* mostra a tela de game over */
function drawGameOver() {
  ctx.fillStyle = "rgba(0,0,0,0.72)";
  ctx.fillRect(0, 0, canvas.width, canvas.height);

  ctx.fillStyle = "#25c45a";
  ctx.font = "700 34px Orbitron";
  ctx.textAlign = "center";
  ctx.fillText("GAME OVER", canvas.width / 2, canvas.height / 2 - 25);

  ctx.fillStyle = "#ffffff";
  ctx.font = "500 20px Rajdhani";
  ctx.fillText("clica em reiniciar para jogar novamente", canvas.width / 2, canvas.height / 2 + 18);
}

/* atualiza o jogo a cada movimento */
function updateGame() {
  if (paused || gameOver) return;

  direction = nextDirection;

  let head = { ...snake[0] };

  if (direction === "LEFT") head.x--;
  if (direction === "RIGHT") head.x++;
  if (direction === "UP") head.y--;
  if (direction === "DOWN") head.y++;

  /* verifica colisão com parede */
  if (head.x < 0 || head.x >= cols || head.y < 0 || head.y >= rows) {
    endGame();
    return;
  }

  const willEat = head.x === food.x && head.y === food.y;

  /*
    Para verificar colisão com o corpo,
    se a cobra não vai comer, ignoramos a cauda,
    porque ela vai sair do lugar no mesmo movimento.
  */
  const bodyToCheck = willEat ? snake : snake.slice(0, snake.length - 1);

  for (let part of bodyToCheck) {
    if (head.x === part.x && head.y === part.y) {
      endGame();
      return;
    }
  }

  /* adiciona a nova cabeça */
  snake.unshift(head);

  /* se comer a maçã */
  if (willEat) {
    score += 10;
    scoreEl.textContent = score;

    if (score > bestScore) {
      bestScore = score;
      localStorage.setItem("dzstorms_snake_best", bestScore);
      bestEl.textContent = bestScore;
    }

    createFood();

    /*
      Aumenta a velocidade aos poucos.
    */
    if (score % 50 === 0 && speed > 65) {
      speed -= 8;
      clearInterval(gameLoop);
      gameLoop = setInterval(updateGame, speed);
    }

  } else {
    /* remove a cauda se não comeu */
    snake.pop();
  }

  drawBackground();
  drawFood();
  drawSnake();
}

/* termina o jogo */
function endGame() {
  gameOver = true;
  statusEl.textContent = "perdeu";

  clearInterval(gameLoop);

  drawBackground();
  drawFood();
  drawSnake();
  drawGameOver();
}

/* controla as setas do teclado */
document.addEventListener("keydown", function(event) {
  const keys = ["ArrowUp", "ArrowDown", "ArrowLeft", "ArrowRight", " "];

  /*
    Impede a página de mexer quando usa as setas.
  */
  if (keys.includes(event.key)) {
    event.preventDefault();
  }

  if (event.key === "ArrowLeft" && direction !== "RIGHT") {
    nextDirection = "LEFT";
  }

  if (event.key === "ArrowRight" && direction !== "LEFT") {
    nextDirection = "RIGHT";
  }

  if (event.key === "ArrowUp" && direction !== "DOWN") {
    nextDirection = "UP";
  }

  if (event.key === "ArrowDown" && direction !== "UP") {
    nextDirection = "DOWN";
  }

  if (event.key === " ") {
    togglePause();
  }
});

/* desenha a primeira tela */
startGame();
drawBackground();
drawFood();
drawSnake();
</script>

<?php
/*
  Fecha a div aberta no header.php.
  Como é uma página de jogo, usamos scripts.php para não mostrar o rodapé.
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