<?php
// carrega o cabeçalho do site
require_once __DIR__ . '/header.php';
?>

<div class="container py-5">
  <div class="card card-dark shadow mx-auto text-center" style="max-width: 620px;">
    <div class="card-body p-4">

      <!-- título do jogo -->
      <h1 class="fw-bold mb-3">PAC-MAN</h1>

      <!-- informações do jogo -->
      <div class="d-flex justify-content-between mx-auto mb-2" style="width:440px;">
        <div>Pontos: <span id="score">0</span></div>
        <div>Vidas: <span id="lives">3</span></div>
      </div>

      <!-- área onde o jogo é desenhado -->
      <canvas 
        id="canvas" 
        width="440" 
        height="440"
        style="
          border:4px solid #214cff; 
          background:black; 
          box-shadow:0 0 20px rgba(33,76,255,0.6);
          display:block;
          margin:0 auto;
        ">
      </canvas>

      <!-- mensagem do jogo -->
      <div id="message" class="mt-3 text-warning">
        usa as setas do teclado para jogar!
      </div>

      <!-- botões -->
      <div class="mt-4">

        <!-- type="button" evita comportamentos estranhos -->
        <button type="button" class="btn btn-success" onclick="reiniciarJogo()">
          reiniciar
        </button>

        <a href="biblioteca.php" class="btn btn-outline-light ms-2">
          voltar
        </a>
      </div>

    </div>
  </div>
</div>

<script>
/* pega o canvas e prepara o contexto 2D */
const canvas = document.getElementById("canvas");
const ctx = canvas.getContext("2d");

/* pega os elementos do HTML */
const scoreEl = document.getElementById("score");
const livesEl = document.getElementById("lives");
const messageEl = document.getElementById("message");

/* tamanho de cada quadrado do mapa */
const tileSize = 20;

/* velocidade do Pac-Man */
const speed = 2;

/* velocidade dos fantasmas */
const ghostSpeed = 2;

/* variáveis principais do jogo */
let score = 0;
let lives = 3;
let gameOver = false;
let gameWon = false;
let powerModeTimer = 0;

/*
  mapa do jogo:
  1 = parede
  0 = ponto pequeno
  2 = ponto grande
  3 = espaço vazio
*/
const map = [
  [1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1],
  [1,2,0,0,0,0,0,0,0,0,1,1,0,0,0,0,0,0,0,0,2,1],
  [1,0,1,1,1,0,1,1,1,0,1,1,0,1,1,1,0,1,1,1,0,1],
  [1,0,1,1,1,0,1,1,1,0,0,0,0,1,1,1,0,1,1,1,0,1],
  [1,0,0,0,0,0,0,0,0,0,1,1,0,0,0,0,0,0,0,0,0,1],
  [1,0,1,1,1,0,1,0,1,1,1,1,1,1,0,1,0,1,1,1,0,1],
  [1,0,0,0,0,0,1,0,0,0,1,1,0,0,0,1,0,0,0,0,0,1],
  [1,1,1,1,1,0,1,1,1,3,1,1,3,1,1,1,0,1,1,1,1,1],
  [3,3,3,3,1,0,1,3,3,3,3,3,3,3,3,1,0,1,3,3,3,3],
  [1,1,1,1,1,0,1,3,1,1,3,3,1,1,3,1,0,1,1,1,1,1],
  [3,3,3,3,3,0,3,3,1,3,3,3,3,1,3,3,0,3,3,3,3,3],
  [1,1,1,1,1,0,1,3,1,1,1,1,1,1,3,1,0,1,1,1,1,1],
  [3,3,3,3,1,0,1,3,3,3,3,3,3,3,3,1,0,1,3,3,3,3],
  [1,1,1,1,1,0,1,3,1,1,1,1,1,1,3,1,0,1,1,1,1,1],
  [1,0,0,0,0,0,0,0,0,0,1,1,0,0,0,0,0,0,0,0,0,1],
  [1,0,1,1,1,0,1,1,1,0,1,1,0,1,1,1,0,1,1,1,0,1],
  [1,2,0,0,1,0,0,0,0,0,3,3,0,0,0,0,0,1,0,0,2,1],
  [1,1,1,0,1,0,1,0,1,1,1,1,1,1,0,1,0,1,0,1,1,1],
  [1,0,0,0,0,0,1,0,0,0,1,1,0,0,0,1,0,0,0,0,0,1],
  [1,0,1,1,1,1,1,1,1,0,1,1,0,1,1,1,1,1,1,0,1],
  [1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1],
  [1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1]
];

