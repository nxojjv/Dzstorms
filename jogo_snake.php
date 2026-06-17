<?php
/* carrega o cabeçalho do site */
require_once __DIR__ . '/header.php';
?>

<div class="container py-5">
  <div class="card card-dark shadow mx-auto text-center" style="max-width: 760px;">
    <div class="card-body p-5">

      <!-- título do jogo -->
      <h1 class="fw-bold mb-2">SNAKE</h1>

      <!-- pequena descrição do jogo -->
      <p class="text-light-emphasis mb-3">
        controla a cobra, come as maçãs e tenta bater o teu recorde.
      </p>

      <!-- painel com informações do jogo -->
      <div class="d-flex justify-content-center gap-4 mb-3 flex-wrap">
        <div>pontos: <strong id="score" class="text-success">0</strong></div>
        <div>recorde: <strong id="recorde" class="text-warning">0</strong></div>
        <div>nível: <strong id="nivel" class="text-info">1</strong></div>
        <div>desafio: <strong id="desafio" class="text-light">0/20</strong></div>
      </div>

      <!-- área onde o jogo é desenhado -->
      <canvas 
        id="game" 
        width="420" 
        height="420"
        style="
          background:#0b1f0b; 
          border:3px solid #25c45a; 
          border-radius:18px; 
          box-shadow:0 0 25px rgba(37,196,90,0.35);
          display:block;
          margin:0 auto;
        ">
      </canvas>

      <!-- mensagem mostrada quando o jogador chega aos 20 pontos -->
      <div id="mensagem" class="alert alert-success mt-4 d-none">
        desafio concluído! chegaste aos 20 pontos.
      </div>

      <!-- botões do jogo -->
      <div class="mt-4">

        <!-- type="button" evita comportamento estranho de formulário -->
        <button type="button" class="btn btn-success" onclick="reiniciarJogo()">
          reiniciar
        </button>

        <a href="biblioteca.php" class="btn btn-outline-light ms-2">
          voltar
        </a>
      </div>

      <!-- instrução para o utilizador -->
      <p class="text-light-emphasis small mt-3 mb-0">
        usa as setas do teclado para jogar.
      </p>
    </div>
  </div>
</div>

<script>
/* pega o canvas e prepara o contexto 2D para desenhar */
const canvas = document.getElementById("game");
const ctx = canvas.getContext("2d");

/* pega os elementos do HTML onde aparecem as informações */
const scoreEl = document.getElementById("score");
const recordeEl = document.getElementById("recorde");
const nivelEl = document.getElementById("nivel");
const desafioEl = document.getElementById("desafio");
const mensagemEl = document.getElementById("mensagem");

/* tamanho de cada quadrado do jogo */
const tamanho = 21;

/* quantidade total de quadrados no tabuleiro */
const total = canvas.width / tamanho;

/* variáveis principais do jogo */
let cobra;
let comida;
let direcao;
let proximaDirecao;
let pontos;
let nivel;
let velocidade;
let acabou;
let intervalo;

/* pega o recorde guardado no navegador */
let recorde = localStorage.getItem("reflexStormRecorde") || 0;
recordeEl.textContent = recorde;

/* função que inicia os valores do jogo */
function iniciarValores() {

  /* posição inicial da cobra */
  cobra = [
    { x: 10, y: 10 },
    { x: 9, y: 10 },
    { x: 8, y: 10 }
  ];

  /* posição inicial da comida */
  comida = { x: 5, y: 5 };

  /* direção inicial da cobra: para a direita */
  direcao = { x: 1, y: 0 };
  proximaDirecao = { x: 1, y: 0 };

  /* valores iniciais */
  pontos = 0;
  nivel = 1;
  velocidade = 150;
  acabou = false;

  /* atualiza as informações na tela */
  scoreEl.textContent = pontos;
  nivelEl.textContent = nivel;
  desafioEl.textContent = "0/20";

  /* esconde a mensagem de desafio concluído */
  mensagemEl.classList.add("d-none");

  /* cria uma comida em posição aleatória */
  criarComida();
}

/* função que inicia o loop do jogo */
function iniciarLoop() {

  /* evita criar vários intervalos ao mesmo tempo */
  clearInterval(intervalo);

  /* chama a função desenhar conforme a velocidade atual */
  intervalo = setInterval(desenhar, velocidade);
}

