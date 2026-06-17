<?php
/* 
  Este ficheiro mostra, na área de administrador,
  todos os pedidos/compras feitos pelos utilizadores.
*/

/* protege a página, permitindo acesso apenas ao administrador */
require_once __DIR__ . '/admin.php';

/* importa a ligação com a base de dados */
require_once __DIR__ . '/db.php';

/* importa funções auxiliares, como e() */
require_once __DIR__ . '/functions.php';

/* carrega o cabeçalho do site */
require_once __DIR__ . '/header.php';

/* cria a ligação com a base de dados */
$pdo = db();

/*
  Consulta os pedidos realizados.

  A tabela pedidos guarda os dados da compra.
  A tabela users guarda os dados do utilizador.

  O INNER JOIN junta as duas tabelas para mostrar:
  - id do pedido
  - total da compra
  - estado do pedido
  - data da compra
  - nome do utilizador
  - email do utilizador
*/
$st = $pdo->query("
  SELECT p.id, p.total, p.status, p.created_at, u.name, u.email
  FROM pedidos p
  INNER JOIN users u ON u.id = p.user_id
  ORDER BY p.created_at DESC
");

/* guarda todos os pedidos encontrados num array */
$pedidos = $st->fetchAll();
?>

<!-- cabeçalho da página de pedidos -->
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h2 class="fw-bold mb-1">pedidos</h2>
    <p class="text-light-emphasis mb-0">
      lista de compras feitas no sistema
    </p>
  </div>
</div>

<!-- card principal onde a tabela é apresentada -->
<div class="card border-0 shadow text-light" style="background: rgba(20,20,28,0.92);">
  <div class="card-body">

    <?php if (!$pedidos): ?>

      <!-- mensagem mostrada quando não existem pedidos -->
      <div class="alert alert-warning mb-0">
        nenhum pedido encontrado.
      </div>

    <?php else: ?>

      <!-- deixa a tabela responsiva em telas menores -->
      <div class="table-responsive">

        <!-- tabela com os pedidos -->
        <table class="table table-dark table-hover align-middle">

          <!-- cabeçalho da tabela -->
          <thead>
            <tr>
              <th>pedido</th>
              <th>utilizador</th>
              <th>email</th>
              <th>total</th>
              <th>estado</th>
              <th>data</th>
            </tr>
          </thead>

          <!-- corpo da tabela -->
          <tbody>

            <!-- percorre todos os pedidos encontrados -->
            <?php foreach ($pedidos as $pedido): ?>
              <tr>

                <!-- mostra o número/id do pedido -->
                <td>
                  #<?= (int)$pedido['id'] ?>
                </td>

                <!-- mostra o nome do utilizador -->
                <td>
                  <?= e($pedido['name']) ?>
                </td>

                <!-- mostra o email do utilizador -->
                <td>
                  <?= e($pedido['email']) ?>
                </td>

                <!-- mostra o valor total da compra formatado em euros -->
                <td class="text-success fw-bold">
                  €<?= number_format((float)$pedido['total'], 2, ',', '.') ?>
                </td>

                <!-- mostra o estado do pedido -->
                <td>
                  <span class="badge bg-success">
                    <?= e($pedido['status']) ?>
                  </span>
                </td>

                <!-- mostra a data da compra formatada -->
                <td>
                  <?= date('d/m/Y H:i', strtotime($pedido['created_at'])) ?>
                </td>

              </tr>
            <?php endforeach; ?>

          </tbody>
        </table>
      </div>

    <?php endif; ?>

  </div>
</div>

<?php 
/* carrega o rodapé do site */
require_once __DIR__ . '/footer.php'; 
?>