/* cópia do mapa original, usada durante o jogo */
let currentMap = JSON.parse(JSON.stringify(map));

/* classe do Pac-Man */
class Pacman {
  constructor() {
    this.reset();
  }

  /* coloca o Pac-Man na posição inicial */
  reset() {
    this.x = 10 * tileSize;
    this.y = 16 * tileSize;
    this.dirX = 0;
    this.dirY = 0;
    this.nextDirX = 0;
    this.nextDirY = 0;
    this.angle = 0;
  }

  /* desenha o Pac-Man */
  draw() {
    ctx.beginPath();

    /* animação da boca */
    let mouth = (Math.sin(Date.now() * 0.01) + 1) * 0.2;

    ctx.arc(
      this.x + tileSize / 2,
      this.y + tileSize / 2,
      tileSize / 2 - 2,
      (this.angle + mouth) * Math.PI,
      (this.angle + 2 - mouth) * Math.PI
    );

    ctx.lineTo(this.x + tileSize / 2, this.y + tileSize / 2);
    ctx.fillStyle = "yellow";
    ctx.fill();
    ctx.closePath();
  }

  /* move o Pac-Man */
  move() {

    /* só muda de direção quando está alinhado com a grelha */
    if (this.x % tileSize === 0 && this.y % tileSize === 0) {
      if (!this.checkCollision(this.nextDirX, this.nextDirY)) {
        this.dirX = this.nextDirX;
        this.dirY = this.nextDirY;

        /* altera o ângulo da boca conforme a direção */
        if (this.dirX === 1) this.angle = 0.2;
        if (this.dirX === -1) this.angle = 1.2;
        if (this.dirY === 1) this.angle = 0.7;
        if (this.dirY === -1) this.angle = 1.7;
      }
    }

    /* move se não houver parede */
    if (!this.checkCollision(this.dirX, this.dirY)) {
      this.x += this.dirX * speed;
      this.y += this.dirY * speed;
    }

    /* túnel lateral */
    if (this.x < 0) this.x = canvas.width - tileSize;
    if (this.x >= canvas.width) this.x = 0;

    /* verifica se comeu pontos */
    this.eatDots();
  }

  /* verifica colisão com paredes */
  checkCollision(dx, dy) {
    let nextX = this.x + dx * speed;
    let nextY = this.y + dy * speed;

    /* permite usar o túnel lateral */
    if (nextX < 0 || nextX >= canvas.width) return false;

    /* impede sair por cima ou por baixo */
    if (nextY < 0 || nextY >= canvas.height) return true;

    let tileLeft = Math.floor(nextX / tileSize);
    let tileRight = Math.floor((nextX + tileSize - 1) / tileSize);
    let tileTop = Math.floor(nextY / tileSize);
    let tileBottom = Math.floor((nextY + tileSize - 1) / tileSize);

    return currentMap[tileTop][tileLeft] === 1 ||
           currentMap[tileTop][tileRight] === 1 ||
           currentMap[tileBottom][tileLeft] === 1 ||
           currentMap[tileBottom][tileRight] === 1;
  }

  /* come os pontos do mapa */
  eatDots() {
    let tileX = Math.floor((this.x + tileSize / 2) / tileSize);
    let tileY = Math.floor((this.y + tileSize / 2) / tileSize);

    /* ponto pequeno */
    if (currentMap[tileY] && currentMap[tileY][tileX] === 0) {
      currentMap[tileY][tileX] = 3;
      score += 10;
      scoreEl.innerText = score;
      checkWin();
    }

    /* ponto grande */
    if (currentMap[tileY] && currentMap[tileY][tileX] === 2) {
      currentMap[tileY][tileX] = 3;
      score += 50;
      scoreEl.innerText = score;
      triggerPowerMode();
      checkWin();
    }
  }
}

/* classe dos fantasmas */
class Ghost {
  constructor(x, y, color) {
    this.startX = x * tileSize;
    this.startY = y * tileSize;
    this.color = color;
    this.reset();
  }