/* função principal do jogo */
function desenhar() {

  /* se o jogo acabou, não faz mais nada */
  if (acabou) return;

  /* aplica a próxima direção escolhida pelo jogador */
  direcao = proximaDirecao;

  /* calcula a nova posição da cabeça */
  const cabeca = {
    x: cobra[0].x + direcao.x,
    y: cobra[0].y + direcao.y
  };

  /* verifica se bateu na parede ou nela própria */
  if (
    cabeca.x < 0 || cabeca.x >= total ||
    cabeca.y < 0 || cabeca.y >= total ||
    bateuNaCobra(cabeca)
  ) {
    fimDeJogo();
    return;
  }

  /* adiciona a nova cabeça no início da cobra */
  cobra.unshift(cabeca);

  /* verifica se a cobra comeu a comida */
  if (cabeca.x === comida.x && cabeca.y === comida.y) {

    /* aumenta a pontuação */
    pontos++;

    /* atualiza a pontuação na tela */
    scoreEl.textContent = pontos;
    desafioEl.textContent = pontos + "/20";

    /* atualiza recorde e nível */
    atualizarRecorde();
    atualizarNivel();

    /* cria nova comida */
    criarComida();

    /* se chegar aos 20 pontos, mostra mensagem */
    if (pontos >= 20) {
      mensagemEl.classList.remove("d-none");
    }

  } else {

    /* se não comeu, remove o último pedaço para a cobra andar */
    cobra.pop();
  }

  /* redesenha o jogo */
  limparTela();
  desenharGrade();
  desenharComida();
  desenharCobra();
}

/* limpa o canvas */
function limparTela() {
  ctx.fillStyle = "#0b1f0b";
  ctx.fillRect(0, 0, canvas.width, canvas.height);
}

/* desenha o fundo quadriculado */
function desenharGrade() {
  for (let y = 0; y < total; y++) {
    for (let x = 0; x < total; x++) {

      /* alterna as cores dos quadrados */
      ctx.fillStyle = (x + y) % 2 === 0 ? "#163d16" : "#123312";

      /* desenha cada quadrado */
      ctx.fillRect(x * tamanho, y * tamanho, tamanho, tamanho);
    }
  }
}

/* desenha a cobra inteira */
function desenharCobra() {
  cobra.forEach((parte, index) => {

    /* converte posição da grade para pixels */
    const px = parte.x * tamanho;
    const py = parte.y * tamanho;

    /* a primeira parte é a cabeça */
    if (index === 0) {
      desenharCabeca(px, py);
    } else {
      desenharCorpo(px, py);
    }
  });
}

/* desenha a cabeça da cobra */
function desenharCabeca(px, py) {

  /* cabeça azul */
  ctx.fillStyle = "#61d4ff";
  ctx.beginPath();
  ctx.roundRect(px + 1, py + 1, tamanho - 2, tamanho - 2, 10);
  ctx.fill();

  /* olhos */
  ctx.fillStyle = "#ffffff";
  ctx.beginPath();
  ctx.arc(px + 7, py + 7, 4, 0, Math.PI * 2);
  ctx.arc(px + 15, py + 7, 4, 0, Math.PI * 2);
  ctx.fill();

  /* pupilas */
  ctx.fillStyle = "#000000";
  ctx.beginPath();
  ctx.arc(px + 8, py + 7, 2, 0, Math.PI * 2);
  ctx.arc(px + 16, py + 7, 2, 0, Math.PI * 2);
  ctx.fill();

  /* língua */
  ctx.strokeStyle = "#ff3b3b";
  ctx.lineWidth = 2;
  ctx.beginPath();
  ctx.moveTo(px + 11, py + 16);
  ctx.lineTo(px + 11, py + 22);
  ctx.stroke();
}

/* desenha o corpo da cobra */
function desenharCorpo(px, py) {

  /* corpo azul */
  ctx.fillStyle = "#438cff";
  ctx.beginPath();
  ctx.roundRect(px + 1, py + 1, tamanho - 2, tamanho - 2, 9);
  ctx.fill();

  /* brilho no corpo */
  ctx.fillStyle = "rgba(255,255,255,0.18)";
  ctx.beginPath();
  ctx.arc(px + 7, py + 7, 3, 0, Math.PI * 2);
  ctx.fill();
}

