<?php
require_once __DIR__ . '/admin.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/header.php';

$pdo = db();

$st = $pdo->query("
  SELECT p.id, p.total, p.status, p.created_at, u.name, u.email
  FROM pedidos p
  INNER JOIN users u ON u.id = p.user_id
  ORDER BY p.created_at DESC
");
$pedidos = $st->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h2 class="fw-bold mb-1">pedidos</h2>
    <p class="text-light-emphasis mb-0">lista de compras feitas no sistema</p>
  </div>
</div>

<div class="card border-0 shadow text-light" style="background: rgba(20,20,28,0.92);">
  <div class="card-body">
    <?php if (!$pedidos): ?>
      <div class="alert alert-warning mb-0">
        nenhum pedido encontrado.
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-dark table-hover align-middle">
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
          <tbody>
            <?php foreach ($pedidos as $pedido): ?>
              <tr>
                <td>#<?= (int)$pedido['id'] ?></td>
                <td><?= e($pedido['name']) ?></td>
                <td><?= e($pedido['email']) ?></td>
                <td class="text-success fw-bold">
                  €<?= number_format((float)$pedido['total'], 2, ',', '.') ?>
                </td>
                <td>
                  <span class="badge bg-success"><?= e($pedido['status']) ?></span>
                </td>
                <td><?= date('d/m/Y H:i', strtotime($pedido['created_at'])) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>