  /* volta o fantasma para a posição inicial */
  reset() {
    this.x = this.startX;
    this.y = this.startY;
    this.dirX = 0;
    this.dirY = -1;
  }

  /* desenha o fantasma */
  draw() {
    ctx.beginPath();
    ctx.arc(this.x + tileSize / 2, this.y + tileSize / 3 + 2, tileSize / 2 - 2, Math.PI, 0, false);
    ctx.lineTo(this.x + tileSize - 2, this.y + tileSize);
    ctx.lineTo(this.x + tileSize * 3 / 4, this.y + tileSize - 4);
    ctx.lineTo(this.x + tileSize / 2, this.y + tileSize);
    ctx.lineTo(this.x + tileSize / 4, this.y + tileSize - 4);
    ctx.lineTo(this.x + 2, this.y + tileSize);

    /* no modo poder, os fantasmas ficam azuis */
    ctx.fillStyle = powerModeTimer > 0 ? "#2121ff" : this.color;
    ctx.fill();
    ctx.closePath();

    /* olhos */
    ctx.beginPath();
    ctx.arc(this.x + 6, this.y + 6, 3, 0, Math.PI * 2);
    ctx.arc(this.x + 14, this.y + 6, 3, 0, Math.PI * 2);
    ctx.fillStyle = "white";
    ctx.fill();

    /* pupilas */
    ctx.beginPath();
    ctx.arc(this.x + 7, this.y + 6, 1.5, 0, Math.PI * 2);
    ctx.arc(this.x + 15, this.y + 6, 1.5, 0, Math.PI * 2);
    ctx.fillStyle = "black";
    ctx.fill();
  }

  /* move o fantasma perseguindo o Pac-Man */
  move() {

    /* só escolhe direção quando está alinhado com a grelha */
    if (this.x % tileSize === 0 && this.y % tileSize === 0) {

      const directions = [
        {x: 1, y: 0},
        {x: -1, y: 0},
        {x: 0, y: 1},
        {x: 0, y: -1}
      ];

      /* filtra caminhos sem parede */
      let validMoves = directions.filter(dir => !this.checkCollision(dir.x, dir.y));

      if (validMoves.length > 0) {
        let bestMove = validMoves[0];
        let bestDistance = Infinity;

        /* escolhe o caminho que deixa o fantasma mais perto do Pac-Man */
        validMoves.forEach(dir => {
          const nextX = this.x + dir.x * tileSize;
          const nextY = this.y + dir.y * tileSize;

          const distance = Math.hypot(
            nextX - pacman.x,
            nextY - pacman.y
          );

          if (distance < bestDistance) {
            bestDistance = distance;
            bestMove = dir;
          }
        });

        this.dirX = bestMove.x;
        this.dirY = bestMove.y;
      }
    }

    /* move o fantasma */
    this.x += this.dirX * ghostSpeed;
    this.y += this.dirY * ghostSpeed;
  }

  /* verifica colisão do fantasma com paredes */
  checkCollision(dx, dy) {
    let nextX = this.x + dx * ghostSpeed;
    let nextY = this.y + dy * ghostSpeed;

    /* impede sair do mapa */
    if (nextX < 0 || nextX >= canvas.width) return true;
    if (nextY < 0 || nextY >= canvas.height) return true;

    let tileLeft = Math.floor(nextX / tileSize);
    let tileRight = Math.floor((nextX + tileSize - 1) / tileSize);
    let tileTop = Math.floor(nextY / tileSize);
    let tileBottom = Math.floor((nextY + tileSize - 1) / tileSize);

    return currentMap[tileTop][tileLeft] === 1 ||
           currentMap[tileTop][tileRight] === 1 ||
           currentMap[tileBottom][tileLeft] === 1 ||
           currentMap[tileBottom][tileRight] === 1;
  }
}

/* cria o Pac-Man */
const pacman = new Pacman();

/* cria os fantasmas */
const ghosts = [
  new Ghost(10, 9, "red"),
  new Ghost(11, 10, "pink"),
  new Ghost(9, 10, "cyan"),
  new Ghost(10, 11, "orange")
];

/* ativa o modo poder quando o Pac-Man come ponto grande */
function triggerPowerMode() {
  powerModeTimer = 300;
}

