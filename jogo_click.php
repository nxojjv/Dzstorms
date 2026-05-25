<?php
require_once __DIR__ . '/header.php';
?>

<div class="container py-5">
  <div class="card card-dark shadow mx-auto text-center" style="max-width: 720px;">
    <div class="card-body p-5">
      <h1 class="fw-bold mb-3">Reflex Storm</h1>

      <p class="text-light-emphasis mb-3">
        controla a cobrinha e come as maçãs vermelhas.
      </p>

      <h4 class="mb-3">
        pontos: <span id="score" class="text-success">0</span>
      </h4>

      <canvas 
        id="game" 
        width="400" 
        height="400" 
        style="background:#071107; border:2px solid #25c45a; border-radius:12px;">
      </canvas>

      <div class="mt-4">
        <button class="btn btn-success" onclick="reiniciarJogo()">reiniciar</button>
        <a href="biblioteca.php" class="btn btn-outline-light ms-2">voltar</a>
      </div>
    </div>
  </div>
</div>

<script>
const canvas = document.getElementById("game");
const ctx = canvas.getContext("2d");
const scoreEl = document.getElementById("score");

const tamanho = 20;
const total = canvas.width / tamanho;

let cobra = [{ x: 10, y: 10 }];
let comida = { x: 5, y: 5 };
let direcao = { x: 1, y: 0 };
let proximaDirecao = { x: 1, y: 0 };
let pontos = 0;
let acabou = false;

function desenhar() {
  if (acabou) return;

  direcao = proximaDirecao;

  const cabeca = {
    x: cobra[0].x + direcao.x,
    y: cobra[0].y + direcao.y
  };

  if (
    cabeca.x < 0 || cabeca.x >= total ||
    cabeca.y < 0 || cabeca.y >= total ||
    bateuNaCobra(cabeca)
  ) {
    fimDeJogo();
    return;
  }

  cobra.unshift(cabeca);

  if (cabeca.x === comida.x && cabeca.y === comida.y) {
    pontos++;
    scoreEl.textContent = pontos;
    criarComida();
  } else {
    cobra.pop();
  }

  limparTela();
  desenharComida();
  desenharCobra();
}

function limparTela() {
  ctx.clearRect(0, 0, canvas.width, canvas.height);

  ctx.fillStyle = "#071107";
  ctx.fillRect(0, 0, canvas.width, canvas.height);
}

function desenharCobra() {
  cobra.forEach((parte, index) => {
    const px = parte.x * tamanho;
    const py = parte.y * tamanho;

    if (index === 0) {
      desenharCabeca(px, py);
    } else {
      desenharCorpo(px, py);
    }
  });
}

function desenharCabeca(px, py) {
  ctx.fillStyle = "#7CFF4D";
  ctx.beginPath();
  ctx.roundRect(px + 1, py + 1, tamanho - 2, tamanho - 2, 7);
  ctx.fill();

  ctx.fillStyle = "#000";

  ctx.beginPath();
  ctx.arc(px + 6, py + 7, 2, 0, Math.PI * 2);
  ctx.arc(px + 14, py + 7, 2, 0, Math.PI * 2);
  ctx.fill();

  ctx.strokeStyle = "#000";
  ctx.lineWidth = 2;
  ctx.beginPath();
  ctx.moveTo(px + 10, py + 12);
  ctx.lineTo(px + 10, py + 16);
  ctx.stroke();
}

function desenharCorpo(px, py) {
  ctx.fillStyle = "#25c45a";
  ctx.beginPath();
  ctx.roundRect(px + 2, py + 2, tamanho - 4, tamanho - 4, 6);
  ctx.fill();

  ctx.fillStyle = "rgba(255,255,255,0.25)";
  ctx.beginPath();
  ctx.arc(px + 7, py + 7, 2, 0, Math.PI * 2);
  ctx.fill();
}

function desenharComida() {
  const cx = comida.x * tamanho + tamanho / 2;
  const cy = comida.y * tamanho + tamanho / 2;

  ctx.fillStyle = "#ff3b3b";
  ctx.beginPath();
  ctx.arc(cx, cy, tamanho / 2 - 3, 0, Math.PI * 2);
  ctx.fill();

  ctx.fillStyle = "#ffffff";
  ctx.beginPath();
  ctx.arc(cx - 3, cy - 3, 2, 0, Math.PI * 2);
  ctx.fill();

  ctx.fillStyle = "#25c45a";
  ctx.fillRect(cx - 1, cy - 11, 3, 6);
}

function bateuNaCobra(cabeca) {
  return cobra.some(parte => parte.x === cabeca.x && parte.y === cabeca.y);
}

function criarComida() {
  comida = {
    x: Math.floor(Math.random() * total),
    y: Math.floor(Math.random() * total)
  };

  if (cobra.some(parte => parte.x === comida.x && parte.y === comida.y)) {
    criarComida();
  }
}

function fimDeJogo() {
  acabou = true;

  ctx.fillStyle = "rgba(0,0,0,0.75)";
  ctx.fillRect(0, 0, canvas.width, canvas.height);

  ctx.fillStyle = "#ffffff";
  ctx.font = "30px Arial";
  ctx.textAlign = "center";
  ctx.fillText("fim do jogo", canvas.width / 2, canvas.height / 2 - 10);

  ctx.font = "18px Arial";
  ctx.fillText("pontos: " + pontos, canvas.width / 2, canvas.height / 2 + 25);
}

function reiniciarJogo() {
  cobra = [{ x: 10, y: 10 }];
  comida = { x: 5, y: 5 };
  direcao = { x: 1, y: 0 };
  proximaDirecao = { x: 1, y: 0 };
  pontos = 0;
  acabou = false;
  scoreEl.textContent = pontos;

  limparTela();
  desenharComida();
  desenharCobra();
}

document.addEventListener("keydown", function(e) {
  if (e.key === "ArrowUp" && direcao.y !== 1) {
    proximaDirecao = { x: 0, y: -1 };
  }

  if (e.key === "ArrowDown" && direcao.y !== -1) {
    proximaDirecao = { x: 0, y: 1 };
  }

  if (e.key === "ArrowLeft" && direcao.x !== 1) {
    proximaDirecao = { x: -1, y: 0 };
  }

  if (e.key === "ArrowRight" && direcao.x !== -1) {
    proximaDirecao = { x: 1, y: 0 };
  }
});

limparTela();
desenharComida();
desenharCobra();

setInterval(desenhar, 120);
</script>

<?php require_once __DIR__ . '/footer.php'; ?>