/* desenha a maçã */
function desenharComida() {

  /* centro da comida */
  const cx = comida.x * tamanho + tamanho / 2;
  const cy = comida.y * tamanho + tamanho / 2;

  /* maçã vermelha */
  ctx.fillStyle = "#ff3b3b";
  ctx.beginPath();
  ctx.arc(cx, cy, tamanho / 2 - 3, 0, Math.PI * 2);
  ctx.fill();

  /* brilho da maçã */
  ctx.fillStyle = "#ffffff";
  ctx.beginPath();
  ctx.arc(cx - 3, cy - 3, 2, 0, Math.PI * 2);
  ctx.fill();

  /* cabo da maçã */
  ctx.fillStyle = "#25c45a";
  ctx.fillRect(cx - 1, cy - 12, 4, 7);
}

/* verifica se a cabeça bateu no corpo da cobra */
function bateuNaCobra(cabeca) {
  return cobra.some(parte => parte.x === cabeca.x && parte.y === cabeca.y);
}

/* cria comida numa posição aleatória */
function criarComida() {
  comida = {
    x: Math.floor(Math.random() * total),
    y: Math.floor(Math.random() * total)
  };

  /* se a comida nascer em cima da cobra, cria outra */
  if (cobra.some(parte => parte.x === comida.x && parte.y === comida.y)) {
    criarComida();
  }
}

/* atualiza o recorde guardado no navegador */
function atualizarRecorde() {
  if (pontos > recorde) {
    recorde = pontos;
    localStorage.setItem("reflexStormRecorde", recorde);
    recordeEl.textContent = recorde;
  }
}

/* aumenta a dificuldade conforme os pontos */
function atualizarNivel() {

  let novoNivel = 1;
  let novaVelocidade = 150;

  if (pontos >= 5) {
    novoNivel = 2;
    novaVelocidade = 125;
  }

  if (pontos >= 10) {
    novoNivel = 3;
    novaVelocidade = 100;
  }

  if (pontos >= 15) {
    novoNivel = 4;
    novaVelocidade = 80;
  }

  /* se mudou de nível, atualiza a velocidade do jogo */
  if (novoNivel !== nivel) {
    nivel = novoNivel;
    velocidade = novaVelocidade;
    nivelEl.textContent = nivel;
    iniciarLoop();
  }
}

/* termina o jogo */
function fimDeJogo() {

  /* marca o jogo como terminado */
  acabou = true;

  /* para o loop */
  clearInterval(intervalo);

  /* escurece o canvas */
  ctx.fillStyle = "rgba(0,0,0,0.75)";
  ctx.fillRect(0, 0, canvas.width, canvas.height);

  /* texto de fim de jogo */
  ctx.fillStyle = "#ffffff";
  ctx.font = "34px Arial";
  ctx.textAlign = "center";
  ctx.fillText("fim do jogo", canvas.width / 2, canvas.height / 2 - 20);

  /* mostra pontos e recorde */
  ctx.font = "20px Arial";
  ctx.fillText("pontos: " + pontos, canvas.width / 2, canvas.height / 2 + 20);
  ctx.fillText("recorde: " + recorde, canvas.width / 2, canvas.height / 2 + 50);
}

/* reinicia o jogo */
function reiniciarJogo() {
  iniciarValores();
  limparTela();
  desenharGrade();
  desenharComida();
  desenharCobra();
  iniciarLoop();
}

/* controla a cobra pelas setas do teclado */
document.addEventListener("keydown", function(e) {

  /* lista das teclas usadas no jogo */
  const teclasDoJogo = ["ArrowUp", "ArrowDown", "ArrowLeft", "ArrowRight"];

  /* impede que as setas façam a página subir/descer */
  if (teclasDoJogo.includes(e.key)) {
    e.preventDefault();
  }

  /* seta para cima */
  if (e.key === "ArrowUp" && direcao.y !== 1) {
    proximaDirecao = { x: 0, y: -1 };
  }

  /* seta para baixo */
  if (e.key === "ArrowDown" && direcao.y !== -1) {
    proximaDirecao = { x: 0, y: 1 };
  }

  /* seta para esquerda */
  if (e.key === "ArrowLeft" && direcao.x !== 1) {
    proximaDirecao = { x: -1, y: 0 };
  }

  /* seta para direita */
  if (e.key === "ArrowRight" && direcao.x !== -1) {
    proximaDirecao = { x: 1, y: 0 };
  }

}, { passive: false });

/* inicia o jogo assim que a página carrega */
iniciarValores();
limparTela();
desenharGrade();
desenharComida();
desenharCobra();
iniciarLoop();
</script>

<?php 
/* carrega o rodapé do site */
require_once __DIR__ . '/footer.php'; 
?>