/* verifica se o jogador venceu */
function checkWin() {
  for (let row of currentMap) {
    if (row.includes(0) || row.includes(2)) return;
  }

  gameWon = true;
  messageEl.innerText = "Parabéns! venceste!";
}

/* desenha o mapa */
function drawMap() {
  for (let r = 0; r < currentMap.length; r++) {
    for (let c = 0; c < currentMap[r].length; c++) {

      /* parede */
      if (currentMap[r][c] === 1) {
        ctx.fillStyle = "blue";
        ctx.fillRect(c * tileSize, r * tileSize, tileSize, tileSize);
      }

      /* ponto pequeno */
      if (currentMap[r][c] === 0) {
        ctx.beginPath();
        ctx.arc(c * tileSize + tileSize / 2, r * tileSize + tileSize / 2, 3, 0, Math.PI * 2);
        ctx.fillStyle = "#ffb8ae";
        ctx.fill();
        ctx.closePath();
      }

      /* ponto grande */
      if (currentMap[r][c] === 2) {
        ctx.beginPath();
        ctx.arc(c * tileSize + tileSize / 2, r * tileSize + tileSize / 2, 6, 0, Math.PI * 2);
        ctx.fillStyle = "#ffb8ae";
        ctx.fill();
        ctx.closePath();
      }
    }
  }
}

/* verifica colisão entre Pac-Man e fantasmas */
function checkCollisions() {
  ghosts.forEach(ghost => {

    let dist = Math.hypot(
      pacman.x + tileSize / 2 - (ghost.x + tileSize / 2),
      pacman.y + tileSize / 2 - (ghost.y + tileSize / 2)
    );

    if (dist < tileSize - 4) {

      /* se estiver no modo poder, come o fantasma */
      if (powerModeTimer > 0) {
        score += 200;
        scoreEl.innerText = score;
        ghost.reset();

      } else {

        /* se não estiver no modo poder, perde vida */
        lives--;
        livesEl.innerText = lives;

        if (lives === 0) {
          gameOver = true;
          messageEl.innerText = "Game Over! clica em reiniciar.";
        } else {
          pacman.reset();
          ghosts.forEach(g => g.reset());
        }
      }
    }
  });
}

/* atualiza a lógica do jogo */
function update() {
  if (gameOver || gameWon) return;

  if (powerModeTimer > 0) {
    powerModeTimer--;
  }

  pacman.move();
  ghosts.forEach(ghost => ghost.move());
  checkCollisions();
}

/* desenha todos os elementos */
function draw() {
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  drawMap();
  pacman.draw();
  ghosts.forEach(ghost => ghost.draw());
}

/* loop principal do jogo */
function gameLoop() {
  update();
  draw();
  requestAnimationFrame(gameLoop);
}

/* reinicia o jogo sem recarregar a página */
function reiniciarJogo() {
  score = 0;
  lives = 3;
  gameOver = false;
  gameWon = false;
  powerModeTimer = 0;

  currentMap = JSON.parse(JSON.stringify(map));

  scoreEl.innerText = score;
  livesEl.innerText = lives;
  messageEl.innerText = "usa as setas do teclado para jogar!";

  pacman.reset();
  ghosts.forEach(g => g.reset());
}

/* controla o teclado */
document.addEventListener("keydown", function(e) {

  /* teclas usadas no jogo */
  const teclasDoJogo = ["ArrowUp", "ArrowDown", "ArrowLeft", "ArrowRight"];

  /* impede que as setas façam a página subir/descer */
  if (teclasDoJogo.includes(e.key)) {
    e.preventDefault();
  }

  /* seta para cima */
  if (e.key === "ArrowUp") {
    pacman.nextDirX = 0;
    pacman.nextDirY = -1;
  }

  /* seta para baixo */
  if (e.key === "ArrowDown") {
    pacman.nextDirX = 0;
    pacman.nextDirY = 1;
  }

  /* seta para esquerda */
  if (e.key === "ArrowLeft") {
    pacman.nextDirX = -1;
    pacman.nextDirY = 0;
  }

  /* seta para direita */
  if (e.key === "ArrowRight") {
    pacman.nextDirX = 1;
    pacman.nextDirY = 0;
  }

}, { passive: false });

/* inicia o jogo */
gameLoop();
</script>

<?php
// carrega o rodapé do site
require_once __DIR__ . '/footer.php';
?>